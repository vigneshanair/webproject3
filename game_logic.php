<?php
// game_logic.php
// Handles session, DB connection, and core game logic.

// ------------------------
// SESSION SETUP & HELPERS
// ------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['evidence_bag'])) {
    // [caseId => [evidenceId => ['label'=>..., 'time'=>...]]]
    $_SESSION['evidence_bag'] = [];
}

if (!isset($_SESSION['notebook'])) {
    // [caseId => text]
    $_SESSION['notebook'] = [];
}

function add_evidence($evidenceId, $label, $caseId)
{
    if (!isset($_SESSION['evidence_bag'][$caseId])) {
        $_SESSION['evidence_bag'][$caseId] = [];
    }
    if (!isset($_SESSION['evidence_bag'][$caseId][$evidenceId])) {
        $_SESSION['evidence_bag'][$caseId][$evidenceId] = [
            'label' => $label,
            'time'  => date('Y-m-d H:i:s')
        ];
    }
}

function get_evidence_for_case($caseId)
{
    return $_SESSION['evidence_bag'][$caseId] ?? [];
}

function set_notebook($caseId, $text)
{
    $_SESSION['notebook'][$caseId] = $text;
}

function get_notebook($caseId)
{
    return $_SESSION['notebook'][$caseId] ?? '';
}

// ------------------------
// DB CONNECTION
// ------------------------

// VERY IMPORTANT: hostname must be localhost on codd.
$host    = 'localhost';
$db      = 'vajithnair1';       // your DB name
$user    = 'vajithnair1';       // your MySQL username
$pass    = 'vajithnair1';       // EXACTLY as you type in the mysql -p prompt
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// ------------------------
// GAME LOGIC HELPERS
// ------------------------

function get_or_create_player(PDO $pdo, string $name): int
{
    $stmt = $pdo->prepare("SELECT id FROM players WHERE name = ?");
    $stmt->execute([$name]);
    $row = $stmt->fetch();
    if ($row) {
        return (int)$row['id'];
    }

    $ins = $pdo->prepare("INSERT INTO players (name) VALUES (?)");
    $ins->execute([$name]);
    return (int)$pdo->lastInsertId();
}

function get_player_id(PDO $pdo): ?int
{
    if (!isset($_SESSION['player_name'])) {
        return null;
    }

    if (!isset($_SESSION['player_id'])) {
        $_SESSION['player_id'] = get_or_create_player($pdo, $_SESSION['player_name']);
    }
    return $_SESSION['player_id'];
}

// Dynamic difficulty: base difficulty from cases.difficulty + stats.
function get_difficulty_for_player(PDO $pdo, int $playerId, int $caseId): string
{
    $stmt = $pdo->prepare("SELECT difficulty FROM cases WHERE id = ?");
    $stmt->execute([$caseId]);
    $case = $stmt->fetch();
    $base = $case ? $case['difficulty'] : 'Easy';

    $s = $pdo->prepare(
        "SELECT wrong_accusations, correct_interrogations
         FROM player_case_stats
         WHERE player_id = ? AND case_id = ?"
    );
    $s->execute([$playerId, $caseId]);
    $row = $s->fetch();
    if (!$row) {
        return $base;
    }

    if ($row['wrong_accusations'] >= 3) {
        return 'Easy';
    }
    if ($row['correct_interrogations'] >= 5 && $row['wrong_accusations'] == 0) {
        return 'Hard';
    }
    return $base;
}

function increment_stat_attempt(PDO $pdo, int $playerId, int $caseId)
{
    $s = $pdo->prepare("SELECT id FROM player_case_stats WHERE player_id = ? AND case_id = ?");
    $s->execute([$playerId, $caseId]);
    $row = $s->fetch();

    if ($row) {
        $upd = $pdo->prepare(
            "UPDATE player_case_stats
             SET attempts = attempts + 1
             WHERE id = ?"
        );
        $upd->execute([$row['id']]);
    } else {
        $ins = $pdo->prepare(
            "INSERT INTO player_case_stats
             (player_id, case_id, attempts, time_started)
             VALUES (?, ?, 1, NOW())"
        );
        $ins->execute([$playerId, $caseId]);
    }
}

function increment_correct_interrogations(PDO $pdo, int $playerId, int $caseId)
{
    $upd = $pdo->prepare(
        "INSERT INTO player_case_stats
            (player_id, case_id, correct_interrogations, time_started)
         VALUES (?, ?, 1, NOW())
         ON DUPLICATE KEY UPDATE
            correct_interrogations = correct_interrogations + 1"
    );
    $upd->execute([$playerId, $caseId]);
}

function increment_wrong_accusations(PDO $pdo, int $playerId, int $caseId)
{
    $upd = $pdo->prepare(
        "INSERT INTO player_case_stats
            (player_id, case_id, wrong_accusations, time_started)
         VALUES (?, ?, 1, NOW())
         ON DUPLICATE KEY UPDATE
            wrong_accusations = wrong_accusations + 1"
    );
    $upd->execute([$playerId, $caseId]);
}

// Save solution + update leaderboard
function record_case_solution(PDO $pdo, int $playerId, int $caseId, string $solutionCode, int $score)
{
    $upd = $pdo->prepare(
        "INSERT INTO player_case_stats
            (player_id, case_id, solution_code, score, time_started, time_finished)
         VALUES (?, ?, ?, ?, NOW(), NOW())
         ON DUPLICATE KEY UPDATE
            solution_code = ?,
            score         = GREATEST(score, VALUES(score)),
            time_finished = NOW()"
    );
    $upd->execute([$playerId, $caseId, $solutionCode, $score, $solutionCode]);

    $tStmt = $pdo->prepare(
        "SELECT TIMESTAMPDIFF(SECOND, time_started, time_finished) AS t
         FROM player_case_stats
         WHERE player_id = ? AND case_id = ?"
    );
    $tStmt->execute([$playerId, $caseId]);
    $tRow = $tStmt->fetch();
    $t = $tRow ? (int)$tRow['t'] : 0;

    $s = $pdo->prepare("SELECT * FROM leaderboard WHERE player_id = ?");
    $s->execute([$playerId]);
    $row = $s->fetch();

    if ($row) {
        $casesSolved = $row['cases_solved'] + 1;
        $totalScore  = $row['total_score'] + $score;
        $newAvg      = ($row['avg_time_seconds'] * $row['cases_solved'] + $t) / $casesSolved;

        $u = $pdo->prepare(
            "UPDATE leaderboard
             SET total_score = ?, cases_solved = ?, avg_time_seconds = ?
             WHERE player_id = ?"
        );
        $u->execute([$totalScore, $casesSolved, (int)$newAvg, $playerId]);
    } else {
        $i = $pdo->prepare(
            "INSERT INTO leaderboard
                (player_id, total_score, cases_solved, avg_time_seconds)
             VALUES (?, ?, 1, ?)"
        );
        $i->execute([$playerId, $score, $t]);
    }
}

// Case unlocks based on dependencies table
function is_case_unlocked(PDO $pdo, int $playerId, int $caseId): bool
{
    if ($caseId === 1) {
        return true;
    }

    $d = $pdo->prepare(
        "SELECT required_case_id, required_solution_code
         FROM case_dependencies
         WHERE case_id = ?"
    );
    $d->execute([$caseId]);
    $deps = $d->fetchAll();
    if (!$deps) {
        return true;
    }

    foreach ($deps as $dep) {
        $q = $pdo->prepare(
            "SELECT solution_code
             FROM player_case_stats
             WHERE player_id = ? AND case_id = ? AND solution_code IS NOT NULL"
        );
        $q->execute([$playerId, $dep['required_case_id']]);
        $row = $q->fetch();
        if (!$row) {
            return false;
        }
        if ($dep['required_solution_code'] &&
            $row['solution_code'] !== $dep['required_solution_code']) {
            return false;
        }
    }
    return true;
}

/* =========================
   CASE LEADS & INTERROGATIONS
   ========================== */

/**
 * Crime-scene leads for each case.
 * Some are good (push story forward), some are dead ends.
 */
function get_leads_for_case(int $caseId): array
{
    switch ($caseId) {
        case 1:
        default:
            return [
                [
                    'id'      => 'window_smudge',
                    'label'   => 'Smudged prints near the open window',
                    'type'    => 'good',
                    'summary' => 'Suggests someone used the window as an exit route.',
                ],
                [
                    'id'      => 'broken_clasp',
                    'label'   => 'Broken bracelet clasp under the table',
                    'type'    => 'good',
                    'summary' => 'Supports a struggle near the display table.',
                ],
                [
                    'id'      => 'caterer_crates',
                    'label'   => 'Empty catering crates in hallway',
                    'type'    => 'dead_end',
                    'summary' => 'Looks suspicious, but crates are logged and checked.',
                ],
                [
                    'id'      => 'smear_on_glass',
                    'label'   => 'Random smear on display glass',
                    'type'    => 'dead_end',
                    'summary' => 'Old maintenance stain – unrelated to the theft.',
                ],
            ];
            // You can add more cases (2–7) here later.
    }
}

/**
 * Interrogation question bank.
 * Each question has a lead_type:
 *  - good  → helpful clue (added to evidence bag)
 *  - false → misleading / lie
 *  - dead  → dead end
 */
function get_interrogation_script(int $caseId): array
{
    // Case 1 example. Extend for 2–7 later if you have time.
    if ($caseId === 1) {
        return [
            'Lena Hart' => [
                [
                    'id'            => 'lena_motive',
                    'question'      => 'Why would anyone want to steal that bracelet?',
                    'answer'        => 'Lena stiffens. “You have no idea what they did to me. Some people will do anything for revenge.”',
                    'lead_type'     => 'good',
                    'evidence_id'   => 'lena_resentment',
                    'evidence_label'=> 'Lena shows strong resentment toward the victim.',
                ],
                [
                    'id'            => 'lena_alibi',
                    'question'      => 'Where were you during the time of the theft?',
                    'answer'        => '“On duty, same as always,” she insists, but avoids eye contact.',
                    'lead_type'     => 'false',
                    'evidence_id'   => 'lena_shaky_alibi',
                    'evidence_label'=> 'Lena’s alibi feels shaky and rehearsed.',
                ],
                [
                    'id'            => 'lena_relationship',
                    'question'      => 'How long have you worked for the victim?',
                    'answer'        => '“Years,” she says softly. “Long enough to know all their secrets.”',
                    'lead_type'     => 'good',
                    'evidence_id'   => 'lena_knows_secrets',
                    'evidence_label'=> 'Lena knows many of the victim’s private secrets.',
                ],
            ],
            'Marco Vance' => [
                [
                    'id'            => 'marco_alibi',
                    'question'      => 'Where were you when the bracelet went missing?',
                    'answer'        => '“On patrol near the main entrance. Cameras will back me up,” he says confidently.',
                    'lead_type'     => 'dead',
                    'evidence_id'   => 'marco_cameras',
                    'evidence_label'=> 'Marco claims cameras support his alibi (later mostly checks out).',
                ],
                [
                    'id'            => 'marco_motive',
                    'question'      => 'Did you know how valuable the bracelet was?',
                    'answer'        => '“Everyone knew. That doesn’t mean I stole it,” he snaps.',
                    'lead_type'     => 'false',
                    'evidence_id'   => 'marco_defensive',
                    'evidence_label'=> 'Marco gets defensive when value is mentioned.',
                ],
            ],
            'Iris Cole' => [
                [
                    'id'            => 'iris_arguing',
                    'question'      => 'You mentioned hearing an argument. Who was arguing?',
                    'answer'        => '“The victim and someone with a low voice… I think it was Lena,” Iris says.',
                    'lead_type'     => 'good',
                    'evidence_id'   => 'iris_heard_argument',
                    'evidence_label'=> 'Iris heard the victim arguing with someone sounding like Lena.',
                ],
                [
                    'id'            => 'iris_motive',
                    'question'      => 'Did you ever try to buy the bracelet yourself?',
                    'answer'        => 'Iris smiles. “I prefer art that isn’t chained to people’s wrists.”',
                    'lead_type'     => 'dead',
                    'evidence_id'   => 'iris_joke',
                    'evidence_label'=> 'Iris jokes about the bracelet; no direct motive revealed.',
                ],
            ],
        ];
    }

    // fallback: no scripted questions
    return [];
}

/**
 * Load suspects for a case (uses `id` column, NOT `suspect_id`).
 */
function get_suspects_for_case(PDO $pdo, int $caseId): array
{
    $stmt = $pdo->prepare(
        "SELECT id, name, personality, initial_alibi,
                IFNULL(is_primary, 0) AS is_primary
         FROM suspects
         WHERE case_id = ?
         ORDER BY id"
    );
    $stmt->execute([$caseId]);
    return $stmt->fetchAll();
}

<?php
// game_state.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- DATA DEFINITIONS (cases) ----------

function get_cases(): array {
    // 7 levels
    return [
        1 => [
            'title'      => 'The Vanishing Bracelet',
            'tagline'    => 'A priceless heirloom disappears during a gala.',
            'difficulty' => 'Easy',
        ],
        2 => [
            'title'      => 'Whispers in the Alley',
            'tagline'    => 'A shadowy figure and a missing informant.',
            'difficulty' => 'Easy',
        ],
        3 => [
            'title'      => 'Echoes in the Gallery',
            'tagline'    => 'A crime hidden in plain sight among paintings.',
            'difficulty' => 'Medium',
        ],
        4 => [
            'title'      => 'Midnight on Metro Line',
            'tagline'    => 'A train, a blackout, and a vanished briefcase.',
            'difficulty' => 'Medium',
        ],
        5 => [
            'title'      => 'The Broken Alibi',
            'tagline'    => 'Everyone has a story. One of them is a lie.',
            'difficulty' => 'Medium',
        ],
        6 => [
            'title'      => 'Static on Channel 9',
            'tagline'    => 'A live broadcast that hid a deadly secret.',
            'difficulty' => 'Hard',
        ],
        7 => [
            'title'      => 'The Final Thread',
            'tagline'    => 'All previous cases collide in one final puzzle.',
            'difficulty' => 'Hard',
        ],
    ];
}

function get_case_title(int $caseId): string {
    $cases = get_cases();
    return $cases[$caseId]['title'] ?? 'Unknown Case';
}

// ---------- SUSPECT DEFINITIONS (for all 7 cases) ----------
// You can later edit names/roles/traits or add 'image' keys for new suspects.

function get_suspects_for_case(int $caseId): array {
    switch ($caseId) {
        case 1:
            // You already saved these images as:
            //  - Lena_Hart.png
            //  - MARCO_VANCE.png
            //  - Iris_Cole.png
            return [
                1 => [
                    'name'   => 'Lena Hart',
                    'role'   => 'Personal Assistant',
                    'traits' => ['Organised', 'Nervous'],
                    'image'  => 'Lena_Hart.png',
                ],
                2 => [
                    'name'   => 'Marco Vance',
                    'role'   => 'Security Guard',
                    'traits' => ['Calm', 'Evasive'],
                    'image'  => 'MARCO_VANCE.png',
                ],
                3 => [
                    'name'   => 'Iris Cole',
                    'role'   => 'Art Dealer',
                    'traits' => ['Charming', 'Ambitious'],
                    'image'  => 'Iris_Cole.png',
                ],
            ];

        case 2: // Whispers in the Alley
            return [
                1 => [
                    'name'   => 'Noah Reyes',
                    'role'   => 'Street Musician',
                    'traits' => ['Observant', 'Anxious'],
                ],
                2 => [
                    'name'   => 'Dana Brooks',
                    'role'   => 'Local Bartender',
                    'traits' => ['Sharp-tongued', 'Protective'],
                ],
                3 => [
                    'name'   => 'Victor Lane',
                    'role'   => 'Private Informant',
                    'traits' => ['Secretive', 'Paranoid'],
                ],
            ];

        case 3: // Echoes in the Gallery
            return [
                1 => [
                    'name'   => 'Amelia Stone',
                    'role'   => 'Curator',
                    'traits' => ['Perfectionist', 'Tired'],
                ],
                2 => [
                    'name'   => 'Julian Park',
                    'role'   => 'Art Critic',
                    'traits' => ['Sarcastic', 'Arrogant'],
                ],
                3 => [
                    'name'   => 'Clara Nguyen',
                    'role'   => 'Restoration Artist',
                    'traits' => ['Patient', 'Meticulous'],
                ],
            ];

        case 4: // Midnight on Metro Line
            return [
                1 => [
                    'name'   => 'Elias Romero',
                    'role'   => 'Train Conductor',
                    'traits' => ['Calm', 'Rule-bound'],
                ],
                2 => [
                    'name'   => 'Harper Lee',
                    'role'   => 'Night-shift Cleaner',
                    'traits' => ['Invisible', 'Observant'],
                ],
                3 => [
                    'name'   => 'Mina Abbas',
                    'role'   => 'Commuter',
                    'traits' => ['Restless', 'Alert'],
                ],
            ];

        case 5: // The Broken Alibi
            return [
                1 => [
                    'name'   => 'Owen Clarke',
                    'role'   => 'Accountant',
                    'traits' => ['Precise', 'Defensive'],
                ],
                2 => [
                    'name'   => 'Riya Patel',
                    'role'   => 'Roommate',
                    'traits' => ['Friendly', 'Forgetful'],
                ],
                3 => [
                    'name'   => 'Logan Hayes',
                    'role'   => 'Co-worker',
                    'traits' => ['Competitive', 'Smooth-talker'],
                ],
            ];

        case 6: // Static on Channel 9
            return [
                1 => [
                    'name'   => 'Nadia Flores',
                    'role'   => 'News Anchor',
                    'traits' => ['Composed', 'Guarded'],
                ],
                2 => [
                    'name'   => 'Theo Marshall',
                    'role'   => 'Camera Operator',
                    'traits' => ['Technical', 'Quiet'],
                ],
                3 => [
                    'name'   => 'Grace Kim',
                    'role'   => 'Producer',
                    'traits' => ['Bossy', 'Efficient'],
                ],
            ];

        case 7: // The Final Thread
            return [
                1 => [
                    'name'   => 'Chief Rowan',
                    'role'   => 'Division Chief',
                    'traits' => ['Authoritative', 'Secretive'],
                ],
                2 => [
                    'name'   => 'Sylvie Hart',
                    'role'   => 'Independent Investigator',
                    'traits' => ['Relentless', 'Emotionally Invested'],
                ],
                3 => [
                    'name'   => 'Unknown Caller',
                    'role'   => 'Anonymous Source',
                    'traits' => ['Unstable', 'Well-informed'],
                ],
            ];

        default:
            return [];
    }
}

// ---------- SESSION INITIALIZATION ----------

if (!isset($_SESSION['progress'])) {
    // progress[caseId] = ['completed' => bool, 'percent' => int]
    $_SESSION['progress'] = [];
}
if (!isset($_SESSION['evidence'])) {
    // evidence[caseId] = [ 'item_key' => 'Item description', ... ]
    $_SESSION['evidence'] = [];
}
if (!isset($_SESSION['notes'])) {
    // notes[caseId] = 'text'
    $_SESSION['notes'] = [];
}
if (!isset($_SESSION['detective'])) {
    $_SESSION['detective'] = [
        'name'  => null,
        'badge' => null,
    ];
}

// ---------- PROGRESSION HELPERS ----------

function get_max_unlocked_case(): int {
    // At least 1
    $max = 1;
    foreach ($_SESSION['progress'] as $caseId => $info) {
        if (!empty($info['completed']) && $caseId >= $max) {
            $max = $caseId + 1;
        }
    }
    return min($max, 7); // cap at 7
}

function get_case_progress(int $caseId): int {
    return $_SESSION['progress'][$caseId]['percent'] ?? 0;
}

function set_case_progress(int $caseId, int $percent): void {
    $percent = max(0, min(100, $percent));
    if (!isset($_SESSION['progress'][$caseId])) {
        $_SESSION['progress'][$caseId] = ['completed' => false, 'percent' => 0];
    }
    $_SESSION['progress'][$caseId]['percent'] = $percent;
    if ($percent === 100) {
        $_SESSION['progress'][$caseId]['completed'] = true;
    }
}

// ---------- EVIDENCE & NOTES ----------

function add_evidence(int $caseId, string $key, string $label): void {
    if (!isset($_SESSION['evidence'][$caseId])) {
        $_SESSION['evidence'][$caseId] = [];
    }
    $_SESSION['evidence'][$caseId][$key] = $label;
}

function get_evidence_for_case(int $caseId): array {
    return $_SESSION['evidence'][$caseId] ?? [];
}

function get_notes_for_case(int $caseId): string {
    return $_SESSION['notes'][$caseId] ?? '';
}

function save_notes_for_case(int $caseId, string $notes): void {
    $_SESSION['notes'][$caseId] = $notes;
}

// ---------- AUTH / COMMON LAYOUT ----------

function require_detective() {
    if (empty($_SESSION['detective']['name'])) {
        header('Location: index.php');
        exit;
    }
}

// Render shared top bar
function render_header(string $pageTitle = ''): void {
    $name  = htmlspecialchars($_SESSION['detective']['name'] ?? 'Unknown Detective');
    $badge = htmlspecialchars($_SESSION['detective']['badge'] ?? '');
    ?>
    <header class="top-bar">
        <div class="top-bar-left">
            <h1 class="game-logo">Cryptic Quest</h1>
            <?php if ($pageTitle): ?>
                <span class="page-title"> / <?php echo htmlspecialchars($pageTitle); ?></span>
            <?php endif; ?>
        </div>
        <div class="top-bar-right">
            <span class="detective-badge">
                <?php echo $badge ? "$badge $name" : $name; ?>
            </span>
        </div>
    </header>
    <?php
}

function render_evidence_bag_sidebar(int $caseId): void {
    $evidence = get_evidence_for_case($caseId);
    ?>
    <aside class="evidence-bag">
        <h3>Evidence Bag</h3>
        <p class="evidence-count"><?php echo count($evidence); ?> items collected</p>
        <ul class="evidence-list">
            <?php if (empty($evidence)): ?>
                <li class="evidence-empty">No evidence yet.</li>
            <?php else: ?>
                <?php foreach ($evidence as $key => $label): ?>
                    <li><?php echo htmlspecialchars($label); ?></li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </aside>
    <?php
}

function render_notebook(int $caseId): void {
    $notes = htmlspecialchars(get_notes_for_case($caseId));
    ?>
    <!-- Floating notebook icon -->
    <a href="#notebook-panel" class="notebook-toggle">📝</a>

    <!-- Notebook popup (uses :target in CSS) -->
    <div id="notebook-panel" class="notebook-panel">
        <div class="notebook-inner">
            <h3>Case Notebook</h3>
            <form method="post">
                <textarea name="notes" rows="6"
                    placeholder="Write your theories, loose ends, and hunches here..."><?php echo $notes; ?></textarea>
                <button type="submit" name="save_notes" class="btn-primary">Save Notes</button>
            </form>
            <a href="#" class="notebook-close">Close</a>
        </div>
    </div>
    <?php
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Final Verdict – Cryptic Quest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- 🔥 THIS IS WHERE THE CSS GOES 🔥 -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<?php
// verdict.php – Final Case Report screen (themed)

require 'game_logic.php';

// Which case are we finishing?
$caseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : 1;

// Get current player
$playerId = get_player_id($pdo);
if (!$playerId) {
    header('Location: index.php');
    exit;
}

// Load case info
$caseStmt = $pdo->prepare("SELECT id, title, description, difficulty FROM cases WHERE id = ?");
$caseStmt->execute([$caseId]);
$case = $caseStmt->fetch();

// Load suspects for dropdown
$susStmt = $pdo->prepare("SELECT id, name FROM suspects WHERE case_id = ? ORDER BY name");
$susStmt->execute([$caseId]);
$suspects = $susStmt->fetchAll();

// Evidence from session “evidence bag”
$evidenceItems = get_evidence_for_case($caseId);

// Handle form submit
$successMessage = '';
$errorMessage   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $primarySuspectId = isset($_POST['primary_suspect']) ? (int)$_POST['primary_suspect'] : 0;
    $motive           = trim($_POST['motive'] ?? '');

    if ($primarySuspectId <= 0) {
        $errorMessage = 'Please choose a primary suspect.';
    } elseif ($motive === '') {
        $errorMessage = 'Please describe the motive in your own words.';
    } else {
        // Simple scoring: number of clues collected
        $score = count($evidenceItems);

        // You can encode the solution as "caseId:suspectId"
        $solutionCode = 'CASE' . $caseId . ':SUSPECT' . $primarySuspectId;

        // Record solution + leaderboard update
        record_case_solution($pdo, $playerId, $caseId, $solutionCode, $score);

        $successMessage = 'Final report submitted! Your investigation has been logged to the leaderboard.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Final Verdict – Cryptic Quest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- IMPORTANT: same stylesheet as your other dark-theme pages -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="app-body">
    <header class="top-nav">
        <div class="top-nav-left">
            <a href="case_dashboard.php?case_id=<?php echo $caseId; ?>" class="btn-back">
                ← Back to Case Dashboard
            </a>
        </div>
        <div class="top-nav-right">
            <span class="badge">Rookie Detective <?php echo htmlspecialchars($_SESSION['player_name'] ?? ''); ?></span>
        </div>
    </header>

    <main class="page page-final-verdict">
        <section class="page-header">
            <h1 class="page-title">Submit Final Case Report</h1>
            <p class="page-subtitle">
                Review your suspects and evidence, then submit your official verdict to the department.
            </p>
        </section>

        <div class="page-grid">
            <!-- LEFT: Case summary + form -->
            <section class="card card-main">
                <div class="card-header">
                    <h2 class="card-title">Case Summary</h2>
                    <?php if ($case): ?>
                        <p class="case-meta">
                            <span class="case-name">
                                Case: <?php echo htmlspecialchars($case['title']); ?>
                            </span>
                            <span class="case-difficulty">
                                Difficulty: <?php echo htmlspecialchars($case['difficulty']); ?>
                            </span>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <div class="section-block">
                        <h3 class="section-title">Collected Evidence</h3>
                        <?php if ($evidenceItems): ?>
                            <ul class="evidence-list">
                                <?php foreach ($evidenceItems as $item): ?>
                                    <li><?php echo htmlspecialchars($item['label']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="muted">No evidence collected yet for this case.</p>
                        <?php endif; ?>
                    </div>

                    <div class="section-block">
                        <h3 class="section-title">Final Verdict</h3>

                        <?php if ($errorMessage): ?>
                            <div class="alert alert-error">
                                <?php echo htmlspecialchars($errorMessage); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($successMessage): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($successMessage); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" class="form-vertical">
                            <div class="form-group">
                                <label for="primary_suspect" class="form-label">Primary Suspect</label>
                                <select id="primary_suspect" name="primary_suspect" class="form-select">
                                    <option value="0">Select a suspect</option>
                                    <?php foreach ($suspects as $s): ?>
                                        <option value="<?php echo $s['id']; ?>">
                                            <?php echo htmlspecialchars($s['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="motive" class="form-label">Motive (your summary)</label>
                                <textarea id="motive"
                                          name="motive"
                                          rows="4"
                                          class="form-textarea"
                                          placeholder="Summarize the motive based on what you've uncovered."><?php
                                    echo htmlspecialchars($_POST['motive'] ?? '');
                                ?></textarea>
                            </div>

                            <button type="submit" class="btn-primary">
                                Submit Final Report
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- RIGHT: Evidence bag sidebar -->
            <aside class="card card-sidebar">
                <div class="card-header">
                    <h2 class="card-title">Evidence Bag</h2>
                    <p class="card-subtitle">
                        <?php echo count($evidenceItems); ?> item(s) collected
                    </p>
                </div>
                <div class="card-body">
                    <?php if ($evidenceItems): ?>
                        <ul class="evidence-list">
                            <?php foreach ($evidenceItems as $item): ?>
                                <li><?php echo htmlspecialchars($item['label']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="muted">Your evidence bag is currently empty.</p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>

<?php
require_once 'game_state.php';
require_detective();

$caseId = (int)($_GET['case'] ?? 1);
$cases  = get_cases();
if (!isset($cases[$caseId])) {
    header('Location: cases.php');
    exit;
}

$case = $cases[$caseId];
$progress = get_case_progress($caseId);

// notebook save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notes'])) {
    save_notes_for_case($caseId, $_POST['notes'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($case['title']); ?> – Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php render_header('Case Dashboard'); ?>

<main class="main-layout with-sidebar">
    <section class="case-dashboard">
        <a href="cases.php" class="back-button">← Back to Case Board</a>

        <header class="case-header">
            <h2><?php echo htmlspecialchars($case['title']); ?></h2>
            <p class="case-tagline-large"><?php echo htmlspecialchars($case['tagline']); ?></p>
        </header>

        <div class="case-meta">
            <div class="meta-item">
                <span class="meta-label">Case ID</span>
                <span class="meta-value">0<?php echo $caseId; ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Difficulty</span>
                <span class="meta-value"><?php echo htmlspecialchars($case['difficulty']); ?></span>
            </div>
            <div class="meta-item meta-progress">
                <span class="meta-label">Investigation Progress</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                </div>
                <span class="progress-label"><?php echo $progress; ?>% complete</span>
            </div>
        </div>

        <section class="case-overview">
            <h3>Current Investigation</h3>
            <p>
                A preliminary report indicates unusual activity surrounding the victim and several key individuals.
                Your tasks: search the crime scene, interrogate suspects, analyze forensic evidence, and submit a final verdict.
            </p>
            <p>
                Remember: every choice impacts what you discover. Overlook details, and the true culprit might walk free.
            </p>
        </section>

        <section class="case-actions">
            <h3>Investigation Steps</h3>
            <div class="action-grid">
                <a class="action-card" href="crime_scene.php?case=<?php echo $caseId; ?>">
                    <h4>Crime Scene</h4>
                    <p>Search key zones for physical clues and overlooked evidence.</p>
                </a>
                <a class="action-card" href="interrogations.php?case=<?php echo $caseId; ?>">
                    <h4>Suspects & Interrogations</h4>
                    <p>Review profiles and question suspects with targeted dialogue.</p>
                </a>
                <a class="action-card" href="lab.php?case=<?php echo $caseId; ?>">
                    <h4>Forensic Lab</h4>
                    <p>Match fingerprints and compare evidence against statements.</p>
                </a>
                <a class="action-card" href="verdict.php?case=<?php echo $caseId; ?>">
                    <h4>Final Verdict</h4>
                    <p>Submit your official case report and accuse the culprit.</p>
                </a>
            </div>
        </section>
    </section>

    <?php render_evidence_bag_sidebar($caseId); ?>
    <?php render_notebook($caseId); ?>
</main>
</body>
</html>

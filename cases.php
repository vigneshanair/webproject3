<?php
// cases.php
require_once 'game_state.php';
require_detective();

$cases = get_cases();
$maxUnlocked = get_max_unlocked_case();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Board – Cryptic Quest</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php render_header('Case Board'); ?>

<main class="main-layout">
    <section class="case-board">
        <h2>Case Board</h2>
        <p class="case-board-subtitle">
            Each solved case unlocks the next. Choose your next investigation, detective.
        </p>

        <div class="case-grid">
            <?php foreach ($cases as $id => $case): 
                $unlocked = ($id <= $maxUnlocked);
                $progress = get_case_progress($id);
                $completed = ($progress === 100);
            ?>
            <div class="case-card <?php echo $unlocked ? '' : 'case-locked'; ?>">
                <div class="case-card-header">
                    <span class="case-number">CASE 0<?php echo $id; ?></span>
                    <span class="case-difficulty"><?php echo htmlspecialchars($case['difficulty']); ?></span>
                </div>
                <h3><?php echo htmlspecialchars($case['title']); ?></h3>
                <p class="case-tagline"><?php echo htmlspecialchars($case['tagline']); ?></p>

                <div class="mini-progress-bar">
                    <div class="mini-progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                </div>
                <p class="mini-progress-label"><?php echo $progress; ?>% complete</p>

                <div class="case-card-footer">
                    <?php if ($unlocked): ?>
                        <a href="case_dashboard.php?case=<?php echo $id; ?>" class="btn-secondary">
                            <?php echo $completed ? 'Review Case' : 'Enter Case'; ?>
                        </a>
                        <?php if ($completed): ?>
                            <span class="status-pill status-solved">SOLVED</span>
                        <?php else: ?>
                            <span class="status-pill status-active">ACTIVE</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="lock-icon">🔒 Locked</span>
                        <span class="status-pill status-locked">COMPLETE PREVIOUS CASE</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>
</body>
</html>

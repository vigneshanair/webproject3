<?php
// levels.php
$page_title = 'Levels – Cryptic Quest';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = (int)($_POST['level'] ?? 0);
    $max   = (int)$_SESSION['max_level_unlocked'];

    if ($level >= 1 && $level <= 7 && $level <= $max) {
        $_SESSION['current_level'] = $level;

        $caseId = get_case_for_level($level);
        if ($caseId !== null) {
            set_active_case($caseId);
            header('Location: cases.php');
            exit;
        }
    }
}

include __DIR__ . '/includes/header.php';

$maxUnlocked = (int)$_SESSION['max_level_unlocked'];
$current     = (int)($_SESSION['current_level'] ?? 1);
?>

<section class="page-section">
    <div class="page-header">
        <h1>Case Levels</h1>
        <p class="page-subtitle">
            Clear a level to unlock the next. Work your way from rookie files to master detective.
        </p>
    </div>

    <form method="post">
        <div class="levels-grid">
            <?php for ($lvl = 1; $lvl <= 7; $lvl++): ?>
                <?php
                $locked   = $lvl > $maxUnlocked;
                $isCurrent = ($lvl === $current);
                ?>
                <article class="card level-card <?php echo $locked ? 'level-locked' : ''; ?>">
                    <span class="level-badge">
                        Level <?php echo $lvl; ?>
                        <?php if ($isCurrent && !$locked): ?> • Current<?php endif; ?>
                    </span>

                    <h3>
                        <?php
                        if     ($lvl === 1) echo 'Rookie Files';
                        elseif ($lvl === 2) echo 'Street Cases';
                        elseif ($lvl === 3) echo 'Storm Watch';
                        elseif ($lvl === 4) echo 'High Profile';
                        elseif ($lvl === 5) echo 'Night Shift';
                        elseif ($lvl === 6) echo 'Internal Affairs';
                        else                echo 'Master Detective';
                        ?>
                    </h3>

                    <p>
                        <?php if ($locked): ?>
                            Unlock by finishing Level <?php echo $lvl - 1; ?>.
                        <?php else: ?>
                            Select to work the active case at this level.
                        <?php endif; ?>
                    </p>

                    <button type="submit"
                            name="level"
                            value="<?php echo $lvl; ?>"
                            class="btn"
                        <?php echo $locked ? 'disabled' : ''; ?>>
                        <?php echo $locked ? 'Locked' : 'Play Level'; ?>
                    </button>
                </article>
            <?php endfor; ?>
        </div>
    </form>

    <div class="page-actions">
        <a href="index.php" class="btn-secondary">⬅ Back to Home</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

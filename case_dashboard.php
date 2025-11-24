<?php
require_once __DIR__ . '/game_logic.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$playerId = get_player_id($pdo);   // no redirect needed, or keep if you want
$caseId   = isset($_GET['case']) ? (int)$_GET['case'] : 1;

// remember this as the active case for the nav
$_SESSION['active_case_id'] = $caseId;

$c = $pdo->prepare("SELECT * FROM cases WHERE id=?");
$c->execute([$caseId]);
$case = $c->fetch();
if (!$case) die('Invalid case');

$c->execute([$caseId]);
$case = $c->fetch();
if (!$case) die('Invalid case');

// if no player, just default to easy difficulty
if ($playerId) {
    $difficulty = get_difficulty_for_player($pdo, $playerId, $caseId);
} else {
    $difficulty = 'easy';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Dashboard - CQ Cryptic Quest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styles.css">

    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, #111827, #020617);
            color: #e5e7eb;
            margin: 0;
            padding: 32px 12px;
        }

        a { color: inherit; }

        .case-shell {
            max-width: 1100px;
            margin: 0 auto;
            background: rgba(15, 23, 42, 0.96);
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.9);
            padding: 22px 24px 26px;
        }

        .case-header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .case-label {
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #9ca3af;
        }

        .back-link {
            font-size: 13px;
            color: #93c5fd;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .case-title {
            font-size: 24px;
            margin: 4px 0 4px;
        }

        .case-sub {
            font-size: 13px;
            color: #d1d5db;
            margin-bottom: 6px;
            max-width: 720px;
        }

        .case-difficulty {
            font-size: 13px;
            color: #e5e7eb;
            margin-bottom: 18px;
        }

        .case-difficulty strong {
            color: #c4b5fd;
        }

        .case-main {
            margin-top: 4px;
        }

        .case-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px 16px;
        }

        .card-link {
            text-decoration: none;
            display: block;
        }

        .action-card {
            background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.22), transparent),
                        radial-gradient(circle at bottom right, rgba(15, 23, 42, 0.9), #020617);
            border-radius: 18px;
            padding: 14px 16px 12px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.8);
            display: flex;
            flex-direction: column;
            gap: 4px;
            transition: transform 0.12s ease-out, box-shadow 0.12s ease-out, border-color 0.12s;
        }

        .action-card h3 {
            font-size: 15px;
            margin: 0;
        }

        .action-card p {
            font-size: 12px;
            color: #d1d5db;
            margin: 0;
        }

        .card-link:hover .action-card {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.95);
            border-color: rgba(56, 189, 248, 0.9);
        }

        @media (max-width: 640px) {
            .case-shell {
                padding: 18px 16px 20px;
            }

            .case-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
<div class="case-shell">
    <header class="case-header">
        <div class="case-header-top">
            <span class="case-label">Active Case</span>
            <a href="levels.php" class="back-link">← Back to Cases</a>
        </div>
        <h1 class="case-title">
            Case <?= $caseId ?>: <?= htmlspecialchars($case['title']) ?>
        </h1>
        <p class="case-sub">
            <?= nl2br(htmlspecialchars($case['description'])) ?>
        </p>
        <p class="case-difficulty">
            <strong>Dynamic Difficulty:</strong> <?= ucfirst($difficulty) ?>
        </p>
    </header>

    <main class="case-main">
        <div class="case-actions-grid">
            <a href="crime_scene.php?case=<?= $caseId ?>" class="card-link">
                <div class="action-card">
                    <h3>Crime Scene</h3>
                    <p>Explore and collect clues.</p>
                </div>
            </a>

            <a href="reconstruction.php?case=<?= $caseId ?>" class="card-link">
                <div class="action-card">
                    <h3>Reconstruct Scene</h3>
                    <p>Drag-and-drop evidence into position.</p>
                </div>
            </a>

            <a href="forensics.php?case=<?= $caseId ?>" class="card-link">
                <div class="action-card">
                    <h3>Forensic Lab</h3>
                    <p>Fingerprint analysis mini-game.</p>
                </div>
            </a>

            <a href="interrogations.php?case=<?= $caseId ?>" class="card-link">
                <div class="action-card">
                    <h3>Interrogations</h3>
                    <p>Dialogue trees &amp; suspect personalities.</p>
                </div>
            </a>

            <a href="casefiles.php?case=<?= $caseId ?>" class="card-link">
                <div class="action-card">
                    <h3>Case File</h3>
                    <p>Digital case file with suspects &amp; evidence.</p>
                </div>
            </a>

            <a href="verdict.php?case=<?= $caseId ?>" class="card-link">
                <div class="action-card">
                    <h3>Make Accusation</h3>
                    <p>Branching solutions &amp; scoring.</p>
                </div>
            </a>
        </div>
    </main>
</div>
</body>
</html>

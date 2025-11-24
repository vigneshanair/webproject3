<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Current level from session (defaults to 1)
$currentLevel = $_SESSION['max_level_unlocked'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CQ Cryptic Quest – Case Levels</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Inline styles just for this page -->
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: radial-gradient(circle at top, #111827, #020617);
            color: #e5e7eb;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            padding: 32px 12px;
        }

        .levels-shell {
            width: 100%;
            max-width: 960px;
            background: rgba(15, 23, 42, 0.95);
            border-radius: 18px;
            border: 1px solid rgba(148, 163, 184, 0.4);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.9);
            padding: 20px 22px 26px;
        }

        .game-title {
            font-size: 20px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #38bdf8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 14px;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        nav a {
            text-decoration: none;
            font-size: 13px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            color: #e5e7eb;
            background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.18), transparent);
        }

        nav a:hover {
            background: rgba(30, 64, 175, 0.6);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 10px;
        }

        .page-header h1 {
            font-size: 24px;
        }

        .current-level-pill {
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.6);
            color: #bbf7d0;
        }

        .page-sub {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 14px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            color: #93c5fd;
            text-decoration: none;
            margin-bottom: 16px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .tiers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 14px;
        }

        .tier-card {
            background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.25), transparent),
                        radial-gradient(circle at bottom right, rgba(30, 64, 175, 0.7), #020617);
            border-radius: 16px;
            padding: 14px 14px 12px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 145px;
        }

        .tier-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .tier-title {
            font-size: 15px;
            font-weight: 600;
        }

        .tier-subtitle {
            font-size: 12px;
            color: #e5e7eb;
        }

        .tier-desc {
            font-size: 12px;
            color: #d1d5db;
            margin-bottom: 10px;
        }

        .tier-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .lock-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.7);
            background: rgba(15, 23, 42, 0.8);
        }

        .lock-pill.locked {
            color: #fecaca;
            border-color: rgba(248, 113, 113, 0.8);
        }

        .lock-pill.unlocked {
            color: #bbf7d0;
            border-color: rgba(34, 197, 94, 0.9);
        }

        .tier-meta {
            font-size: 11px;
            color: #cbd5f5;
            text-align: right;
        }

        .btn-small {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            background: linear-gradient(to right, #22c55e, #16a34a);
            color: white;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(22, 163, 74, 0.6);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn-small.disabled {
            background: rgba(55, 65, 81, 0.8);
            color: #9ca3af;
            box-shadow: none;
            cursor: default;
        }

        @media (max-width: 640px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
        }
    </style>
</head>
<body>
<div class="levels-shell">
    <div class="game-title">
        <span>CQ • Cryptic Quest</span>
        <span style="font-size:11px; color:#9ca3af;">Crime Scene Investigation</span>
    </div>
    <p class="subtitle">Choose your investigation tier. Clear a level to unlock the next band of cases.</p>

    <!-- use ONLY existing files from your screenshot -->
    <nav>
        <a href="index.php">Home</a>
        <a href="levels.php">Levels</a>
        <a href="case_dashboard.php">Case Board</a>
        <a href="evidence_bag.php">Evidence Bag</a>
        <a href="leaderboard.php">Leaderboard</a>
    </nav>

    <div class="page-header">
        <h1>Case Levels</h1>
        <div class="current-level-pill">
            Level <?php echo (int)$currentLevel; ?> • Current Access
        </div>
    </div>

    <p class="page-sub">
        Clear a level to unlock the next. Each band raises the stakes, suspects, and complexity.
    </p>

    <a class="back-link" href="case_dashboard.php">
        ← Back to Main Access
    </a>

    <section class="tiers-grid">

        <!-- Level 1–2: Rookie Files -->
        <article class="tier-card">
            <div>
                <div class="tier-header">
                    <div>
                        <div class="tier-title">Rookie Files</div>
                        <div class="tier-subtitle">Introductory case work</div>
                    </div>
                    <span class="tier-meta">
                        Level 1–2<br>Difficulty: Easy
                    </span>
                </div>
                <p class="tier-desc">
                    Ease into the Cryptic Quest universe with guided investigations and clear-cut evidence trails.
                </p>
            </div>
            <div class="tier-footer">
                <?php if ($currentLevel >= 1): ?>
                    <span class="lock-pill unlocked">✅ Unlocked</span>
                    <!-- DIRECTLY use your existing Case-1 entry file -->
                    <a class="btn-small" href="crime_scene.php">
                        <?php echo ($currentLevel > 1) ? 'Replay Level 1' : 'Resume Level'; ?>
                    </a>
                <?php else: ?>
                    <span class="lock-pill locked">🔒 Locked</span>
                    <span class="btn-small disabled">Play Level</span>
                <?php endif; ?>
            </div>
        </article>

        <!-- The other levels stay locked for now, but they DON'T link to missing files -->

        <!-- Level 3: Street Cases -->
        <article class="tier-card">
            <div>
                <div class="tier-header">
                    <div>
                        <div class="tier-title">Street Cases</div>
                        <div class="tier-subtitle">City-level incidents</div>
                    </div>
                    <span class="tier-meta">
                        Level 3<br>Difficulty: Medium
                    </span>
                </div>
                <p class="tier-desc">
                    Put your pattern-spotting to work on tougher crime scenes woven into the city’s undercurrent.
                </p>
            </div>
            <div class="tier-footer">
                <span class="lock-pill locked">🔒 Locked</span>
                <span class="btn-small disabled">Play Level</span>
            </div>
        </article>

        <!-- Level 4: Storm Watch -->
        <article class="tier-card">
            <div>
                <div class="tier-header">
                    <div>
                        <div class="tier-title">Storm Watch</div>
                        <div class="tier-subtitle">Escalating threats</div>
                    </div>
                    <span class="tier-meta">
                        Level 4<br>Difficulty: Medium
                    </span>
                </div>
                <p class="tier-desc">
                    Interconnected cases with time-pressure and red herrings. Every clue placement matters.
                </p>
            </div>
            <div class="tier-footer">
                <span class="lock-pill locked">🔒 Locked</span>
                <span class="btn-small disabled">Play Level</span>
            </div>
        </article>

        <!-- Level 5: High Profile -->
        <article class="tier-card">
            <div>
                <div class="tier-header">
                    <div>
                        <div class="tier-title">High Profile</div>
                        <div class="tier-subtitle">Media-sensitive cases</div>
                    </div>
                    <span class="tier-meta">
                        Level 5<br>Difficulty: Hard
                    </span>
                </div>
                <p class="tier-desc">
                    Political pressure, public scrutiny, and fewer obvious clues. Missteps have consequences.
                </p>
            </div>
            <div class="tier-footer">
                <span class="lock-pill locked">🔒 Locked</span>
                <span class="btn-small disabled">Play Level</span>
            </div>
        </article>

        <!-- Level 6: Night Shift -->
        <article class="tier-card">
            <div>
                <div class="tier-header">
                    <div>
                        <div class="tier-title">Night Shift</div>
                        <div class="tier-subtitle">After-hours operations</div>
                    </div>
                    <span class="tier-meta">
                        Level 6<br>Difficulty: Hard
                    </span>
                </div>
                <p class="tier-desc">
                    Low-light scenes, partial evidence, and cross-case bleed. Your intuition becomes a tool.
                </p>
            </div>
            <div class="tier-footer">
                <span class="lock-pill locked">🔒 Locked</span>
                <span class="btn-small disabled">Play Level</span>
            </div>
        </article>

        <!-- Level 7: Blackout -->
        <article class="tier-card">
            <div>
                <div class="tier-header">
                    <div>
                        <div class="tier-title">Blackout</div>
                        <div class="tier-subtitle">Final convergence</div>
                    </div>
                    <span class="tier-meta">
                        Level 7<br>Difficulty: Elite
                    </span>
                </div>
                <p class="tier-desc">
                    Every decision from earlier levels feeds into this final web of suspects, motives, and cover-ups.
                </p>
            </div>
            <div class="tier-footer">
                <span class="lock-pill locked">🔒 Locked</span>
                <span class="btn-small disabled">Play Level</span>
            </div>
        </article>

    </section>
</div>
</body>
</html>

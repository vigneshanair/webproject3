<?php
// forensic_lab.php
require_once 'game_logic.php';

$caseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : 1;

// Get evidence from session for this case
$evidenceForCase = get_evidence_for_case($caseId);

// Forensic result variables
$selectedKey    = null;
$selectedLabel  = null;
$matchPercent   = null;
$resultMessage  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedKey = $_POST['evidence_key'] ?? null;

    if ($selectedKey && isset($evidenceForCase[$selectedKey])) {
        $selectedLabel = $evidenceForCase[$selectedKey]['label'];

        // Deterministic “random” percentage 40–100
        $hash = crc32($selectedKey . '|' . $caseId);
        $matchPercent = 40 + ($hash % 61); // 40..100

        if ($matchPercent >= 85) {
            $resultMessage = 'Strong forensic match – this evidence strongly ties the suspect to the case.';
        } elseif ($matchPercent >= 65) {
            $resultMessage = 'Partial correlation – the evidence supports the story, but more proof is needed.';
        } else {
            $resultMessage = 'Weak match – this lead might be a dead end or a deliberate misdirection.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forensic Lab – Cryptic Quest</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Extra styles specific to this page (still matches your dark theme) */

        body {
            background: #050816;
            color: #f5f5f5;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .cq-shell {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 24px 60px;
        }

        .cq-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .cq-badge {
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.08);
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #facc15;
        }

        .cq-title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .cq-layout {
            display: grid;
            grid-template-columns: minmax(0, 2.3fr) minmax(0, 1.2fr);
            gap: 24px;
        }

        .cq-card {
            background: radial-gradient(circle at top left, #1e293b 0, #020617 60%);
            border-radius: 18px;
            padding: 20px 22px;
            border: 1px solid rgba(148,163,184,0.25);
            box-shadow: 0 22px 40px rgba(15,23,42,0.8);
        }

        .cq-card h2 {
            font-size: 18px;
            margin-bottom: 6px;
        }

        .cq-subtitle {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 18px;
        }

        .cq-label {
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .cq-select {
            width: 100%;
            background: rgba(15,23,42,0.9);
            border-radius: 999px;
            border: 1px solid rgba(148,163,184,0.7);
            color: #e5e7eb;
            padding: 10px 14px;
            font-size: 14px;
            outline: none;
        }

        .cq-select:focus {
            border-color: #facc15;
            box-shadow: 0 0 0 1px rgba(250,204,21,0.7);
        }

        .cq-button-row {
            margin-top: 16px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .cq-primary-btn {
            border: none;
            padding: 10px 22px;
            border-radius: 999px;
            background: radial-gradient(circle at top left, #facc15, #f59e0b);
            color: #111827;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 14px 32px rgba(250,204,21,0.4);
        }

        .cq-primary-btn:hover {
            transform: translateY(-1px);
        }

        .cq-helper {
            font-size: 12px;
            color: #9ca3af;
        }

        .cq-forensic-result {
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px dashed rgba(148,163,184,0.4);
        }

        .cq-result-label {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .cq-result-main {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .cq-result-percent {
            font-size: 32px;
            font-weight: 700;
            color: #facc15;
        }

        .cq-result-unit {
            font-size: 16px;
            color: #e5e7eb;
        }

        .cq-meter-shell {
            margin-top: 14px;
        }

        .cq-meter-track {
            width: 100%;
            height: 12px;
            border-radius: 999px;
            background: radial-gradient(circle at top left, #020617, #111827);
            overflow: hidden;
            border: 1px solid rgba(148,163,184,0.5);
        }

        .cq-meter-fill {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #22c55e, #facc15, #f97316, #ef4444);
            transition: width 1.2s ease-out;
        }

        .cq-meter-scale {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .cq-result-message {
            margin-top: 12px;
            font-size: 14px;
            color: #e5e7eb;
        }

        .cq-evidence-bag-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .cq-evidence-count {
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .cq-evidence-list {
            list-style: disc;
            padding-left: 18px;
            font-size: 13px;
            max-height: 260px;
            overflow-y: auto;
        }

        .cq-evidence-list li {
            margin-bottom: 6px;
        }

        .cq-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid rgba(148,163,184,0.6);
            color: #e5e7eb;
            text-decoration: none;
        }

        .cq-back-link:hover {
            background: rgba(15,23,42,0.8);
        }

        @media (max-width: 900px) {
            .cq-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="cq-shell">
    <div class="cq-header-row">
        <a href="case_dashboard.php?case_id=<?php echo $caseId; ?>" class="cq-back-link">
            ← Back to Case
        </a>
        <div class="cq-badge">Forensic Lab</div>
    </div>

    <h1 class="cq-title">Fingerprint & Trace Analyzer</h1>

    <div class="cq-layout">
        <!-- LEFT: Forensic analyzer -->
        <div class="cq-card">
            <h2>Run Evidence Forensics</h2>
            <p class="cq-subtitle">
                Choose a piece of collected evidence and let the lab estimate how strongly it matches key suspects and timelines.
                Not every lead will be solid—some will be deliberate dead ends.
            </p>

            <form method="post">
                <div class="cq-label">Evidence Input</div>
                <select name="evidence_key" class="cq-select" required>
                    <option value="">Select evidence from your bag…</option>
                    <?php foreach ($evidenceForCase as $key => $item): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>"
                            <?php echo ($key === $selectedKey) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($item['label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="cq-button-row">
                    <button type="submit" class="cq-primary-btn">Run Forensic Scan</button>
                    <span class="cq-helper">The lab engine will approximate a match score for this clue.</span>
                </div>
            </form>

            <?php if ($matchPercent !== null): ?>
                <div class="cq-forensic-result">
                    <div class="cq-result-label">
                        Analysis Result for:
                        <strong><?php echo htmlspecialchars($selectedLabel); ?></strong>
                    </div>

                    <div class="cq-result-main">
                        <span class="cq-result-percent" id="matchPercentValue">
                            <?php echo (int)$matchPercent; ?>
                        </span>
                        <span class="cq-result-unit">% match</span>
                    </div>

                    <div class="cq-meter-shell">
                        <div class="cq-meter-track">
                            <div class="cq-meter-fill" id="matchMeter"
                                 data-target="<?php echo (int)$matchPercent; ?>"></div>
                        </div>
                        <div class="cq-meter-scale">
                            <span>Low</span>
                            <span>Medium</span>
                            <span>High</span>
                        </div>
                    </div>

                    <p class="cq-result-message">
                        <?php echo htmlspecialchars($resultMessage); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Evidence bag -->
        <div class="cq-card">
            <div class="cq-evidence-bag-title">Evidence Bag</div>
            <div class="cq-evidence-count">
                <?php echo count($evidenceForCase); ?> items collected for this case
            </div>
            <ul class="cq-evidence-list">
                <?php if ($evidenceForCase): ?>
                    <?php foreach ($evidenceForCase as $item): ?>
                        <li><?php echo htmlspecialchars($item['label']); ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No evidence logged yet. Explore the crime scene to gather clues.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script>
// Animate the meter fill when result is shown
(function () {
    var meter = document.getElementById('matchMeter');
    if (!meter) return;
    var target = parseInt(meter.getAttribute('data-target'), 10) || 0;
    // trigger CSS transition
    requestAnimationFrame(function () {
        meter.style.width = target + '%';
    });
})();
</script>
</body>
</html>

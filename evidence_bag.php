<?php
$page_subtitle = 'Evidence Bag';
require 'game_logic.php';
require 'includes/header.php';

// Case id: from query string, fall back to 1
$caseId = isset($_GET['case_id']) ? (int)$_GET['case_id'] : 1;
$bag    = get_evidence_for_case($caseId);
?>

<!-- INLINE CSS FOR THIS PAGE -->
<style>
    body {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: radial-gradient(circle at top, #111827, #020617);
        color: #e5e7eb;
        margin: 0;
        padding: 32px 12px;
    }

    nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
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

    .cq-section {
        max-width: 900px;
        margin: 0 auto;
        background: rgba(15, 23, 42, 0.96);
        border-radius: 18px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.9);
        padding: 22px 24px 26px;
    }

    .cq-section-header {
        margin-bottom: 14px;
    }

    .cq-title {
        font-size: 24px;
        margin-bottom: 4px;
    }

    .cq-subtext {
        font-size: 13px;
        color: #9ca3af;
    }

    .evidence-bag-box {
        background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent);
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.55);
        padding: 14px 16px 12px;
        margin-top: 10px;
    }

    .evidence-count {
        font-size: 13px;
        color: #e5e7eb;
        margin-bottom: 8px;
    }

    .cq-evidence-list {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .cq-evidence-list li {
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px dashed rgba(148, 163, 184, 0.4);
    }

    .cq-evidence-label {
        font-size: 13px;
        font-weight: 500;
    }

    .cq-evidence-meta {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 1px;
    }

    .cq-muted {
        font-size: 13px;
        color: #9ca3af;
    }

    .cq-back-row {
        margin-top: 16px;
        text-align: left;
    }

    .cq-back-link {
        font-size: 13px;
        color: #93c5fd;
        text-decoration: none;
    }

    .cq-back-link:hover {
        text-decoration: underline;
    }
</style>

<section class="cq-section">
    <div class="cq-section-header">
        <h1 class="cq-title">Evidence Bag</h1>
        <p class="cq-subtext">
            All pieces you’ve logged for this case. Use them to challenge suspects and reconstruct the crime.
        </p>
    </div>

    <div class="evidence-bag-box">
        <p class="evidence-count">
            <?php echo count($bag); ?> item<?php echo count($bag) === 1 ? '' : 's'; ?> collected for this case.
        </p>

        <?php if (empty($bag)): ?>
            <p class="cq-muted">No evidence collected yet. Return to the crime scene and start searching.</p>
        <?php else: ?>
            <ul class="cq-evidence-list">
                <?php foreach ($bag as $item): ?>
                    <li>
                        <div class="cq-evidence-label">
                            <?php echo htmlspecialchars($item['label']); ?>
                        </div>
                        <div class="cq-evidence-meta">
                            Logged at <?php echo htmlspecialchars($item['time']); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="cq-back-row">
        <a href="case_dashboard.php?case_id=<?php echo $caseId; ?>" class="cq-back-link">← Back to Case Dashboard</a>
    </div>
</section>

<?php require 'includes/footer.php'; ?>

<?php
$page_subtitle = 'The Vanishing Bracelet / Crime Scene';
require 'game_logic.php';
require 'includes/header.php';

$caseId = 1; // if you want dynamic later: (int)($_GET['case_id'] ?? 1);

// --- LEADS CONFIG: good vs dead-end ---
$zones = [
    'desk' => [
        'title' => 'Search the Desk',
        'tagline' => 'Check drawers, papers, and personal items.',
        'leads' => [
            'desk_invitation' => [
                'label' => 'Gala invitation with odd time stamp',
                'description' => 'The printed time doesn’t match the official gala schedule.',
                'type' => 'good'
            ],
            'desk_old_note' => [
                'label' => 'Old handwritten note',
                'description' => 'Looks dramatic, but it’s from weeks ago. A distraction.',
                'type' => 'dead'
            ],
        ],
    ],
    'window' => [
        'title' => 'Check the Window',
        'tagline' => 'Look for signs of exit or entry.',
        'leads' => [
            'window_footprints' => [
                'label' => 'Smudged footprints by window',
                'description' => 'Footprints suggest someone left in a hurry.',
                'type' => 'good'
            ],
        ],
    ],
    'floor' => [
        'title' => 'Inspect the Floor',
        'tagline' => 'Look beneath furniture for dropped evidence.',
        'leads' => [
            'floor_glass' => [
                'label' => 'Tiny glass shards',
                'description' => 'Shards from the display case lie under the table.',
                'type' => 'good'
            ],
            'floor_confetti' => [
                'label' => 'Old party confetti',
                'description' => 'From a previous event – unrelated to this crime.',
                'type' => 'dead'
            ],
        ],
    ],
];

$selectedLeadKey = $_GET['lead'] ?? null;
$currentMessage = null;

// when a lead is clicked -> add to evidence bag if it is a good one
if ($selectedLeadKey) {
    foreach ($zones as $zoneId => $zone) {
        if (isset($zone['leads'][$selectedLeadKey])) {
            $lead = $zone['leads'][$selectedLeadKey];

            if ($lead['type'] === 'good') {
                add_evidence($selectedLeadKey, $lead['label'], $caseId);
                $currentMessage = [
                    'status' => 'good',
                    'title'  => 'Promising Lead',
                    'text'   => $lead['description']
                ];
            } else {
                $currentMessage = [
                    'status' => 'dead',
                    'title'  => 'Dead End',
                    'text'   => $lead['description'] . ' It doesn’t move the case forward.'
                ];
            }
            break;
        }
    }
}

$evidenceForCase = get_evidence_for_case($caseId);
?>

<!-- INLINE CSS JUST FOR THIS PAGE -->
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
        max-width: 1100px;
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

    .cq-grid {
        display: grid;
        gap: 18px;
    }

    .cq-grid-3 {
        grid-template-columns: minmax(0, 2.1fr) minmax(0, 1.7fr) minmax(0, 1.3fr);
    }

    .cq-panel {
        background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent);
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.55);
        padding: 14px 16px 12px;
    }

    .cq-panel-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .cq-panel-sub {
        font-size: 12px;
        color: #9ca3af;
        margin-bottom: 10px;
    }

    .cq-zone-block {
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px dashed rgba(148, 163, 184, 0.4);
    }

    .cq-zone-header {
        font-size: 14px;
        font-weight: 600;
    }

    .cq-zone-tagline {
        font-size: 12px;
        color: #9ca3af;
        margin-bottom: 4px;
    }

    .cq-lead-list {
        list-style: none;
        padding-left: 0;
        margin: 0;
    }

    .cq-lead-list li {
        margin-bottom: 4px;
    }

    .cq-lead-link {
        font-size: 13px;
        color: #93c5fd;
        text-decoration: none;
    }

    .cq-lead-link:hover {
        text-decoration: underline;
    }

    .cq-callout {
        border-radius: 12px;
        padding: 10px 12px;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .cq-callout-good {
        background: rgba(22, 163, 74, 0.12);
        border: 1px solid rgba(22, 163, 74, 0.7);
    }

    .cq-callout-dead {
        background: rgba(248, 113, 113, 0.12);
        border: 1px solid rgba(248, 113, 113, 0.7);
    }

    .cq-callout-label {
        font-weight: 600;
        margin-bottom: 3px;
    }

    .cq-callout-hint {
        font-size: 12px;
        color: #e5e7eb;
        margin-top: 4px;
    }

    .cq-muted {
        font-size: 13px;
        color: #9ca3af;
    }

    .cq-tip-box {
        margin-top: 10px;
        font-size: 12px;
        padding: 8px 10px;
        border-radius: 10px;
        background: rgba(15, 23, 42, 0.9);
        border: 1px dashed rgba(148, 163, 184, 0.7);
        color: #e5e7eb;
    }

    .cq-panel-right {
        background: radial-gradient(circle at top right, rgba(56, 189, 248, 0.16), transparent);
    }

    .cq-evidence-list {
        list-style: none;
        padding-left: 0;
        margin: 8px 0 0;
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

    @media (max-width: 900px) {
        .cq-grid-3 {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="cq-section">
    <div class="cq-section-header">
        <h1 class="cq-title">Study the Scene</h1>
        <p class="cq-subtext">
            The room is quiet, but it’s screaming with clues. Hover over zones and trust your instincts.
        </p>
    </div>

    <div class="cq-grid cq-grid-3">
        <!-- Left: Zone list -->
        <div class="cq-panel">
            <h2 class="cq-panel-title">Search Zones</h2>
            <p class="cq-panel-sub">
                Some paths reveal the truth. Others are deliberate dead ends.
            </p>

            <?php foreach ($zones as $zoneId => $zone): ?>
                <div class="cq-zone-block">
                    <div class="cq-zone-header"><?php echo htmlspecialchars($zone['title']); ?></div>
                    <div class="cq-zone-tagline"><?php echo htmlspecialchars($zone['tagline']); ?></div>
                    <ul class="cq-lead-list">
                        <?php foreach ($zone['leads'] as $leadKey => $lead): ?>
                            <li>
                                <a class="cq-lead-link"
                                   href="crime_scene.php?lead=<?php echo urlencode($leadKey); ?>">
                                    <?php echo htmlspecialchars($lead['label']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Middle: Result card -->
        <div class="cq-panel">
            <h2 class="cq-panel-title">Scene Feedback</h2>
            <?php if ($currentMessage): ?>
                <div class="cq-callout cq-callout-<?php echo $currentMessage['status']; ?>">
                    <div class="cq-callout-label">
                        <?php echo htmlspecialchars($currentMessage['title']); ?>
                    </div>
                    <p><?php echo htmlspecialchars($currentMessage['text']); ?></p>
                    <?php if ($currentMessage['status'] === 'good'): ?>
                        <p class="cq-callout-hint">
                            The evidence bag has been updated. This might connect to a suspect’s story later.
                        </p>
                    <?php else: ?>
                        <p class="cq-callout-hint">
                            Not every clue matters. Wasting time on dead ends affects your final score.
                        </p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="cq-muted">
                    Select a lead on the left to inspect it. Good leads strengthen your case; dead ends teach you what
                    to ignore.
                </p>
            <?php endif; ?>

            <div class="cq-tip-box">
                <strong>Tip:</strong> Try to build a timeline in your head – time stamps, footprints, and broken glass
                all need to agree.
            </div>
        </div>

        <!-- Right: Evidence bag -->
        <aside class="cq-panel cq-panel-right">
            <h2 class="cq-panel-title">Evidence Bag</h2>
            <p class="cq-panel-sub">
                <?php echo count($evidenceForCase); ?> items collected for this case.
            </p>
            <?php if ($evidenceForCase): ?>
                <ul class="cq-evidence-list">
                    <?php foreach ($evidenceForCase as $id => $item): ?>
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
            <?php else: ?>
                <p class="cq-muted">No evidence yet. Start exploring the zones.</p>
            <?php endif; ?>
        </aside>
    </div>

    <div class="cq-back-row">
        <a href="case_dashboard.php?case_id=<?php echo $caseId; ?>" class="cq-back-link">← Back to Case Dashboard</a>
    </div>
</section>

<?php require 'includes/footer.php'; ?>

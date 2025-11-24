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

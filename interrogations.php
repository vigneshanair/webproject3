<?php
$page_subtitle = 'The Vanishing Bracelet / Suspect Interrogations';
require 'game_logic.php';
require 'includes/header.php';

$caseId    = 1;
$caseTitle = 'The Vanishing Bracelet';

// static suspect + dialogue tree for Case 1
$suspects = [
    'lena' => [
        'name' => 'Lena Hart',
        'role' => 'Personal Assistant',
        'traits' => 'Organised, nervous',
        'truth_profile' => 'Mixed – hides emotional motives.',
        'questions' => [
            'motive' => [
                'label' => 'Why would anyone want to steal the bracelet?',
                'is_truth' => false,
                'response' => '“People do desperate things when they feel ignored.” She avoids eye contact.',
                'hint' => 'Her answer deflects. This feels like a partial lie.',
            ],
            'where' => [
                'label' => 'Where were you during the theft?',
                'is_truth' => false,
                'response' => '“On duty near the entrance the whole time.”',
                'hint' => 'Your timeline and prints say otherwise.',
            ],
            'relationship' => [
                'label' => 'How long have you worked for the victim?',
                'is_truth' => true,
                'response' => '“Seven years. I managed everything – schedules, events, even personal errands.”',
                'hint' => 'Details line up with HR records. Likely true.',
            ],
        ],
    ],
    'marco' => [
        'name' => 'Marco Vance',
        'role' => 'Security Guard',
        'traits' => 'Charming, evasive',
        'truth_profile' => 'Often hides details to protect himself.',
        'questions' => [
            'route' => [
                'label' => 'Which patrol route were you on?',
                'is_truth' => true,
                'response' => '“North hallway, then terrace. Cameras will show I stuck to the route.”',
                'hint' => 'Camera logs partially confirm this.',
            ],
            'bracelet' => [
                'label' => 'Did you know the bracelet was so valuable?',
                'is_truth' => false,
                'response' => '“No idea, it just looked shiny.”',
                'hint' => 'Security briefing notes prove otherwise.',
            ],
        ],
    ],
    'iris' => [
        'name' => 'Iris Cole',
        'role' => 'Art Dealer',
        'traits' => 'Observant, calm',
        'truth_profile' => 'Mostly truthful, but selectively silent.',
        'questions' => [
            'argument' => [
                'label' => 'Did you hear an argument before the theft?',
                'is_truth' => true,
                'response' => '“Yes. Raised voices near the study. I recognised Lena’s voice.”',
                'hint' => 'Matches other witness statements.',
            ],
            'money' => [
                'label' => 'Were you in any financial trouble?',
                'is_truth' => false,
                'response' => '“Business is booming. No problems at all.”',
                'hint' => 'Bank records show short-term debt – minor, but she’s hiding it.',
            ],
        ],
    ],
];

$currentSuspectKey = $_GET['suspect'] ?? 'lena';
if (!isset($suspects[$currentSuspectKey])) {
    $currentSuspectKey = 'lena';
}
$currentSuspect = $suspects[$currentSuspectKey];

$currentQuestionKey = $_GET['q'] ?? null;
$currentAnswer = null;

if ($currentQuestionKey && isset($currentSuspect['questions'][$currentQuestionKey])) {
    $q = $currentSuspect['questions'][$currentQuestionKey];
    $currentAnswer = $q;
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
        grid-template-columns: minmax(0, 1.5fr) minmax(0, 2fr) minmax(0, 1.4fr);
    }

    .cq-panel {
        background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent);
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.55);
        padding: 14px 16px 12px;
    }

    .cq-panel-right {
        background: radial-gradient(circle at top right, rgba(56, 189, 248, 0.18), transparent);
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

    .cq-muted {
        font-size: 13px;
        color: #9ca3af;
    }

    /* Suspect list */
    .cq-suspect-list {
        list-style: none;
        padding-left: 0;
        margin: 6px 0 0;
    }

    .cq-suspect-list li {
        margin-bottom: 6px;
    }

    .cq-suspect-link {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 6px 8px;
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid transparent;
        font-size: 13px;
        color: #e5e7eb;
    }

    .cq-suspect-list li.active .cq-suspect-link {
        border-color: rgba(56, 189, 248, 0.9);
        background: radial-gradient(circle at left, rgba(56, 189, 248, 0.18), rgba(15, 23, 42, 0.9));
    }

    .cq-suspect-link:hover {
        border-color: rgba(56, 189, 248, 0.7);
        background: rgba(15, 23, 42, 0.9);
    }

    .cq-suspect-name {
        font-weight: 600;
    }

    .cq-suspect-role {
        font-size: 11px;
        color: #9ca3af;
    }

    /* Dialogue area */
    .cq-dialogue-area {
        margin-top: 6px;
        margin-bottom: 10px;
    }

    .cq-dialogue-bubble {
        border-radius: 12px;
        padding: 8px 10px;
        font-size: 13px;
        margin-bottom: 6px;
    }

    .cq-dialogue-player {
        background: rgba(17, 24, 39, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.7);
    }

    .cq-dialogue-suspect {
        background: rgba(30, 64, 175, 0.25);
        border: 1px solid rgba(59, 130, 246, 0.9);
    }

    .cq-truth-tag {
        display: inline-block;
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 999px;
        margin-top: 2px;
        margin-bottom: 6px;
    }

    .cq-truth-true {
        background: rgba(22, 163, 74, 0.16);
        border: 1px solid rgba(34, 197, 94, 0.9);
        color: #bbf7d0;
    }

    .cq-truth-false {
        background: rgba(248, 113, 113, 0.16);
        border: 1px solid rgba(248, 113, 113, 0.9);
        color: #fecaca;
    }

    .cq-hint-text {
        font-size: 12px;
        color: #e5e7eb;
        margin-bottom: 6px;
    }

    .cq-question-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .cq-pill-button {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 999px;
        text-decoration: none;
        border: 1px solid rgba(148, 163, 184, 0.7);
        color: #e5e7eb;
        background: radial-gradient(circle at top left, rgba(56, 189, 248, 0.12), transparent);
    }

    .cq-pill-button:hover {
        background: rgba(30, 64, 175, 0.6);
    }

    /* Evidence bag (reuse from crime scene) */
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
        <h1 class="cq-title">Suspect Interrogations</h1>
        <p class="cq-subtext">
            Choose a suspect, select your questions carefully. Some responses reveal the truth; others are lies or dead ends.
        </p>
    </div>

    <div class="cq-grid cq-grid-3">
        <!-- Left: suspect list -->
        <aside class="cq-panel">
            <h2 class="cq-panel-title">Suspects</h2>
            <ul class="cq-suspect-list">
                <?php foreach ($suspects as $key => $suspect): ?>
                    <li class="<?php echo $key === $currentSuspectKey ? 'active' : ''; ?>">
                        <a href="interrogations.php?suspect=<?php echo urlencode($key); ?>" class="cq-suspect-link">
                            <span class="cq-suspect-name"><?php echo htmlspecialchars($suspect['name']); ?></span>
                            <span class="cq-suspect-role"><?php echo htmlspecialchars($suspect['role']); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <!-- Middle: dialogue area -->
        <div class="cq-panel">
            <h2 class="cq-panel-title"><?php echo htmlspecialchars($currentSuspect['name']); ?></h2>
            <p class="cq-panel-sub">
                Traits: <?php echo htmlspecialchars($currentSuspect['traits']); ?>.
                Truth pattern: <?php echo htmlspecialchars($currentSuspect['truth_profile']); ?>
            </p>

            <div class="cq-dialogue-area">
                <?php if ($currentAnswer): ?>
                    <div class="cq-dialogue-bubble cq-dialogue-player">
                        <strong>You:</strong><br>
                        <?php echo htmlspecialchars($currentAnswer['label']); ?>
                    </div>
                    <div class="cq-dialogue-bubble cq-dialogue-suspect">
                        <strong><?php echo htmlspecialchars($currentSuspect['name']); ?>:</strong><br>
                        <?php echo htmlspecialchars($currentAnswer['response']); ?>
                    </div>
                    <div class="cq-truth-tag cq-truth-<?php echo $currentAnswer['is_truth'] ? 'true' : 'false'; ?>">
                        <?php echo $currentAnswer['is_truth'] ? 'Feels truthful' : 'Feels deceptive'; ?>
                    </div>
                    <p class="cq-hint-text">
                        <?php echo htmlspecialchars($currentAnswer['hint']); ?>
                    </p>
                <?php else: ?>
                    <p class="cq-muted">
                        Start by selecting a question below to interrogate this suspect.
                        Watch how their answers line up with your evidence bag.
                    </p>
                <?php endif; ?>
            </div>

            <div class="cq-question-row">
                <?php foreach ($currentSuspect['questions'] as $qKey => $q): ?>
                    <a class="cq-pill-button"
                       href="interrogations.php?suspect=<?php echo urlencode($currentSuspectKey); ?>&q=<?php echo urlencode($qKey); ?>">
                        <?php echo htmlspecialchars($q['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right: Evidence bag -->
        <aside class="cq-panel cq-panel-right">
            <h2 class="cq-panel-title">Evidence Bag</h2>
            <p class="cq-panel-sub"><?php echo count($evidenceForCase); ?> items collected.</p>
            <?php if ($evidenceForCase): ?>
                <ul class="cq-evidence-list">
                    <?php foreach ($evidenceForCase as $item): ?>
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
                <p class="cq-muted">
                    You have no evidence yet. Interrogations are stronger when backed by hard facts from the scene.
                </p>
            <?php endif; ?>
        </aside>
    </div>

    <div class="cq-back-row">
        <a href="case_dashboard.php?case_id=<?php echo $caseId; ?>" class="cq-back-link">← Back to Case Dashboard</a>
    </div>
</section>

<?php require 'includes/footer.php'; ?>

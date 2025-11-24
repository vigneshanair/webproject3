<?php
$page_subtitle = 'The Vanishing Bracelet / Suspect Interrogations';
require 'game_logic.php';
require 'includes/header.php';

$caseId   = 1;
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

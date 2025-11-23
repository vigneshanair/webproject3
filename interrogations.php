<?php
require_once 'game_state.php';
require_detective();

$caseId = (int)($_GET['case'] ?? 1);
$cases  = get_cases();
if (!isset($cases[$caseId])) {
    header('Location: cases.php');
    exit;
}

$suspects = get_suspects_for_case($caseId);
$currentSuspectId = (int)($_GET['suspect'] ?? 1);
if (!isset($suspects[$currentSuspectId])) {
    $currentSuspectId = array_key_first($suspects);
}

$dialogueLogKey = "dialogue_case_{$caseId}_suspect_{$currentSuspectId}";
if (!isset($_SESSION[$dialogueLogKey])) {
    $_SESSION[$dialogueLogKey] = [];
}

$response = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_notes'])) {
        save_notes_for_case($caseId, $_POST['notes'] ?? '');
    }
    if (isset($_POST['question_id'])) {
        $q = $_POST['question_id'];
        $suspectName = $suspects[$currentSuspectId]['name'];

        switch ($q) {
            case 'alibi':
                $response = "$suspectName hesitates and says they were handling security checks during the time of the theft.";
                add_evidence($caseId, "alibi_$currentSuspectId", "$suspectName claims to be on duty during the theft.");
                set_case_progress($caseId, max(get_case_progress($caseId), 65));
                break;
            case 'relationship':
                $response = "$suspectName admits they’ve worked with the victim for years but avoids emotional details.";
                add_evidence($caseId, "relationship_$currentSuspectId", "$suspectName has a long-term working relationship with the victim.");
                break;
            case 'contradiction':
                $response = "You confront $suspectName with the gala invitation time. They stumble over their explanation.";
                add_evidence($caseId, "lie_$currentSuspectId", "$suspectName’s story doesn’t match the invitation time.");
                set_case_progress($caseId, max(get_case_progress($caseId), 80));
                break;
            case 'motive':
                $response = "$suspectName finally snaps: ‘You don’t understand what they did to me.’ A strong motive emerges.";
                add_evidence($caseId, "motive_$currentSuspectId", "$suspectName shows strong resentment towards the victim.");
                set_case_progress($caseId, max(get_case_progress($caseId), 90));
                break;
            default:
                $response = "$suspectName shrugs and refuses to add anything more.";
        }

        $_SESSION[$dialogueLogKey][] = [
            'question' => $q,
            'response' => $response,
        ];
    }
}

$dialogueLog    = $_SESSION[$dialogueLogKey];
$currentSuspect = $suspects[$currentSuspectId];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interrogations – <?php echo htmlspecialchars(get_case_title($caseId)); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php render_header('Suspect Interrogations'); ?>

<main class="main-layout with-sidebar">
    <section class="interrogation-room">
        <a href="case_dashboard.php?case=<?php echo $caseId; ?>" class="back-button">← Back to Case Dashboard</a>

        <header class="section-header">
            <h2>Suspect Interrogations</h2>
            <p>Choose a suspect, read their profile, and ask targeted questions.</p>
        </header>

        <div class="interrogation-layout">
            <aside class="suspect-list-panel">
                <h3>Suspects</h3>
                <ul class="suspect-list">
                    <?php foreach ($suspects as $id => $s): ?>
                        <li class="<?php echo $id === $currentSuspectId ? 'active-suspect' : ''; ?>">
                            <a href="interrogations.php?case=<?php echo $caseId; ?>&suspect=<?php echo $id; ?>">
                                <div class="suspect-row">
                                    <img src="<?php echo htmlspecialchars($s['image']); ?>"
                                         alt="<?php echo htmlspecialchars($s['name']); ?>"
                                         class="suspect-avatar-small">
                                    <div class="suspect-row-text">
                                        <span class="suspect-name"><?php echo htmlspecialchars($s['name']); ?></span>
                                        <span class="suspect-role"><?php echo htmlspecialchars($s['role']); ?></span>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="suspect-detail-panel">
                <div class="suspect-profile-card">
                    <img
                        src="<?php echo htmlspecialchars($currentSuspect['image']); ?>"
                        alt="<?php echo htmlspecialchars($currentSuspect['name']); ?>"
                        class="suspect-avatar-large"
                    >
                    <div class="suspect-info">
                        <h3><?php echo htmlspecialchars($currentSuspect['name']); ?></h3>
                        <p class="suspect-role-line"><?php echo htmlspecialchars($currentSuspect['role']); ?></p>
                        <p class="suspect-traits">
                            Traits:
                            <?php echo htmlspecialchars(implode(', ', $currentSuspect['traits'])); ?>
                        </p>
                    </div>
                </div>

                <div class="dialogue-section">
                    <div class="dialogue-log">
                        <?php if (empty($dialogueLog)): ?>
                            <p class="dialogue-placeholder">
                                The room is dim, a single light overhead. Start the interrogation by choosing a question.
                            </p>
                        <?php else: ?>
                            <?php foreach ($dialogueLog as $entry): ?>
                                <div class="dialogue-entry">
                                    <div class="dialogue-question">
                                        <span class="speaker-label">You:</span>
                                        <span class="dialogue-text">
                                            <?php
                                            switch ($entry['question']) {
                                                case 'alibi': echo '“Where were you during the time of the theft?”'; break;
                                                case 'relationship': echo '“How did you know the victim?”'; break;
                                                case 'contradiction': echo '“Your story doesn’t match the invitation time. Explain.”'; break;
                                                case 'motive': echo '“Why would anyone want to steal that bracelet?”'; break;
                                                default: echo '“Tell me more about that night.”';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <div class="dialogue-response">
                                        <span class="speaker-label"><?php echo htmlspecialchars($currentSuspect['name']); ?>:</span>
                                        <span class="dialogue-text"><?php echo htmlspecialchars($entry['response']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form method="post" class="question-choices">
                        <h4>Choose Your Question</h4>
                        <div class="question-buttons">
                            <button type="submit" name="question_id" value="alibi" class="btn-secondary">
                                Ask about their alibi
                            </button>
                            <button type="submit" name="question_id" value="relationship" class="btn-secondary">
                                Ask about their relationship with the victim
                            </button>
                            <button type="submit" name="question_id" value="contradiction" class="btn-secondary">
                                Confront inconsistencies
                            </button>
                            <button type="submit" name="question_id" value="motive" class="btn-secondary">
                                Push on possible motive
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php render_evidence_bag_sidebar($caseId); ?>
    <?php render_notebook($caseId); ?>
</main>
</body>
</html>

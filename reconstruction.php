<?php
require_once __DIR__ . '/game_logic.php';

$caseId   = isset($_GET['case']) ? (int)$_GET['case'] : 1;
$playerId = get_player_id($pdo);
if (!$playerId) { header('Location:index.php'); exit; }

$difficulty = get_difficulty_for_player($pdo,$playerId,$caseId);

$slots = [
  'display_case'   => 'glass',
  'security_door'  => 'fibers',
  'painting_frame' => 'fingerprint',
];

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correct = true;
    foreach ($slots as $slotId => $required) {
        $submitted = $_POST['slot_'.$slotId] ?? '';
        if ($submitted !== $required) $correct = false;
    }
    if ($correct) {
        $message = "You correctly reconstructed the crime scene.";
        $success = true;
        add_evidence('scene_reconstructed','Crime scene successfully reconstructed',$caseId);
        increment_correct_interrogations($pdo,$playerId,$caseId);
    } else {
        $message = "The reconstruction seems off. Double-check your placements.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Crime Scene Reconstruction</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="main-header">
  <h1>Crime Scene Reconstruction</h1>
  <a href="case_dashboard.php?case=<?= $caseId ?>">← Back to Case</a>
</header>
<main class="content">
  <?php if ($difficulty === 'easy'): ?>
    <div class="hint-box">
      <strong>Hint:</strong> Glass → display case, fibers → door, fingerprint → frame.
    </div>
  <?php elseif ($difficulty === 'hard'): ?>
    <div class="hint-box hard">
      <strong>Hard Mode:</strong> No obvious hints. Think logically.
    </div>
  <?php endif; ?>

  <?php if ($message): ?>
    <div class="result-box <?= $success?'success':'failure' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="post" class="reconstruct-form">
    <div class="reconstruct-layout">
      <div class="reconstruct-scene">
        <div class="scene-title">Gallery Layout</div>

        <div class="reconstruct-slot" data-slot-id="display_case">
          <span class="slot-label">Display Case</span>
          <div class="slot-dropzone"></div>
        </div>

        <div class="reconstruct-slot" data-slot-id="security_door">
          <span class="slot-label">Security Door</span>
          <div class="slot-dropzone"></div>
        </div>

        <div class="reconstruct-slot" data-slot-id="painting_frame">
          <span class="slot-label">Painting Frame</span>
          <div class="slot-dropzone"></div>
        </div>
      </div>

      <div class="reconstruct-evidence-tray">
        <h3>Evidence Pieces</h3>
        <div class="evidence-tokens">
          <div class="evidence-token" draggable="true" data-evidence-id="glass">Shattered Glass</div>
          <div class="evidence-token" draggable="true" data-evidence-id="fibers">Glove Fibers</div>
          <div class="evidence-token" draggable="true" data-evidence-id="fingerprint">Fingerprint Fragment</div>
        </div>

        <?php $caseIdForEvidence = $caseId; include __DIR__ . '/evidence_bag.php'; ?>
      </div>
    </div>

    <?php foreach ($slots as $slotId => $_): ?>
      <input type="hidden" name="slot_<?= $slotId ?>" id="slot_<?= $slotId ?>" value="">
    <?php endforeach; ?>

    <div class="reconstruct-actions">
      <button type="submit">Submit Reconstruction</button>
    </div>
  </form>

  <button class="notebook-btn" onclick="openNotebook()">📝 Notebook</button>
  <div id="notebookModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeNotebook()">&times;</span>
      <h3>Case <?= $caseId ?> Notebook</h3>
      <form method="post" action="notebook.php?case=<?= $caseId ?>&from=reconstruction">
        <textarea name="notes" rows="8"><?= htmlspecialchars(get_notebook($caseId)) ?></textarea>
        <button type="submit">Save Notes</button>
      </form>
    </div>
  </div>
</main>
<script>
const tokens = document.querySelectorAll('.evidence-token');
const dropzones = document.querySelectorAll('.slot-dropzone');

tokens.forEach(t => {
  t.addEventListener('dragstart', e => {
    e.dataTransfer.setData('text/plain', t.dataset.evidenceId);
  });
});

dropzones.forEach(zone => {
  zone.addEventListener('dragover', e => e.preventDefault());
  zone.addEventListener('drop', e => {
    e.preventDefault();
    const id = e.dataTransfer.getData('text/plain');
    if (!id) return;
    zone.textContent = '';
    const span = document.createElement('span');
    span.classList.add('placed-evidence');
    span.textContent = displayLabel(id);
    zone.appendChild(span);
    const slotId = zone.closest('.reconstruct-slot').dataset.slotId;
    document.getElementById('slot_'+slotId).value = id;
  });
});

function displayLabel(id){
  if (id==='glass') return 'Shattered Glass';
  if (id==='fibers') return 'Glove Fibers';
  if (id==='fingerprint') return 'Fingerprint Fragment';
  return id;
}
function openNotebook(){ document.getElementById('notebookModal').style.display='block'; }
function closeNotebook(){ document.getElementById('notebookModal').style.display='none'; }
</script>
</body>
</html>

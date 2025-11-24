<?php
require_once __DIR__ . '/game_logic.php';

$caseId   = isset($_GET['case']) ? (int)$_GET['case'] : 1;
$playerId = get_player_id($pdo);
if (!$playerId) { header('Location:index.php'); exit; }

$difficulty = get_difficulty_for_player($pdo, $playerId, $caseId);

// Evidence bag for sidebar
$evidenceForCase = get_evidence_for_case($caseId);

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
  <style>
    body {
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: radial-gradient(circle at top, #111827, #020617);
      color: #e5e7eb;
      margin: 0;
      padding: 32px 12px;
    }

    .main-header {
      max-width: 1100px;
      margin: 0 auto 18px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #e5e7eb;
    }

    .main-header h1 {
      font-size: 24px;
      margin: 0;
    }

    .main-header a {
      font-size: 13px;
      color: #93c5fd;
      text-decoration: none;
    }

    .main-header a:hover {
      text-decoration: underline;
    }

    .content {
      max-width: 1100px;
      margin: 0 auto;
      background: rgba(15,23,42,0.96);
      border-radius: 18px;
      border: 1px solid rgba(148,163,184,0.5);
      box-shadow: 0 24px 60px rgba(15,23,42,0.9);
      padding: 20px 22px 26px;
    }

    .hint-box,
    .hint-box.hard {
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 13px;
      margin-bottom: 14px;
    }

    .hint-box {
      background: rgba(22,163,74,0.12);
      border: 1px solid rgba(34,197,94,0.8);
      color: #bbf7d0;
    }

    .hint-box.hard {
      background: rgba(248,113,113,0.12);
      border-color: rgba(248,113,113,0.9);
      color: #fecaca;
    }

    .result-box {
      border-radius: 12px;
      padding: 10px 12px;
      font-size: 13px;
      margin-bottom: 14px;
    }

    .result-box.success {
      background: rgba(22,163,74,0.16);
      border: 1px solid rgba(34,197,94,0.8);
    }

    .result-box.failure {
      background: rgba(248,113,113,0.16);
      border: 1px solid rgba(248,113,113,0.8);
    }

    .reconstruct-layout {
      display: grid;
      grid-template-columns: minmax(0, 2fr) minmax(0, 1.5fr);
      gap: 20px;
      margin-top: 8px;
    }

    .reconstruct-scene,
    .reconstruct-evidence-tray {
      background: radial-gradient(circle at top left, rgba(56,189,248,0.16), transparent);
      border-radius: 16px;
      border: 1px solid rgba(148,163,184,0.5);
      padding: 14px 16px 16px;
    }

    .scene-title {
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .reconstruct-slot {
      margin-bottom: 10px;
    }

    .slot-label {
      display: block;
      font-size: 13px;
      margin-bottom: 4px;
      color: #e5e7eb;
    }

    .slot-dropzone {
      min-height: 46px;
      border-radius: 12px;
      border: 1px dashed rgba(148,163,184,0.7);
      background: rgba(15,23,42,0.9);
      padding: 8px 10px;
      display: flex;
      align-items: center;
      font-size: 13px;
      color: #9ca3af;
    }

    .placed-evidence {
      font-weight: 500;
      color: #e5e7eb;
    }

    .reconstruct-evidence-tray h3 {
      margin: 0 0 8px;
      font-size: 15px;
    }

    .evidence-tokens {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 14px;
    }

    .evidence-token {
      padding: 8px 10px;
      border-radius: 999px;
      background: radial-gradient(circle at top left, #1f2937, #020617);
      border: 1px solid rgba(148,163,184,0.7);
      font-size: 13px;
      cursor: grab;
      user-select: none;
    }

    .evidence-token:active {
      cursor: grabbing;
    }

    .reconstruct-actions {
      margin-top: 16px;
      text-align: right;
    }

    .reconstruct-actions button {
      border: none;
      padding: 10px 22px;
      border-radius: 999px;
      background: linear-gradient(to right, #22c55e, #16a34a);
      color: #f9fafb;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 12px 24px rgba(22,163,74,0.6);
    }

    .notebook-btn {
      margin-top: 12px;
      border-radius: 999px;
      border: 1px solid rgba(148,163,184,0.7);
      background: rgba(15,23,42,0.9);
      color: #e5e7eb;
      padding: 8px 14px;
      font-size: 13px;
      cursor: pointer;
    }

    /* Evidence bag sidebar inside tray */
    .cq-evidence-bag-title {
      font-size: 13px;
      font-weight: 600;
      margin-top: 6px;
      margin-bottom: 4px;
    }

    .cq-evidence-count {
      font-size: 12px;
      color: #9ca3af;
      margin-bottom: 4px;
    }

    .cq-evidence-list {
      list-style: disc;
      padding-left: 18px;
      font-size: 12px;
      max-height: 120px;
      overflow-y: auto;
    }

    .cq-evidence-list li {
      margin-bottom: 4px;
    }

    /* Notebook modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(15,23,42,0.8);
    }

    .modal-content {
      background-color: #0b1120;
      margin: 80px auto;
      padding: 18px 20px;
      border: 1px solid rgba(148,163,184,0.8);
      width: 90%;
      max-width: 520px;
      border-radius: 14px;
      color: #e5e7eb;
    }

    .modal-content textarea {
      width: 100%;
      background: rgba(15,23,42,0.9);
      border-radius: 10px;
      border: 1px solid rgba(148,163,184,0.7);
      color: #e5e7eb;
      padding: 8px;
      font-family: inherit;
      font-size: 13px;
    }

    .modal-content button {
      margin-top: 10px;
      padding: 8px 16px;
      border-radius: 999px;
      border: none;
      background: linear-gradient(to right, #22c55e, #16a34a);
      color: #f9fafb;
      font-size: 13px;
      cursor: pointer;
    }

    .close {
      float: right;
      font-size: 20px;
      cursor: pointer;
    }

    @media (max-width: 900px) {
      .reconstruct-layout {
        grid-template-columns: 1fr;
      }
    }
  </style>
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
    <div class="result-box <?= $success ? 'success' : 'failure' ?>">
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

        <div class="cq-evidence-bag-title">Evidence Bag</div>
        <div class="cq-evidence-count">
          <?= count($evidenceForCase) ?> items collected for this case
        </div>
        <ul class="cq-evidence-list">
          <?php if ($evidenceForCase): ?>
            <?php foreach ($evidenceForCase as $item): ?>
              <li><?= htmlspecialchars($item['label']) ?></li>
            <?php endforeach; ?>
          <?php else: ?>
            <li>No evidence logged yet. Explore the crime scene to gather clues.</li>
          <?php endif; ?>
        </ul>
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

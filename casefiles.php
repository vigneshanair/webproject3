<?php
require_once __DIR__ . '/game_logic.php';

$playerId = get_player_id($pdo);
if (!$playerId) { header('Location:index.php'); exit; }

$caseId = isset($_GET['case']) ? (int)$_GET['case'] : 1;

$c = $pdo->prepare("SELECT * FROM cases WHERE id=?");
$c->execute([$caseId]);
$case = $c->fetch();
if (!$case) die('Invalid case');

$s = $pdo->prepare("SELECT * FROM suspects WHERE case_id=?");
$s->execute([$caseId]);
$suspects = $s->fetchAll();

$e = $pdo->prepare("SELECT * FROM evidence WHERE case_id=?");
$e->execute([$caseId]);
$allEvidence = $e->fetchAll();

$bag = get_evidence_for_case($caseId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Case File</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="main-header">
  <h1>Case File – Case <?= $caseId ?></h1>
  <a href="case_dashboard.php?case=<?= $caseId ?>">← Back to Case</a>
</header>
<main class="content">
  <section class="case-summary">
    <h2>Summary</h2>
    <p><?= nl2br(htmlspecialchars($case['description'])) ?></p>
  </section>

  <section class="case-suspects">
    <h2>Suspect Profiles</h2>
    <div class="suspect-grid">
      <?php foreach ($suspects as $s): ?>
        <div class="suspect-card">
          <h3><?= htmlspecialchars($s['name']) ?></h3>
          <p><strong>Role:</strong> <?= htmlspecialchars($s['role']) ?></p>
          <p><strong>Personality:</strong> <?= htmlspecialchars($s['personality']) ?></p>
          <p><?= nl2br(htmlspecialchars($s['backstory'])) ?></p>
          <?php if ($s['is_primary']): ?>
            <span class="primary-badge">Primary Suspect</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="case-evidence">
    <h2>Evidence Collected</h2>
    <?php if (empty($bag)): ?>
      <p>No evidence collected yet.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($bag as $item): ?>
          <li><?= htmlspecialchars($item['label']) ?> (<?= htmlspecialchars($item['time']) ?>)</li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <h2>Evidence Still Missing</h2>
    <ul>
      <?php foreach ($allEvidence as $ev):
        $found = false;
        foreach ($bag as $b) {
          if (stripos($b['label'],$ev['name']) !== false) { $found = true; break; }
        }
        if (!$found): ?>
          <li><?= htmlspecialchars($ev['name']) ?> – Location: <?= htmlspecialchars($ev['location']) ?></li>
        <?php endif; endforeach; ?>
    </ul>
  </section>
</main>
</body>
</html>

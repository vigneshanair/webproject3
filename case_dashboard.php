<?php
require_once __DIR__ . '/game_logic.php';

$playerId = get_player_id($pdo);
if (!$playerId) { header('Location:index.php'); exit; }

$caseId = isset($_GET['case']) ? (int)$_GET['case'] : 1;

$c = $pdo->prepare("SELECT * FROM cases WHERE id=?");
$c->execute([$caseId]);
$case = $c->fetch();
if (!$case) die('Invalid case');

$difficulty = get_difficulty_for_player($pdo,$playerId,$caseId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Case Dashboard</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="main-header">
  <h1>Case <?= $caseId ?>: <?= htmlspecialchars($case['title']) ?></h1>
  <a href="levels.php">← Back to Cases</a>
</header>
<main class="content">
  <p><?= nl2br(htmlspecialchars($case['description'])) ?></p>
  <p><strong>Dynamic Difficulty:</strong> <?= ucfirst($difficulty) ?></p>

  <div class="case-actions-grid">
    <a href="crime_scene.php?case=<?= $caseId ?>" class="card-link">
      <div class="action-card">
        <h3>Crime Scene</h3>
        <p>Explore and collect clues.</p>
      </div>
    </a>

    <a href="reconstruction.php?case=<?= $caseId ?>" class="card-link">
      <div class="action-card">
        <h3>Reconstruct Scene</h3>
        <p>Drag-and-drop evidence into position.</p>
      </div>
    </a>

    <a href="forensics.php?case=<?= $caseId ?>" class="card-link">
      <div class="action-card">
        <h3>Forensic Lab</h3>
        <p>Fingerprint analysis mini-game.</p>
      </div>
    </a>

    <a href="interrogations.php?case=<?= $caseId ?>" class="card-link">
      <div class="action-card">
        <h3>Interrogations</h3>
        <p>Dialogue trees & suspect personalities.</p>
      </div>
    </a>

    <a href="casefiles.php?case=<?= $caseId ?>" class="card-link">
      <div class="action-card">
        <h3>Case File</h3>
        <p>Digital case file with suspects & evidence.</p>
      </div>
    </a>

    <a href="verdict.php?case=<?= $caseId ?>" class="card-link">
      <div class="action-card">
        <h3>Make Accusation</h3>
        <p>Branching solutions & scoring.</p>
      </div>
    </a>
  </div>
</main>
</body>
</html>

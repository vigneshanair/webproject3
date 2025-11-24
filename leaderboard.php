<?php
require_once __DIR__ . '/game_logic.php';

$rows = $pdo->query("SELECT l.*, p.name
                     FROM leaderboard l
                     JOIN players p ON l.player_id=p.id
                     ORDER BY l.total_score DESC, l.avg_time_seconds ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Leaderboard</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="main-header">
  <h1>Leaderboard</h1>
  <a href="levels.php">← Back to Cases</a>
</header>
<main class="content">
  <table class="leaderboard-table">
    <thead>
      <tr>
        <th>Rank</th>
        <th>Detective</th>
        <th>Total Score</th>
        <th>Cases Solved</th>
        <th>Avg Time (s)</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="5">No investigations completed yet.</td></tr>
      <?php else:
        $rank = 1;
        foreach ($rows as $r): ?>
          <tr>
            <td><?= $rank++ ?></td>
            <td><?= htmlspecialchars($r['name']) ?></td>
            <td><?= (int)$r['total_score'] ?></td>
            <td><?= (int)$r['cases_solved'] ?></td>
            <td><?= (int)$r['avg_time_seconds'] ?></td>
          </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</main>
</body>
</html>

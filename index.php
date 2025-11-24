<?php
require_once __DIR__ . '/game_logic.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['detective_name'] ?? '');
    if ($name !== '') {
        $_SESSION['player_name'] = $name;
        $_SESSION['player_id']  = get_or_create_player($pdo, $name);
        header('Location: levels.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cryptic Quest – Login</title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body class="login-body">
  <div class="login-wrapper">
    <div class="login-card">
      <h1>🕵️ Cryptic Quest</h1>
      <p>Enter your detective codename to begin.</p>
      <form method="post">
        <label for="detective_name">Detective Name</label>
        <input type="text" id="detective_name" name="detective_name" required>
        <button type="submit">Start Investigation</button>
      </form>
    </div>
  </div>
</body>
</html>

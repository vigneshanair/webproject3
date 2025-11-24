<?php
require_once __DIR__ . '/game_logic.php';

$caseId = isset($_GET['case']) ? (int)$_GET['case'] : 1;
$from   = $_GET['from'] ?? 'case_dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = $_POST['notes'] ?? '';
    set_notebook($caseId, $notes);
}

$redirect = 'case_dashboard.php';
if     ($from === 'crime_scene')        $redirect = 'crime_scene.php';
elseif ($from === 'interrogations')     $redirect = 'interrogations.php';
elseif ($from === 'reconstruction')     $redirect = 'reconstruction.php';

header("Location: {$redirect}?case={$caseId}");
exit;

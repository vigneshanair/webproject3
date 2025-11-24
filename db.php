<?php
// db.php – database connection for codd

$host = 'localhost';
$db   = 'vajithnair1';    // <-- put your DB name here (likely same as your username)
$user = 'vajithnair1';    // <-- your MySQL username
$pass = 'vajithnair1';  // <-- EXACT password you just used in terminal

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

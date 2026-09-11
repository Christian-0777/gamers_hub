<?php

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = db();

    echo 'MySQL database connection successful.<br>';
    echo 'Database: ' . $pdo->query('SELECT DATABASE()')->fetchColumn();
} catch (PDOException $e) {
    echo 'Database connection failed:<br>';
    echo htmlspecialchars($e->getMessage());
}
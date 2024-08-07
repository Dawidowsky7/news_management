<?php
require_once 'inc/functions.php';

header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=news_management', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $pdo->query('SELECT DISTINCT title FROM news');
    $titles = $query->fetchAll(PDO::FETCH_COLUMN);

    $response = ['titles' => $titles];
    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

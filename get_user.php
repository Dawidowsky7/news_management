<?php
require_once 'inc/functions.php';

if (!checkAuth() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user = getUserById($id);
    if ($user) {
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'Użytkownik nie znaleziony']);
    }
} else {
    echo json_encode(['error' => 'Brak ID użytkownika']);
}
?>

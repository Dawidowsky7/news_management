<?php
require_once 'inc/functions.php';
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $news = getNewsById($id);

    if ($news) {
        $response = ['news' => $news];
    } else {
        $response = ['news' => null];
    }
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Brak ID']);
}
?>

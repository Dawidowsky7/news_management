<?php
require_once 'inc/functions.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $news = getNewsById($id);
    echo json_encode($news);
}
?>

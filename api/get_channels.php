<?php
    require_once __DIR__ . '/../pages/db.php';
    $res = $conn->query("SELECT * FROM chat_channels");
    $channels = [];
    while ($row = $res->fetch_assoc()) {
        $channels[] = $row;
    }
    echo json_encode($channels);
?>

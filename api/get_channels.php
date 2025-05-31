<?php
    require_once __DIR__ . '/../pages/db.php';
    header('Content-Type: application/xml; charset=utf-8');
    $res = $conn->query("SELECT * FROM chat_channels");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<channels>";
    while ($row = $res->fetch_assoc()) {
        echo "<channel>";
        echo "<channel_id>" . htmlspecialchars($row['channel_id']) . "</channel_id>";
        echo "<name>" . htmlspecialchars($row['name']) . "</name>";
        echo "</channel>";
    }
    echo "</channels>";
?>

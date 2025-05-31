<?php
    require_once __DIR__ . '/../pages/db.php';
    header('Content-Type: application/xml; charset=utf-8');

    if (!isset($_GET['channel_id'])) {
        http_response_code(400);
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?><error>Channel ID missing</error>";
        exit;
    }

    $channel_id = intval($_GET['channel_id']);
    $stmt = $conn->prepare("
        SELECT m.message_id, m.content, m.image_url, m.timestamp, u.username, u.pic_profile
        FROM chat_messages m
        JOIN users u ON m.user_id = u.user_id
        WHERE m.channel_id = ?
        ORDER BY m.timestamp ASC
    ");

    $stmt->bind_param("i", $channel_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<messages>";
    while ($row = $result->fetch_assoc()) {
        echo "<message>";
        echo "<message_id>" . htmlspecialchars($row['message_id']) . "</message_id>";
        echo "<username>" . htmlspecialchars($row['username']) . "</username>";
        echo "<content>" . htmlspecialchars($row['content']) . "</content>";
        echo "<image_url>" . htmlspecialchars($row['image_url']) . "</image_url>";
        echo "<timestamp>" . htmlspecialchars($row['timestamp']) . "</timestamp>";
        echo "<pic_profile>" . htmlspecialchars($row['pic_profile']) . "</pic_profile>";
        echo "</message>";
    }
    echo "</messages>";
?>

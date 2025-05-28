<?php
require_once _DIR_ . '/../pages/db.php';
header('Content-Type: application/json');

if (!isset($_GET['channel_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Channel ID missing']);
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

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
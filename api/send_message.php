<?php
    session_start();
    require_once __DIR__ . '/../pages/db.php';

    if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
        $_SESSION['user_id'] = $_COOKIE['user_id'];
    }

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo "Not logged in";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $channel_id = $_POST['channel_id'] ?? null;
    $content = $_POST['content'] ?? '';
    $image_url = null;

    if (!$channel_id) {
        http_response_code(400);
        echo "Channel ID missing";
        exit;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $filename = uniqid() . "_" . basename($_FILES['image']['name']);
        $target_path = "../uploads/" . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
            $image_url = "uploads/" . $filename;
        }
    }

    $stmt = $conn->prepare("INSERT INTO chat_messages (channel_id, user_id, content, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $channel_id, $user_id, $content, $image_url);
    if ($stmt->execute()) {
        echo "OK";
    } else {
        http_response_code(500);
        echo "DB Error: " . $stmt->error;
    }
?>
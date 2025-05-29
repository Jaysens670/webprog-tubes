<?php
// notifications.php
// Script to remind users of tasks with deadlines within the next 24 hours

session_start();
include 'db.php';

// Only run if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Query for tasks with deadlines within the next 24 hours
$sql = "SELECT task_id, title, deadline FROM tasks WHERE user_id = ? AND deadline > NOW() AND deadline <= DATE_ADD(NOW(), INTERVAL 1 DAY) ORDER BY deadline ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'task_id' => $row['task_id'],
        'title' => $row['title'],
        'deadline' => $row['deadline'],
        'message' => 'Tenggat tugas "' . $row['title'] . '" akan berakhir dalam 24 jam!'
    ];
}

header('Content-Type: application/json');
echo json_encode($notifications);

<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folder_name = $_POST['folder_name'];

    $stmt = $conn->prepare("INSERT INTO folders (user_id, folder_name, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $folder_name);
    $stmt->execute();

    echo "<p>Folder berhasil dibuat!</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buat Folder</title>
    <link rel="stylesheet" href="addfolder.css">
</head>
<body>
<div class="container">
    <h2>Buat Folder Baru</h2>
    <form method="post">
        Nama Folder: <input type="text" name="folder_name" required>
        <button type="submit">Buat</button>
    </form>
</div>
</body>
</html>

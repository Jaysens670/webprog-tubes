<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folder_name = $_POST['folder_name'];

    $stmt = $conn->prepare("INSERT INTO folders (user_id, folder_name, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $folder_name);
    
    if ($stmt->execute()) {
        $message = '<div class="message success">Folder berhasil dibuat!</div>';
    } else {
        $message = '<div class="message error">Gagal membuat folder. Silakan coba lagi.</div>';
    }
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
    <?php echo $message; ?>
    <form method="post">
        <label for="folder_name">Nama Folder:</label>
        <input type="text" id="folder_name" name="folder_name" required>
        <button type="submit">Buat</button>
    </form>
    <div class="navigation-buttons">
        <a href="home.php" class="back-button">← Kembali ke Main Menu</a>
    </div>
</div>
</body>
</html>
<?php
    session_start();
    require_once 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
?>

<!DOCTYPE html>
<html>

<head>
    <title>Global Chat</title>
    <link rel="stylesheet" href="../assets/chat.css">
</head>

<body>
    <h2>Global Chat</h2>

    <select id="channelSelect"></select>
    <div id="chatBox"></div>

    <form id="chatForm" enctype="multipart/form-data">
        <input type="text" id="messageInput" placeholder="Ketik pesan...">
        <label for="imageInput" style="margin-left:8px;">Input Gambar:</label>
        <input type="file" id="imageInput" style="display:none;">
        <button type="button" id="addImageBtn" style="margin-left:4px;">Add Gambar</button>
        <button type="submit">Kirim</button>
    </form>

    <div class="navigation-buttons">
        <a href="home.php" class="back-button">← Kembali ke Main Menu</a>
    </div>

    <script src="../assets/chat.js"></script>
    <script>
        // Add image button logic
        document.getElementById('addImageBtn').addEventListener('click', function(e) {
            document.getElementById('imageInput').click();
        });
    </script>
</body>

</html>

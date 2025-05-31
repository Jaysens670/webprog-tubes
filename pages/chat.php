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
    <link rel="stylesheet" href="chat.css">
</head>

<body>
    <h2>Global Chat</h2>

    <select id="channelSelect"></select>
    <div id="chatBox"></div>

    <form id="chatForm" enctype="multipart/form-data">
        <div class="image-upload-row">
            <span style="margin-right:10px; font-weight:bold;">Input Gambar :</span>
            <button type="button" id="addImageBtn" style="display:inline-block; background-color:#2980b9; color:#fff; border:none; border-radius:4px; padding:8px 16px; font-size:1em; font-weight:bold; cursor:pointer; transition:background 0.2s;">Add Picture</button>
            <input type="file" id="imageInput" style="display:none;">
            <img id="previewImage" src="#" alt="Preview" style="display:none; max-width:120px; max-height:120px; margin-left:12px; border-radius:8px; border:1px solid #ccc;" />
        </div>
        <div class="input-row">
            <input type="text" id="messageInput" placeholder="Ketik pesan...">
            <button type="submit">Kirim</button>
        </div>
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
        // Preview image logic
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = this.files[0];
            const preview = document.getElementById('previewImage');
            if (file) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    preview.src = ev.target.result;
                    preview.style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        });
        // Hilangkan preview gambar setelah submit
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            setTimeout(function() {
                const preview = document.getElementById('previewImage');
                preview.src = '#';
                preview.style.display = 'none';
            }, 100); // beri jeda agar proses upload tidak terganggu
        });
    </script>
</body>

</html>

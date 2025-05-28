<?php
    session_start();
    require_once 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Profile</title>
    <link rel="stylesheet" href="../assets/profile.css">
</head>

<body>
    <h2>Profil Anda</h2>
    <form action="update_profile.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
        Username: <strong><?= htmlspecialchars($user['username']) ?></strong><br>
        
        Nama Lengkap: <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>"><br>
        
        Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"><br>
        
        Password Baru:
        <input type="password" id="new_password" name="new_password" placeholder="Kosongkan jika tidak diubah">
        <input type="checkbox" id="showPassword"> Tampilkan Password<br><br>
        
        Foto Profil:
        <?php if ($user['pic_profile']): ?>
            <img src="uploads/<?= htmlspecialchars($user['pic_profile']) ?>" width="100"><br>
        <?php else: ?>
            <p style="color:gray;">(Belum ada foto profil)</p>
        <?php endif; ?>
        <input type="file" name="pic_profile"><br>

        <button type="submit">Simpan</button>
    </form>
    <div class="navigation-buttons">
        <a href="home.php" class="back-button">← Kembali ke Main Menu</a>
    </div>

    <script>
        document.getElementById("showPassword").addEventListener("change", function() {
            const passwordField = document.getElementById("new_password");
            passwordField.type = this.checked ? "text" : "password";
        });
    </script>

</body>

</html>
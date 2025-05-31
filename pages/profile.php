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
        <?php
        $profilePicPath = isset($user['pic_profile']) && $user['pic_profile'] ? '../uploads/' . htmlspecialchars($user['pic_profile']) : '../assets/default-profile.png';
        ?>
        <div style="margin-bottom:8px;">
            <img id="profilePreview" src="<?= $profilePicPath ?>" width="100" style="border-radius:50%; border:2px solid #ffd166; box-shadow:0 2px 8px rgba(44,62,80,0.10);" onerror="this.onerror=null;this.src='../assets/default-profile.png';"><br>
        </div>
        <label for="pic_profile" style="font-weight:500; margin-top:4px; display:inline-block;">Ubah Profile Picture</label>
        <input type="file" id="pic_profile" name="pic_profile"><br>

        <button type="submit" class="gradient-btn">Simpan</button>
    </form>
    <div class="navigation-buttons">
        <a href="home.php" class="back-button">← Kembali ke Main Menu</a>
    </div>

    <script>
        document.getElementById("showPassword").addEventListener("change", function() {
            const passwordField = document.getElementById("new_password");
            passwordField.type = this.checked ? "text" : "password";
        });
        // Preview profile picture before upload
        const picInput = document.getElementById('pic_profile');
        const previewImg = document.getElementById('profilePreview');
        picInput.addEventListener('change', function() {
            if (picInput.files && picInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                }
                reader.readAsDataURL(picInput.files[0]);
            } else {
                // If no file selected, revert to default or previous
                <?php if ($user['pic_profile']): ?>
                previewImg.src = '../uploads/<?= htmlspecialchars($user['pic_profile']) ?>';
                <?php else: ?>
                previewImg.src = '../assets/default-profile.png';
                <?php endif; ?>
                previewImg.style.display = 'block';
            }
        });
    </script>

</body>

</html>
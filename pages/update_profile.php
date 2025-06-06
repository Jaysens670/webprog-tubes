<?php
require_once 'db.php';
session_start();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="stylesheet" href="update_profile.css">
</head>

<body>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'];
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $new_password = $_POST['new_password'];

        $errors = [];
        $pic_profile = null;

        if (!$fullname || !$email) {
            $errors[] = "Nama dan Email wajib diisi.";
        }

        if (isset($_FILES['pic_profile']) && $_FILES['pic_profile']['error'] == 0) {
            $ext = pathinfo($_FILES['pic_profile']['name'], PATHINFO_EXTENSION);
            $pic_profile = uniqid() . "." . $ext;
            move_uploaded_file($_FILES['pic_profile']['tmp_name'], "uploads/" . $pic_profile);
        }

        $cek = $conn->prepare("SELECT user_id FROM users WHERE (email = ?) AND user_id != ? LIMIT 1");
        $cek->bind_param("si", $email, $user_id);
        $cek->execute();
        $cek_result = $cek->get_result();
        if ($cek_result->num_rows > 0) {
            echo "<script>alert('Email sudah digunakan oleh user lain');window.history.back();</script>";
            exit;
        }

        if (empty($errors)) {
            if ($new_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, password=?, pic_profile=IFNULL(?, pic_profile) WHERE user_id=?");
                $stmt->bind_param("ssssi", $fullname, $email, $hashed_password, $pic_profile, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, pic_profile=IFNULL(?, pic_profile) WHERE user_id=?");
                $stmt->bind_param("sssi", $fullname, $email, $pic_profile, $user_id);
            }

            if ($stmt->execute()) {
                echo "<div class='success-box'>
                    <h2>Profil berhasil diperbarui!</h2>
                    <a href='profile.php'>Kembali ke Profile</a>
                </div>";
            } else {
                echo "<div class='error-box'>
                    <h2>Gagal memperbarui profil.</h2>
                    <p>" . htmlspecialchars($stmt->error) . "</p>
                    <a href='profile.php'>Kembali</a>
                </div>";
            }
        } else {
            echo "<div class='error-box'>";
            foreach ($errors as $e) {
                echo "<p>Error: $e</p>";
            }
            echo "<a href='profile.php'>Kembali</a></div>";
        }
    }
    ?>

</body>

</html>
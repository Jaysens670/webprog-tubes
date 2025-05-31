<?php
    // register.php - Register Page
    session_start();
    if (isset($_SESSION['user_id']) || isset($_COOKIE['user_id'])) {
        header('Location: index.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>

<body>
    <header>
        <h1 class="Header">VisioTask</h1>
    </header>
    <h2>Register</h2>
    <form method="POST" action="register.php" enctype="multipart/form-data">
        <div style="display: flex; align-items: center; gap: 16px; justify-content: flex-start; margin-bottom: 8px;">
            <span style="font-size: 1rem; color: #2d3e50; font-weight: 500; min-width:120px; text-align:right;">Profile Picture :</span>
            <button id="addProfilePicture" type="button" onclick="document.getElementById('profileInput').click();">+</button>
        </div>
        <input type="file" id="profileInput" name="pic_profile" accept="image/*" style="display:none;">
        <input type="text" name="username" placeholder="Username" required>
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="submit">Register</button>
    </form>
    <p>Sudah punya akun? <a href="login.php">Login</a></p>
    <div id="registerMsg"></div>

    <?php
    if (isset($_POST['submit'])) {
        include 'db.php';

        // Inisialisasi variabel error
        $errors = [];

        // Validasi data
        if (empty($_POST['username'])) {
            $errors[] = "Username harus diisi";
        }

        if (empty($_POST['fullname'])) {
            $errors[] = "Nama lengkap harus diisi";
        }

        if (empty($_POST['email'])) {
            $errors[] = "Email harus diisi";
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Format email tidak valid";
        }

        if (empty($_POST['password'])) {
            $errors[] = "Password harus diisi";
        }

        // Handle upload foto
        $pic_profile = null;
        if (isset($_FILES['pic_profile']) && $_FILES['pic_profile']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    $errors[] = "Gagal membuat folder upload";
                }
            }

            $filename = basename($_FILES["pic_profile"]["name"]);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($ext, $allowed_ext)) {
                $errors[] = "Hanya file JPG, JPEG, PNG, atau GIF yang diperbolehkan";
            } else {
                $new_filename = uniqid() . "." . $ext;
                $target_file = $target_dir . $new_filename;

                if (!move_uploaded_file($_FILES["pic_profile"]["tmp_name"], $target_file)) {
                    $errors[] = "Gagal mengupload file";
                } else {
                    $pic_profile = $new_filename;
                }
            }
        }

        // Jika ada error, tampilkan dan hentikan proses
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p style='color:red;'>Error: $error</p>";
            }
            echo "<p><a href='register.php'>Kembali ke form registrasi</a></p>";
            exit;
        }
        // Proses registrasi jika tidak ada error
        try {
            $username = $_POST['username'];
            $fullname = $_POST['fullname'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Cek username/email sudah terdaftar
            $cek = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1");
            $cek->bind_param("ss", $username, $email);
            $cek->execute();
            $cek_result = $cek->get_result();
            if ($cek_result->num_rows > 0) {
                echo "<script>alert('Email or Username is already used.');window.history.back();</script>";
                exit;
            }

            $sql = "INSERT INTO users (username, fullname, email, password, pic_profile) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $fullname, $email, $password, $pic_profile);
            if ($stmt->execute()) {
                echo "<script>alert('Registrasi berhasil!');window.location.href='login.php';</script>";
                exit();
            } else {
                throw new Exception("Gagal registrasi: " . $stmt->error);
            }
        } catch (Exception $e) {
            echo "<p style='color:red;'>" . $e->getMessage() . "</p>";
            echo "<p><a href='register.php'>Kembali ke form registrasi</a></p>";
        }
    }
    ?>

    <script>
    // Preview profile picture in the circle
    const input = document.getElementById('profileInput');
    let previewImg = document.getElementById('profilePreview');
    if (!previewImg) {
        previewImg = document.createElement('img');
        previewImg.id = 'profilePreview';
        previewImg.alt = '';
        previewImg.style.width = '100%';
        previewImg.style.height = '100%';
        previewImg.style.objectFit = 'cover';
        previewImg.style.position = 'absolute';
        previewImg.style.left = '0';
        previewImg.style.top = '0';
        previewImg.style.borderRadius = '50%';
        previewImg.style.zIndex = '1';
        document.getElementById('addProfilePicture').style.position = 'relative';
        document.getElementById('addProfilePicture').appendChild(previewImg);
    }
    // Set default image as blank (transparent SVG) to avoid broken icon
    const blankSVG =
        'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96"></svg>';
    previewImg.src = blankSVG;
    input.addEventListener('change', function() {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            previewImg.src = blankSVG;
            previewImg.style.display = 'block';
        }
    });
    </script>
</body>

</html>
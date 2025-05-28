<?php
    // api/register.php - Handle AJAX registration
    require_once '../db.php';
    require_once '../cookie_helper.php';
    session_start();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        if (!$fullname || !$email || !$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
            exit();
        }
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Username/email sudah terdaftar']);
            $stmt->close();
            exit();
        }
        $stmt->close();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, fullname, password, email) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $username, $fullname, $hash, $email);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Registrasi berhasil! Silakan login.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal register']);
        }
        $stmt->close();
    } else {
        echo 'Invalid request';
    }
?>

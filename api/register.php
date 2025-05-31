<?php
    // api/register.php - Handle AJAX registration
    require_once '../db.php';
    require_once '../cookie_helper.php';
    session_start();
    header('Content-Type: application/xml; charset=utf-8');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        if (!$fullname || !$email || !$username || !$password) {
            echo '<response><status>error</status><message>Semua field wajib diisi</message></response>';
            exit();
        }
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            echo '<response><status>error</status><message>Username/email sudah terdaftar</message></response>';
            $stmt->close();
            exit();
        }
        $stmt->close();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, fullname, password, email) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $username, $fullname, $hash, $email);
        if ($stmt->execute()) {
            echo '<response><status>success</status><message>Registrasi berhasil! Silakan login.</message></response>';
        } else {
            echo '<response><status>error</status><message>Gagal register</message></response>';
        }
        $stmt->close();
    } else {
        echo '<response><status>error</status><message>Invalid request</message></response>';
    }
?>

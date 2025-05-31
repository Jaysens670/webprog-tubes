<?php
    // api/login.php - Handle AJAX login
    require_once '../db.php';
    require_once '../cookie_helper.php';
    session_start();
    header('Content-Type: application/xml; charset=utf-8');
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $stmt = $conn->prepare('SELECT user_id, password FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hash);
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                $_SESSION['user_id'] = $user_id;
                set_user_cookie($user_id);
                echo '<response><status>success</status><message>Login success</message></response>';
            } else {
                echo '<response><status>error</status><message>Password salah</message></response>';
            }
        } else {
            echo '<response><status>error</status><message>Username tidak ditemukan</message></response>';
        }
        $stmt->close();
    } else {
        echo '<response><status>error</status><message>Invalid request</message></response>';
    }
?>

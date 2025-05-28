<?php
    session_start();
    include 'db.php';

    if (isset($_SESSION['user_id']) || isset($_COOKIE['user_id'])) {
        header('Location: home.php');
        exit();
    }

    $login_error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['fullname'];
            header("Location: home.php");
            exit();
        } else {
            $login_error = "Invalid username or password";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>
    <h2>Login</h2>
    <?php if (!empty($login_error)): ?>
        <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
    <?php endif; ?>
    <form id="loginForm" method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Belum punya akun? <a href="register.php">Register</a></p>
    <div id="loginMsg"></div>
    <script src="assets/login.js"></script>
</body>

</html>
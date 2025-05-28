
<?php
    // index.php - Home/Dashboard
    require_once 'db.php';
    session_start();
    
    // Cek apakah session/cookie user_id ada, jika tidak set null
    $user_id = $_SESSION['user_id'] ?? ($_COOKIE['user_id'] ?? null);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>To-Do List Home</title>
    <link rel="stylesheet" href="home.css">
</head>

<body>
    <header>
        <h1 class="Header">VisioTask</h1>
        <nav>
            <?php if ($user_id): ?>
                <a href="profile.php">Profile</a> |
                <a href="chat.php">Global Chat</a> |
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a> |
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2>Your Task Folders</h2>
        <div id="folders"></div>
        <?php if ($user_id): ?>
            <button onclick="location.href='folder.php'">+ New Folder</button>
            <h2>Shared Tasks</h2>
            <div id="shared-tasks"></div>
        <?php else: ?>
            <p>Silakan login untuk mengakses fitur task dan shared tasks.</p>
        <?php endif; ?>
    </main>
    <script src="assets/main.js"></script>
</body>

</html>
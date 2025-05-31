<?php
    require_once 'db.php';
    session_start();
    
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
        <div id="folders">
        <?php if ($user_id): ?>
            <?php
            $stmt = $conn->prepare("SELECT * FROM folders WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $folders = $stmt->get_result();
            if ($folders->num_rows == 0) {
                echo "<p>(Belum ada folder)</p>";
            } else {
                echo '<div class="folder-list">';
                while ($folder = $folders->fetch_assoc()) {
                    echo '<div class="folder-item">';
                    echo '<strong>' . htmlspecialchars($folder['folder_name']) . '</strong><br>';
                    echo '<button class="lihat-tugas-btn" onclick="location.href=\'folder.php?id=' . $folder['folder_id'] . '\'">Lihat Tugas</button>';
                    echo '</div>';
                }
                echo '</div>';
            }
            ?>
        <?php else: ?>
            <p>Silakan login untuk mengakses fitur task dan shared tasks.</p>
        <?php endif; ?>
        </div>
        <?php if ($user_id): ?>
            <button onclick="location.href='addfolder.php'">+ New Folder</button>
            <button onclick="location.href='maketask.php'">+ New task</button>
            <h2>Shared Tasks</h2>
            <div class="shared-tasks-list" style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
            <?php
    $stmt = $conn->prepare("SELECT t.*, u.username AS pemilik 
                            FROM task_shares ts 
                            JOIN tasks t ON ts.task_id = t.task_id 
                            JOIN folders f ON t.folder_id = f.folder_id 
                            JOIN users u ON f.user_id = u.user_id 
                            WHERE ts.shared_to_user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<p>(Belum ada tugas kolaborasi)</p>";
    } else {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="folder-item" style="background: #f8fafc; border: 2px solid #ffd36b; border-radius: 15px; padding: 24px 32px; min-width: 260px; margin-bottom: 10px; box-shadow: 0 2px 8px #0001; display: flex; flex-direction: column; align-items: center;">';
            echo '<strong>' . htmlspecialchars($row['title']) . '</strong><br>';
            echo '<button class="lihat-tugas-btn" style="margin-top: 10px; background: #ffd36b; border: none; border-radius: 8px; padding: 8px 24px; font-weight: bold; cursor: pointer; box-shadow: 0 1px 2px #0001; transition: background 0.2s;" onclick="location.href=\'task.php?id=' . $row['task_id'] . '\'">Lihat Tugas</button>';
            echo '</div>';
        }
    }
    ?>
            </div>
        <?php endif; ?>
    </main>
    <script src="assets/main.js"></script>
</body>

</html>
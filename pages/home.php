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
        <div id="folders">
        <?php if ($user_id): ?>
            <?php
            // Ambil semua folder milik user
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
            <div id="shared-tasks"></div>
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
        echo "<div>";
        echo "<strong><a href='task.php?id={$row['task_id']}'>" . htmlspecialchars($row['title']) . "</a></strong><br>";
        echo "👤 Dari: " . htmlspecialchars($row['pemilik']) . "<br>";
        echo "⏳ Deadline: " . $row['deadline'] . "<br>";
        echo "🔥 Prioritas: " . $row['priority'] . "<br>";
        echo "📌 Status: " . $row['status'] . "<br><br>";
        echo "</div>";
    }
}
?>
        <?php endif; ?>
    </main>
    <script src="assets/main.js"></script>
</body>

</html>
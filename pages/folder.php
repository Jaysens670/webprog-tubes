<?php
// folder.php - Menampilkan daftar tugas dalam folder tertentu
require_once 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? ($_COOKIE['user_id'] ?? null);
$folder_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$user_id || !$folder_id) {
    echo '<p>Folder tidak ditemukan atau Anda belum login.</p>';
    exit;
}

// Ambil data folder
$stmt = $conn->prepare("SELECT * FROM folders WHERE folder_id = ? AND user_id = ?");
$stmt->bind_param("ii", $folder_id, $user_id);
$stmt->execute();
$folder = $stmt->get_result()->fetch_assoc();

if (!$folder) {
    echo '<p>Folder tidak ditemukan atau bukan milik Anda.</p>';
    exit;
}

// Ambil daftar tugas di folder ini, urutkan berdasarkan prioritas (TINGGI > SEDANG > RENDAH), lalu deadline terdekat ke atas
$stmt = $conn->prepare("SELECT * FROM tasks WHERE folder_id = ? ORDER BY FIELD(priority, 'Tinggi', 'Sedang', 'Rendah'), deadline ASC");
$stmt->bind_param("i", $folder_id);
$stmt->execute();
$tasks = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tugas di Folder: <?php echo htmlspecialchars($folder['folder_name']); ?></title>
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="folder.css">
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
        <h2>Daftar Tugas</h2>
        <?php if ($tasks->num_rows == 0): ?>
            <p>(Belum ada tugas di folder ini)</p>
        <?php else: ?>
            <ul>
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <li>
                    <strong><a href="task.php?id=<?php echo $task['task_id']; ?>"><?php echo htmlspecialchars($task['title']); ?></a></strong><br>
                    Deadline: <?php echo $task['deadline']; ?> |
                    Priotitas:
                    <?php
                        $priority = strtoupper($task['priority']);
                        if ($priority === 'TINGGI') {
                            echo '<span style="color:#e74c3c;font-weight:bold;">' . $priority . '</span>';
                        } elseif ($priority === 'SEDANG') {
                            echo '<span style="color:#ffd166;font-weight:bold;">' . $priority . '</span>';
                        } else {
                            echo '<span style="color:#3498db;font-weight:bold;">' . $priority . '</span>';
                        }
                    ?> |
                    Status:
                    <?php
                        $status = strtolower($task['status']);
                        if ($status === 'selesai') {
                            echo '<span style="color:#27ae60;font-weight:bold;">✅ SELESAI</span>';
                        } else {
                            echo '<span style="color:#e74c3c;font-weight:bold;">‼❌ BELUM</span>';
                        }
                    ?>
                </li>
            <?php endwhile; ?>
            </ul>
        <?php endif; ?>
        <button onclick="location.href='maketask.php?folder_id=<?php echo $folder_id; ?>'">+ Tambah Tugas</button>
        <div class="back-btn-container">
            <button class="back-button" onclick="location.href='home.php'">← Kembali ke Main Menu</button>
        </div>
    </main>
</body>
</html>

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

// Ambil daftar tugas di folder ini
$stmt = $conn->prepare("SELECT * FROM tasks WHERE folder_id = ?");
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
</head>
<body>
    <header>
        <h1 class="Header">Tugas di Folder: <?php echo htmlspecialchars($folder['folder_name']); ?></h1>
        <nav>
            <a href="home.php">Home</a> |
            <a href="logout.php">Logout</a>
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
                    Deadline: <?php echo $task['deadline']; ?> | Prioritas: <?php echo $task['priority']; ?> | Status: <?php echo $task['status']; ?>
                </li>
            <?php endwhile; ?>
            </ul>
        <?php endif; ?>
        <button onclick="location.href='maketask.php?folder_id=<?php echo $folder_id; ?>'">+ Tambah Tugas</button>
    </main>
</body>
</html>

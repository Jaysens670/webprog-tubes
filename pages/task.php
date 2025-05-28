<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'] ?? 0;

// Fetch task info with folder owner and creator username
$stmt = $conn->prepare("SELECT t.*, f.folder_name, f.user_id AS folder_owner, u.username AS folder_owner_name 
                        FROM tasks t
                        JOIN folders f ON t.folder_id = f.folder_id
                        JOIN users u ON f.user_id = u.user_id
                        WHERE t.task_id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
if (!$task) {
    die("Tugas tidak ditemukan.");
}

// Determine if current user is owner or collaborator
$is_owner = ($task['folder_owner'] == $user_id);
$check = $conn->prepare("SELECT 1 FROM task_shares WHERE task_id = ? AND shared_to_user_id = ?");
$check->bind_param("ii", $task_id, $user_id);
$check->execute();
$is_collaborator = ($check->get_result()->num_rows > 0);

// Mark task as done if posted by owner
if (isset($_POST['mark_done']) && $is_owner && $task['status'] != 'Selesai') {
    $conn->query("UPDATE tasks SET status = 'Selesai' WHERE task_id = $task_id");
    header("Location: task.php?id=$task_id");
    exit();
}

// Add new comment if posted by owner or collaborator
if (isset($_POST['comment']) && ($is_owner || $is_collaborator)) {
    $message = trim($_POST['message']);
    if ($message !== '') {
        $stmt = $conn->prepare("INSERT INTO task_comments (task_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $task_id, $user_id, $message);
        $stmt->execute();
        header("Location: task.php?id=$task_id");
        exit();
    }
}

// Add collaborator (owner only)
$invite_message = '';
if (isset($_POST['add_collab']) && $is_owner) {
    $collab_username = trim($_POST['collab_username']);
    if ($collab_username === '') {
        $invite_message = 'Username tidak boleh kosong.';
    } else {
        $user_check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $user_check->bind_param("s", $collab_username);
        $user_check->execute();
        $user_result = $user_check->get_result();
        if ($user_result->num_rows == 0) {
            $invite_message = "User '$collab_username' tidak ditemukan.";
        } else {
            $collab_user_id = $user_result->fetch_assoc()['user_id'];
            if ($collab_user_id == $user_id) {
                $invite_message = 'Anda adalah pemilik tugas, tidak perlu menambahkan diri Anda sebagai kolaborator.';
            } else {
                $exist_check = $conn->prepare("SELECT 1 FROM task_shares WHERE task_id = ? AND shared_to_user_id = ?");
                $exist_check->bind_param("ii", $task_id, $collab_user_id);
                $exist_check->execute();
                if ($exist_check->get_result()->num_rows > 0) {
                    $invite_message = "User '$collab_username' sudah menjadi kolaborator.";
                } else {
                    $insert = $conn->prepare("INSERT INTO task_shares (task_id, shared_to_user_id) VALUES (?, ?)");
                    $insert->bind_param("ii", $task_id, $collab_user_id);
                    if ($insert->execute()) {
                        $invite_message = "User '$collab_username' berhasil ditambahkan sebagai kolaborator.";
                    } else {
                        $invite_message = "Gagal menambahkan kolaborator: " . $insert->error;
                    }
                }
            }
        }
    }
}

// Get collaborators list
$collabs = $conn->prepare("SELECT u.username FROM task_shares ts JOIN users u ON ts.shared_to_user_id = u.user_id WHERE ts.task_id = ?");
$collabs->bind_param("i", $task_id);
$collabs->execute();
$collab_result = $collabs->get_result();

// Get comments ordered descending
$comments = $conn->prepare("SELECT c.message, c.created_at, u.username FROM task_comments c JOIN users u ON c.user_id = u.user_id WHERE c.task_id = ? ORDER BY c.created_at DESC");
$comments->bind_param("i", $task_id);
$comments->execute();
$comment_result = $comments->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Tugas - <?= htmlspecialchars($task['title']) ?></title>
    <style>
        body {font-family: Arial; background: #f4f6f9; padding: 30px;}
        .container {background: white; padding: 25px; border-radius: 10px; max-width: 700px; margin: auto;}
        h2 {margin-top: 0;}
        .done {color: green; font-weight: bold;}
        .late {color: red; font-weight: bold;}
        ul {padding-left: 20px;}
        ul li {margin-bottom: 4px; padding: 2px 0;}
        .comment-box {margin-top: 20px;}
        .comment {padding: 6px 8px; background: #f1f1f1; border-radius: 6px; margin-bottom: 6px; font-size: 14px; line-height: 1.3;}
        textarea {width: 100%; padding: 10px; font-family: Arial;}
        .button {padding: 6px 12px; background: #2a63c1; border: none; color: white; border-radius: 5px; cursor: pointer;}
        .button:hover {background: #1e4aa7;}
        p.message-success {color: green;}
        p.message-error {color: red;}
        form {margin-bottom: 20px;}
    </style>
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($task['title']) ?></h2>
    <p><strong>📁 Folder:</strong> <?= htmlspecialchars($task['folder_name']) ?></p>
    <p><strong>👤 Dibuat oleh:</strong> <?= htmlspecialchars($task['folder_owner_name']) ?></p>
    <p><strong>⏳ Deadline:</strong> <?= $task['deadline'] ?></p>
    <p><strong>🔥 Prioritas:</strong> <?= $task['priority'] ?></p>
    <p><strong>📌 Status:</strong>
    <?php
        if ($task['status'] === 'Selesai') {
            echo (new DateTime() > new DateTime($task['deadline'])) ? "<span class='late'>Terlambat diselesaikan ❌</span>" : "<span class='done'>Selesai ✅</span>";
        } else {
            echo htmlspecialchars($task['status']);
        }
    ?>
    </p>
    <p><strong>📝 Deskripsi:</strong><br><?= nl2br(htmlspecialchars($task['description'])) ?></p>

    <?php if ($is_owner && $task['status'] !== 'Selesai'): ?>
    <form method="post"><button class="button" name="mark_done">✔ Tandai Selesai</button></form>
    <?php endif; ?>

    <hr>
    <a href="home.php">balik</a>
    <h3>🤝 Kolaborator</h3>
    <ul>
        <?php while ($c = $collab_result->fetch_assoc()): ?>
            <li><?= htmlspecialchars($c['username']) ?></li>
        <?php endwhile; ?>
    </ul>

    <?php if ($is_owner): ?>
        <h4>+ Tambah Kolaborator</h4>
        <?php if ($invite_message !== ''): ?>
            <p class="<?= strpos($invite_message, 'berhasil') !== false ? 'message-success' : 'message-error' ?>"><?= htmlspecialchars($invite_message) ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="collab_username" placeholder="Masukkan username user" required>
            <button class="button" name="add_collab" type="submit">Tambahkan</button>
        </form>
    <?php endif; ?>

    <?php if ($is_owner || $is_collaborator): ?>
        <div class="comment-box">
            <h3>💬 Komentar</h3>
            <form method="post">
                <textarea name="message" rows="3" placeholder="Tulis komentar..." required></textarea><br>
                <button class="button" name="comment">Kirim Komentar</button>
            </form>

            <?php while ($row = $comment_result->fetch_assoc()): ?>
                <div class="comment">
                    <strong><?= htmlspecialchars($row['username']) ?>:</strong><br>
                    <?= nl2br(htmlspecialchars($row['message'])) ?><br>
                    <small><i><?= $row['created_at'] ?></i></small>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

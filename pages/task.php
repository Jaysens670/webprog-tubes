<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'] ?? 0;

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

$is_owner = ($task['folder_owner'] == $user_id);
$check = $conn->prepare("SELECT 1 FROM task_shares WHERE task_id = ? AND shared_to_user_id = ?");
$check->bind_param("ii", $task_id, $user_id);
$check->execute();
$is_collaborator = ($check->get_result()->num_rows > 0);

$stmt = $conn->prepare("SELECT COUNT(*) AS incomplete_count FROM subtasks WHERE task_id = ? AND is_done = 0");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result_subtasks = $stmt->get_result()->fetch_assoc();
$incomplete_subtask_count = $result_subtasks['incomplete_count'] ?? 0;
$stmt->close();

if (isset($_POST['mark_done'])) {
    if ($is_owner) {
        if ($task['status'] != 'Selesai') {
            if ($incomplete_subtask_count == 0) {
                $conn->query("UPDATE tasks SET status = 'Selesai' WHERE task_id = $task_id");
                header("Location: task.php?id=$task_id");
                exit();
            } else {
                $error_task_completion = "Tidak bisa menandai tugas selesai, karena masih ada subtask yang belum selesai.";
            }
        }
    } else {
        $error_task_completion = "Anda bukan pemilik tugas, tidak bisa menandai selesai.";
    }
}

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
            $invite_message = "User   '$collab_username' tidak ditemukan.";
        } else {
            $collab_user_id = $user_result->fetch_assoc()['user_id'];
            if ($collab_user_id == $user_id) {
                $invite_message = 'Anda adalah pemilik tugas, tidak perlu menambahkan diri Anda sebagai kolaborator.';
            } else {
                $exist_check = $conn->prepare("SELECT 1 FROM task_shares WHERE task_id = ? AND shared_to_user_id = ?");
                $exist_check->bind_param("ii", $task_id, $collab_user_id);
                $exist_check->execute();
                if ($exist_check->get_result()->num_rows > 0) {
                    $invite_message = "User   '$collab_username' sudah menjadi kolaborator.";
                } else {
                    $insert = $conn->prepare("INSERT INTO task_shares (task_id, shared_to_user_id) VALUES (?, ?)");
                    $insert->bind_param("ii", $task_id, $collab_user_id);
                    if ($insert->execute()) {
                        $invite_message = "User   '$collab_username' berhasil ditambahkan sebagai kolaborator.";
                    } else {
                        $invite_message = "Gagal menambahkan kolaborator: " . $insert->error;
                    }
                }
            }
        }
    }
}

$collabs = $conn->prepare("SELECT u.username FROM task_shares ts JOIN users u ON ts.shared_to_user_id = u.user_id WHERE ts.task_id = ?");
$collabs->bind_param("i", $task_id);
$collabs->execute();
$collab_result = $collabs->get_result();

$comments = $conn->prepare("SELECT c.message, c.created_at, u.username FROM task_comments c JOIN users u ON c.user_id = u.user_id WHERE c.task_id = ? ORDER BY c.created_at DESC");
$comments->bind_param("i", $task_id);
$comments->execute();
$comment_result = $comments->get_result();

$subtask_message = '';
if (isset($_POST['add_subtask'])) {
    $subtask_title = trim($_POST['subtask_title']);
    if ($subtask_title !== '') {
        $insert_subtask = $conn->prepare("INSERT INTO subtasks (task_id, title, is_done) VALUES (?, ?, 0)");
        $insert_subtask->bind_param("is", $task_id, $subtask_title);
        if ($insert_subtask->execute()) {
            $subtask_message = "Subtask berhasil ditambahkan!";
        } else {
            $subtask_message = "Gagal menambahkan subtask: " . $insert_subtask->error;
        }
        $insert_subtask->close();
    } else {
        $subtask_message = "Judul subtask tidak boleh kosong.";
    }
}

$subtasks = $conn->prepare("SELECT * FROM subtasks WHERE task_id = ? ORDER BY subtask_id DESC");
$subtasks->bind_param("i", $task_id);
$subtasks->execute();
$subtask_result = $subtasks->get_result();

if (isset($_POST['mark_subtask_done'])) {
    $subtask_id = (int)$_POST['subtask_id'];
    $stmt = $conn->prepare("UPDATE subtasks SET is_done = 1 WHERE subtask_id = ?");
    $stmt->bind_param("i", $subtask_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM subtasks WHERE task_id = ? AND is_done = 0");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $pending = $stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    if ($pending == 0) {
        $conn->query("UPDATE tasks SET status = 'Selesai' WHERE task_id = $task_id");
    }
    header("Location: task.php?id=$task_id");
    exit();
}

// Handle task notes
$note_message = '';
if (isset($_POST['add_note'])) {
    $note_text = trim($_POST['note_text']);
    if ($note_text !== '') {
        $insert_note = $conn->prepare("INSERT INTO task_notes (task_id, user_id, note_text) VALUES (?, ?, ?)");
        $insert_note->bind_param("iis", $task_id, $user_id, $note_text);
        if ($insert_note->execute()) {
            $note_message = "Catatan berhasil ditambahkan!";
        } else {
            $note_message = "Gagal menambahkan catatan: " . $insert_note->error;
        }
        $insert_note->close();
    } else {
        $note_message = "Catatan tidak boleh kosong.";
    }
}

// Fetch notes for this task
$notes = $conn->prepare("SELECT note_text, created_at FROM task_notes WHERE task_id = ? AND user_id = ?");
$notes->bind_param("ii", $task_id, $user_id);
$notes->execute();
$note_result = $notes->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - <?= htmlspecialchars($task['title']) ?></title>
    <link rel="stylesheet" href="task.css">
</head>
<body>
<div class="container">
    <h2><?= htmlspecialchars($task['title']) ?></h2>
    
    <div class="task-meta">
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
    </div>

    <?php if ($is_owner && $task['status'] !== 'Selesai'): ?>
    <form method="post">
        <button class="button" name="mark_done">✔ Tandai Selesai</button>
    </form>
    <?php endif; ?>

    <hr>

    <div class="navigation-buttons">
        <a href="home.php" class="back-button">← Kembali ke Main Menu</a>
    </div>

    <div class="collaborators-section">
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
    </div>

    <div class="subtasks-section">
        <h3>📝 Subtasks</h3>
        <ul>
            <?php while ($subtask = $subtask_result->fetch_assoc()): ?>
                <li>
                    <strong><?= htmlspecialchars($subtask['title']) ?></strong> - <?= $subtask['is_done'] ? '✔ Selesai' : '✘ Belum Selesai' ?>
                    <?php if (!$subtask['is_done']): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="subtask_id" value="<?= $subtask['subtask_id'] ?>">
                            <button type="submit" name="mark_subtask_done" class="button">Tandai Selesai</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>

        <h3>➕ Tambah Subtask</h3>
        <?php if ($subtask_message !== ''): ?>
            <p class="<?= strpos($subtask_message, 'berhasil') !== false ? 'message-success' : 'message-error' ?>"><?= htmlspecialchars($subtask_message) ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="subtask_title" placeholder="Judul Subtask" required>
            <button class="button" name="add_subtask" type="submit">Tambah Subtask</button>
        </form>
    </div>

    <div class="notes-section">
        <h3>📝 Catatan Pribadi</h3>
        <?php if ($note_message !== ''): ?>
            <p class="<?= strpos($note_message, 'berhasil') !== false ? 'message-success' : 'message-error' ?>"><?= htmlspecialchars($note_message) ?></p>
        <?php endif; ?>
        <form method="post">
            <textarea name="note_text" rows="3" placeholder="Tulis catatan..." required></textarea><br>
            <button class="button" name="add_note">Tambahkan Catatan</button>
        </form>
        <h4>Catatan Anda:</h4>
        <ul>
            <?php while ($note = $note_result->fetch_assoc()): ?>
                <li><?= nl2br(htmlspecialchars($note['note_text'])) ?> <small>(<?= $note['created_at'] ?>)</small></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <?php if ($is_owner || $is_collaborator): ?>
        <div class="comments-section">
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

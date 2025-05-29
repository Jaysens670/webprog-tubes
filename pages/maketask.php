<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch available folders for the current user only
$folders = [];
$sql = "SELECT folder_id, folder_name FROM folders WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $folders[] = $row;
    }
}

// If form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $folder_id = (int)$_POST['folder_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $deadline = $_POST['deadline'];
    $priority = $_POST['priority'];

    // Verify that the folder belongs to the user
    $valid_folder = false;
    foreach ($folders as $folder) {
        if ($folder['folder_id'] == $folder_id) {
            $valid_folder = true;
            break;
        }
    }

    if (!$valid_folder) {
        $error = "Invalid folder selected.";
    } elseif (empty($title) || empty($description) || empty($deadline) || empty($priority)) {
        $error = "Please fill in all fields.";
    } else {
        $status = "Belum";
        $created_at = date('Y-m-d H:i:s');

        $sql = "INSERT INTO tasks (folder_id, title, description, deadline, status, priority, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("issssss", $folder_id, $title, $description, $deadline, $status, $priority, $created_at);
            if ($stmt->execute()) {
                $success = "Task successfully added!";
            } else {
                $error = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database prepare error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Add Task - VisioTask</title>
<link rel="stylesheet" href="maketask.css">
<style>
    .message {
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
    }
    .error {
        background-color: #f8d7da;
        color: #721c24;
    }
    .success {
        background-color: #d4edda;
        color: #155724;
    }
</style>
</head>
<body>
    <a href="home.php">back</a>
<h2>Add New Task</h2>

<?php if (!empty($error)): ?>
    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="message success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" action="">
    <label for="folder_id">Select Folder:</label>
    <select name="folder_id" id="folder_id" required>
        <?php foreach ($folders as $folder): ?>
            <option value="<?php echo htmlspecialchars($folder['folder_id']); ?>">
                <?php echo htmlspecialchars($folder['folder_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="title">Task Title:</label>
    <input type="text" name="title" id="title" required maxlength="255" />

    <label for="description">Description:</label>
    <textarea name="description" id="description" required maxlength="2000"></textarea>

    <label for="deadline">Deadline:</label>
    <input type="date" name="deadline" id="deadline" required />

    <label for="priority">Priority:</label>
    <select name="priority" id="priority" required>
        <option value="Tinggi">Tinggi</option>
        <option value="Sedang">Sedang</option>
        <option value="Rendah">Rendah</option>
    </select>

    <input type="submit" value="Add Task" />
</form>

</body>
</html>
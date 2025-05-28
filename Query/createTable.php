<?php
    include 'config.php';

    // Tabel users
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        user_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        fullname VARCHAR(100),
        password VARCHAR(255),
        email VARCHAR(100) UNIQUE,
        pic_profile VARCHAR(255)
    )");

    // Tabel folders
    $conn->query("CREATE TABLE IF NOT EXISTS folders (
        folder_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        folder_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    )");

    // Tabel tasks
    $conn->query("CREATE TABLE IF NOT EXISTS tasks (
        task_id INT AUTO_INCREMENT PRIMARY KEY,
        folder_id INT,
        title VARCHAR(150),
        description TEXT,
        deadline DATE,
        status ENUM('Belum','Proses','Selesai') DEFAULT 'Belum',
        priority ENUM('Rendah','Sedang','Tinggi') DEFAULT 'Sedang',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (folder_id) REFERENCES folders(folder_id) ON DELETE CASCADE
    )");

    // Tabel subtasks
    $conn->query("CREATE TABLE IF NOT EXISTS subtasks (
        subtask_id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT,
        title VARCHAR(150),
        is_done BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE CASCADE
    )");

    // Tabel task_notes
    $conn->query("CREATE TABLE IF NOT EXISTS task_notes (
        note_id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT,
        note_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE CASCADE
    )");

    // Tabel task_shares
    $conn->query("CREATE TABLE IF NOT EXISTS task_shares (
        share_id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT,
        shared_to_user_id INT,
        FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE CASCADE,
        FOREIGN KEY (shared_to_user_id) REFERENCES users(user_id)
    )");

    // Tabel task_comments
    $conn->query("CREATE TABLE IF NOT EXISTS task_comments (
        comment_id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT,
        user_id INT,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )");

    // Tabel notifications
    $conn->query("CREATE TABLE IF NOT EXISTS notifications (
        notif_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        task_id INT,
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (task_id) REFERENCES tasks(task_id)
    )");

    // Tabel chat_channels
    $conn->query("CREATE TABLE IF NOT EXISTS chat_channels (
        channel_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100)
    )");

    // Tabel chat_messages
    $conn->query("CREATE TABLE IF NOT EXISTS chat_messages (
        message_id INT AUTO_INCREMENT PRIMARY KEY,
        channel_id INT,
        user_id INT,
        content TEXT,
        image_url VARCHAR(255),
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (channel_id) REFERENCES chat_channels(channel_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )");

    echo "Semua tabel berhasil dibuat.";
?>

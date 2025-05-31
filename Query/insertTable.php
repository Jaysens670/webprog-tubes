<?php
    include 'config.php';

    // Insert channel chat default
    $conn->query("INSERT INTO chat_channels (name) VALUES ('General')");
    $conn->query("INSERT INTO chat_channels (name) VALUES ('Matematika')");
    $conn->query("INSERT INTO chat_channels (name) VALUES ('Fisika')");
    $conn->query("INSERT INTO chat_channels (name) VALUES ('Kimia')");
    $conn->query("INSERT INTO chat_channels (name) VALUES ('Koding')");

    echo "Channel default berhasil ditambahkan.";
?>

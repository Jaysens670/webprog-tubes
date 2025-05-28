<?php
    $servername = "localhost";
    $username = "root";
    $password = "";

    // Membuat koneksi
    $conn = new mysqli($servername, $username, $password);

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Buat database
    $sql = "CREATE DATABASE IF NOT EXISTS VisioTask";
    if ($conn->query($sql) === TRUE) {
        echo "Database VisioTask berhasil dibuat.";
    } else {
        echo "Gagal membuat database: " . $conn->error;
    }

    $conn->close();
?>

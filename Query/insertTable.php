<?php
include 'config.php';

// Buat password yang sudah di-hash
$passAni = password_hash('passani', PASSWORD_DEFAULT);
$passBudi = password_hash('passbudi', PASSWORD_DEFAULT);
$passCitra = password_hash('passcitra', PASSWORD_DEFAULT);

// Users
$conn->query("INSERT INTO users (username, fullname, password, email, pic_profile) VALUES
('ani123', 'Ani Wijaya', '$passAni', 'ani@gmail.com', 'ani.jpg'),
('budi321', 'Budi Santoso', '$passBudi', 'budi@gmail.com', 'budi.jpg'),
('citra456', 'Citra Lestari', '$passCitra', 'citra@gmail.com', 'citra.jpg')");


// Folders (folder tugas milik Ani)
$conn->query("INSERT INTO folders (user_id, folder_name) VALUES
(1, 'Tugas Matematika'),
(1, 'Tugas IPA'),
(2, 'PR Bahasa Indonesia')");

// Tasks dalam folder "Tugas Matematika"
$conn->query("INSERT INTO tasks (folder_id, title, description, deadline, status, priority) VALUES
(1, 'Kerjakan soal halaman 25', 'Soal tentang pecahan dan desimal', '2025-06-05', 'Belum', 'Sedang'),
(1, 'Buat rangkuman materi bab 3', 'Tentang persamaan linear', '2025-06-03', 'Proses', 'Tinggi')");

// Tasks dalam folder "Tugas IPA"
$conn->query("INSERT INTO tasks (folder_id, title, description, deadline, status, priority) VALUES
(2, 'Laporan Praktikum Sains', 'Membuat laporan pengamatan tumbuhan', '2025-06-07', 'Belum', 'Tinggi')");

// Tasks dalam folder Budi
$conn->query("INSERT INTO tasks (folder_id, title, description, deadline, status, priority) VALUES
(3, 'Analisis Puisi Chairil Anwar', 'Tugas menganalisis puisi ', '2025-06-04', 'Selesai', 'Sedang')");

// Subtasks untuk tugas Ani
$conn->query("INSERT INTO subtasks (task_id, title, is_done) VALUES
(1, 'Kerjakan no. 1-5', TRUE),
(1, 'Kerjakan no. 6-10', FALSE),
(2, 'Baca ulang materi', TRUE),
(2, 'Tulis rangkuman di buku', FALSE),
(3, 'Ambil data pengamatan', TRUE),
(3, 'Tulis kesimpulan', FALSE)");

// Notes untuk tugas
$conn->query("INSERT INTO task_notes (task_id, user_id, note_text) VALUES
(1, 1, 'Gunakan pecahan campuran di nomor 3.'),
(3, 1, 'Laporan harus pakai format PDF.'),
(4, 2, 'Tambahkan makna simbolik puisi.')");

// Membagikan tugas ke user lain
$conn->query("INSERT INTO task_shares (task_id, shared_to_user_id) VALUES
(1, 2), -- Ani share ke Budi
(3, 3), -- Ani share tugas IPA ke Citra
(4, 1)  -- Budi share ke Ani
");

// Komentar pada tugas
$conn->query("INSERT INTO task_comments (task_id, user_id, message) VALUES
(1, 2, 'Nomor 5 bingung. Bisa bantu?'),
(3, 3, 'Kita pakai format DOC atau PDF?'),
(4, 1, 'Tugasnya menarik banget!')");

// Notifikasi dummy
$conn->query("INSERT INTO notifications (user_id, task_id, message) VALUES
(1, 4, 'Tugas baru dibagikan oleh Budi'),
(2, 1, 'Tugas dibagikan oleh Ani'),
(3, 3, 'Tugas IPA dibagikan ke kamu')");

// Chat channels
$conn->query("INSERT INTO chat_channels (name) VALUES
('Kelas 8A'),
('Tugas IPA'),
('Diskusi Umum')");

// Chat messages
$conn->query("INSERT INTO chat_messages (channel_id, user_id, content, image_url) VALUES
(1, 1, 'Guys, ada PR matematika lho!', NULL),
(2, 3, 'Aku udah ambil data pengamatan.', NULL),
(3, 2, 'Ada yang paham soal nomor 6?', NULL)");


// Insert channel chat default
$conn->query("INSERT INTO chat_channels (name) VALUES ('General')");
$conn->query("INSERT INTO chat_channels (name) VALUES ('Matematika')");
$conn->query("INSERT INTO chat_channels (name) VALUES ('Fisika')");
$conn->query("INSERT INTO chat_channels (name) VALUES ('Kimia')");
$conn->query("INSERT INTO chat_channels (name) VALUES ('Koding')");

echo "data dummy sudah dibuat.";
?>
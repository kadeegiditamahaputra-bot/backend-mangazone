<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require "../config/db.php";

$id = intval($_GET['id']);

$stmt = mysqli_prepare($conn, "DELETE FROM manga WHERE malId = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

// Bagian eksekusi database dan redirect di atas sengaja tetap berjalan normal.
// Namun, agar desain di bawah sempat terlihat oleh mata user sebelum berpindah halaman,
// kita gunakan script redirect setelah jeda animasi selesai (sekitar 1.2 detik).
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MangaZone - Deleting...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #fafafa;
            color: #171717;
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }
        .delete-card {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: fadeIn 0.4s ease-out;
        }
        /* Custom Spinner Minimalis warna hitam pekat sesuai tema utama */
        .spinner-custom {
            width: 48px;
            height: 48px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #171717;
            border-radius: 50%;
            display: inline-block;
            animation: spin 0.8s linear infinite;
            margin-bottom: 1.5rem;
        }
        .status-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }
        .status-desc {
            font-size: 13px;
            color: #737373;
            margin-bottom: 0;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

    <div class="delete-card">
        <div class="spinner-custom"></div>
        <div class="status-title">Menghapus Manga #<?= $id ?></div>
        <div class="status-desc">Memproses sinkronisasi database, mohon tunggu sebentar...</div>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = "dashboard.php";
        }, 1200); // Memberikan jeda 1.2 detik agar animasi loading smooth sebelum redirect
    </script>

</body>
</html>
<?php 
exit; 
?>
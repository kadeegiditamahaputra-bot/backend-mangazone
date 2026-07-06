<?php
session_start();

// Proteksi halaman admin agar tidak bisa diakses tanpa login
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require "../config/db.php";

// Menggunakan MySQLi sesuai dengan file koneksi $conn kamu
$query = "SELECT * FROM manga ORDER BY mal_id DESC";
$result = mysqli_query($conn, $query);

// Ambil semua data ke dalam array
$mangas = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $mangas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Manga - MangaZone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #fafafa;
            color: #171717;
            font-family: 'Inter', sans-serif;
        }
        .table-container {
            background: #ffffff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }
        .btn-mz-dark {
            background-color: #171717;
            color: #ffffff;
            border: 1px solid #171717;
        }
        .btn-mz-dark:hover {
            background-color: #404040;
            color: #ffffff;
        }
    </style>
</head>
<body class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold h3 mb-1">Daftar Manga Repository</h1>
            <p class="text-muted small mb-0">Kelola indeks data komik yang terdaftar pada sistem.</p>
        </div>
        <a href="manga_add.php" class="btn btn-mz-dark">Tambah Manga</a>
    </div>

    <div class="table-container p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%">MAL ID</th>
                        <th style="width: 50%">Judul Manga</th>
                        <th style="width: 15%">Score</th>
                        <th style="width: 20%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mangas)): ?>
                        <?php foreach ($mangas as $manga): ?>
                            <tr>
                                <td class="fw-semibold text-secondary">#<?= $manga['mal_id'] ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($manga['title']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        ⭐ <?= number_format($manga['score'], 1) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <a href="manga_edit.php?id=<?= $manga['mal_id'] ?>" class="btn btn-outline-warning btn-sm px-3">Edit</a>
                                        <a href="manga_delete.php?id=<?= $manga['mal_id'] ?>" class="btn btn-outline-danger btn-sm px-3" onclick="return confirm('Apakah Anda yakin ingin menghapus manga ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                Tidak ada data manga di dalam database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
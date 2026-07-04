<?php
require "../config/db.php";
$stmt = $db->query("SELECT * FROM manga");
$mangas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Manga</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
  <h1>Daftar Manga</h1>
  <a href="manga_add.php" class="btn btn-primary mb-3">Tambah Manga</a>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>ID</th><th>Judul</th><th>Score</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($mangas as $manga): ?>
        <tr>
          <td><?= $manga['malId'] ?></td>
          <td><?= $manga['title'] ?></td>
          <td><?= $manga['score'] ?></td>
          <td>
            <a href="manga_edit.php?id=<?= $manga['malId'] ?>" class="btn btn-warning btn-sm">Edit</a>
            <a href="manga_delete.php?id=<?= $manga['malId'] ?>" class="btn btn-danger btn-sm">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>

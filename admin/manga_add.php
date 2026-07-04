<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require "../config/db.php";

$success_message = "";
$error_message = "";

// Ambil data seluruh manga dari API/Database untuk sidebar registry
$json = file_get_contents("http://localhost/mangazone-backend/api/manga.php");
$mangas = json_decode($json, true);
if (isset($mangas['data'])) {
    $mangas = $mangas['data'];
} else {
    $mangas = [];
}

// Urutkan berdasarkan malId ascending
usort($mangas, function($a, $b) {
    $idA = $a['malId'] ?? ($a['mal_id'] ?? 0);
    $idB = $b['malId'] ?? ($b['mal_id'] ?? 0);
    return $idA <=> $idB;
});

// PROSES INSERT DATA SELESAI SUBMIT FORM
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $malId = intval($_POST['malId']);
    $title = trim($_POST['title']);
    $imageUrl = trim($_POST['imageUrl']);
    $score = floatval($_POST['score']);
    $chapters = intval($_POST['chapters']);
    $synopsis = trim($_POST['synopsis']);

    // Mengolah genre yang dipisahkan koma menjadi array JSON
    $genres = [];
    if (!empty($_POST['genres'])) {
        $genres = array_map('trim', explode(',', $_POST['genres']));
    }
    $genresJson = json_encode($genres);

    if ($title == "") {
        $error_message = "Manga title cannot be empty.";
    } else {
        // Cek duplikasi MAL ID di database
        $cek = mysqli_query($conn, "SELECT malId FROM manga WHERE malId='$malId'");

        if (mysqli_num_rows($cek) > 0) {
            $error_message = "MAL ID Reference already exists in repository registry.";
        } else {
            // Prepared statement untuk keamanan database
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO manga (malId, title, imageUrl, score, chapters, synopsis, genres) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "issdiss",
                $malId,
                $title,
                $imageUrl,
                $score,
                $chapters,
                $synopsis,
                $genresJson
            );

            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Repository '" . htmlspecialchars($title) . "' has been created successfully.";

                // Refresh data sidebar index setelah data berhasil masuk database
                $json = file_get_contents("http://localhost/mangazone-backend/api/manga.php");
                $mangas = json_decode($json, true);
                if (isset($mangas['data'])) { 
                    $mangas = $mangas['data']; 
                }
                usort($mangas, function($a, $b) {
                    return ($a['malId'] ?? ($a['mal_id'] ?? 0)) <=> ($b['malId'] ?? ($b['mal_id'] ?? 0));
                });
            } else {
                $error_message = "Database runtime error: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create a new manga repository</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:-apple-system,BlinkMacSystemFont,Segoe+UI,Helvetica,Arial,sans-serif&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

<style>
/* GitHub Dark Theme Core Styling */
body {
    background-color: #0d1117;
    color: #c9d1d9;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
    font-size: 14px;
}
.navbar-github {
    background-color: #161b22;
    border-bottom: 1px solid #30363d;
    padding: 0.75rem 0;
}
.brand-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
}
.brand-logo-round {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #30363d;
}
.brand-text {
    font-size: 0.95rem;
    font-weight: 600;
    color: #f0f6fc;
}
.brand-accent {
    color: #8b949e;
    font-weight: 400;
    margin-left: 4px;
}
.gh-breadcrumb {
    font-size: 14px;
    color: #8b949e;
}
.gh-breadcrumb a {
    color: #58a6ff;
    text-decoration: none;
}
.gh-breadcrumb a:hover {
    text-decoration: underline;
}
.gh-panel-sidebar {
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 6px;
}
.gh-panel-header {
    background-color: #1f242c;
    border-bottom: 1px solid #30363d;
    padding: 12px 16px;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
}
.gh-input-search {
    background-color: #0d1117;
    border: 1px solid #30363d;
    color: #c9d1d9;
    font-size: 13px;
    padding: 4px 10px;
    border-radius: 6px;
}
.gh-input-search:focus {
    background-color: #0d1117;
    border-color: #1f6feb;
    box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.3);
    outline: none;
}
.gh-scroll-container {
    max-height: 520px;
    overflow-y: auto;
}
.gh-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid #21262d;
    transition: background-color 0.15s ease;
}
.gh-list-item:last-child {
    border-bottom: none;
}
.gh-list-item:hover {
    background-color: #1f242c;
}
.gh-item-title {
    font-weight: 500;
    color: #e6edf3;
    font-size: 13px;
    max-width: 160px;
}
.gh-badge-id {
    font-family: 'Fira Code', monospace;
    font-size: 11px;
    color: #8b949e;
    background-color: #21262d;
    padding: 2px 6px;
    border-radius: 4px;
    border: 1px solid #30363d;
}
.gh-main-heading {
    font-size: 24px;
    font-weight: 400;
    color: #e6edf3;
    border-bottom: 1px solid #21262d;
    padding-bottom: 8px;
}
.gh-form-group {
    margin-bottom: 16px;
}
.gh-label {
    font-size: 14px;
    font-weight: 600;
    color: #e6edf3;
    margin-bottom: 6px;
    display: block;
}
.gh-label-desc {
    font-size: 12px;
    color: #8b949e;
    margin-top: -4px;
    margin-bottom: 6px;
}
.gh-input-text {
    background-color: #0d1117;
    border: 1px solid #30363d;
    color: #e6edf3 !important;
    font-size: 14px;
    padding: 5px 12px;
    border-radius: 6px;
    width: 100%;
}
.gh-input-text:focus {
    background-color: #0d1117;
    border-color: #1f6feb;
    box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.3);
    outline: none;
}
.gh-textarea-md {
    font-family: 'Fira Code', monospace;
    font-size: 13px;
    min-height: 120px;
    resize: vertical;
}
.btn-gh-primary {
    background-color: #238636;
    color: #ffffff !important;
    border: 1px solid rgba(240, 246, 252, 0.1);
    font-size: 14px;
    font-weight: 500;
    padding: 5px 16px;
    border-radius: 6px;
}
.btn-gh-primary:hover {
    background-color: #2ea043;
}
.btn-gh-cancel {
    background-color: #21262d;
    border: 1px solid #30363d;
    color: #c9d1d9 !important;
    font-size: 14px;
    font-weight: 500;
    padding: 5px 16px;
    border-radius: 6px;
    text-decoration: none;
}
.btn-gh-cancel:hover {
    background-color: #30363d;
    border-color: #8b949e;
}
.gh-banner {
    border-radius: 6px;
    padding: 12px 16px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.gh-banner-success {
    background-color: rgba(56, 139, 253, 0.1);
    border: 1px solid rgba(56, 139, 253, 0.4);
    color: #58a6ff;
}
.gh-banner-danger {
    background-color: rgba(248, 81, 73, 0.1);
    border: 1px solid rgba(248, 81, 73, 0.4);
    color: #ff7b72;
}
.gh-scroll-container::-webkit-scrollbar { width: 6px; }
.gh-scroll-container::-webkit-scrollbar-track { background: #161b22; }
.gh-scroll-container::-webkit-scrollbar-thumb { background: #30363d; border-radius: 3px; }
</style>
</head>

<body>

<nav class="navbar navbar-github sticky-top">
  <div class="container-xl">
    <a class="brand-wrapper" href="dashboard.php">
      <img src="../assets/mangazone.png" alt="M" class="brand-logo-round" onerror="this.style.visibility='hidden'; this.style.width='0'">
      <span class="brand-text">MangaZone<span class="brand-accent">/ repository</span></span>
    </a>
  </div>
</nav>

<div class="container-xl py-4">
    <div class="gh-breadcrumb mb-4">
        <a href="dashboard.php">Repositories</a> / <span class="text-white">new</span>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4 order-2 order-lg-1">
            <div class="gh-panel-sidebar">
                <div class="gh-panel-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold text-white text-truncate">Database Index Registry</span>
                    <span class="badge rounded-pill" style="background-color: #30363d; color: #8b949e; font-size: 11px;"><?= count($mangas) ?> items</span>
                </div>
                
                <div class="p-3 border-bottom " style="border-color: #21262d !important;">
                    <input type="text" id="idSearchInput" class="form-control gh-input-search w-100" placeholder="Filter tracked IDs or titles...">
                </div>
                
                <div class="gh-scroll-container" id="idListGroup">
                    <?php if(!empty($mangas)): ?>
                        <?php foreach($mangas as $m): 
                            $mId = $m['malId'] ?? ($m['mal_id'] ?? 0);
                            $mTitle = $m['title'] ?? 'Untitled';
                        ?>
                            <div class="gh-list-item" data-search-text="<?= htmlspecialchars(strtolower($mId . ' ' . $mTitle)) ?>">
                                <span class="gh-item-title text-truncate" title="<?= htmlspecialchars($mTitle) ?>">
                                    <?= htmlspecialchars($mTitle) ?>
                                </span>
                                <span class="gh-badge-id">id:<?= htmlspecialchars($mId) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted text-center py-4" style="font-size: 13px;">No indexes discovered.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 order-1 order-lg-2">
            <h2 class="gh-main-heading mb-4">Create a new manga repository</h2>

            <?php if (!empty($success_message)): ?>
                <div class="gh-banner gh-banner-success mb-4">
                    <svg aria-hidden="true" height="16" viewBox="0 0 16 16" width="16" fill="currentColor"><path d="M0 8a8 8 0 1116 0A8 8 0 010 8zm11.58-2.22a.75.75 0 00-1.06-1.06L7 8.19 5.48 6.67a.75.75 0 00-1.06 1.06l2.05 2.05a.75.75 0 001.06 0l4.05-4.05z"></path></svg>
                    <div><?= $success_message ?></div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="gh-banner gh-banner-danger mb-4">
                    <svg aria-hidden="true" height="16" viewBox="0 0 16 16" width="16" fill="currentColor"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.03 11.3c.66 1.235-.236 2.653-1.544 2.653H1.971C.663 15 1.711-2.43 2.15 2.653l6.03-11.3zM1.97 13.5h12.058L8 2.114 1.971 13.5zM8 11.25a.75.75 0 110-1.5.75.75 0 010 1.5zm0-6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 018 5.25z"></path></svg>
                    <div><?= $error_message ?></div>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="gh-form-group">
                    <label Linda for="title" class="gh-label">Manga Title</label>
                    <input type="text" name="title" id="title" class="form-control gh-input-text" placeholder="e.g., Chainsaw Man, Berserk" required>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4 gh-form-group">
                        <label for="malId" class="gh-label">MAL ID Reference</label>
                        <div class="gh-label-desc">Unique identifier index</div>
                        <input type="number" name="malId" id="malId" class="form-control gh-input-text" placeholder="e.g., 44485" required>
                    </div>

                    <div class="col-12 col-md-4 gh-form-group">
                        <label for="score" class="gh-label">Rating Score</label>
                        <div class="gh-label-desc">Global target evaluation</div>
                        <input type="text" name="score" id="score" class="form-control gh-input-text" placeholder="0.0" value="0.0">
                    </div>

                    <div class="col-12 col-md-4 gh-form-group">
                        <label for="chapters" class="gh-label">Total Chapters</label>
                        <div class="gh-label-desc">Current build cycle</div>
                        <input type="number" name="chapters" id="chapters" class="form-control gh-input-text" placeholder="0" value="0">
                    </div>
                </div>

                <div class="gh-form-group">
                    <label for="imageUrl" class="gh-label">Image Artifact URL</label>
                    <input type="url" name="imageUrl" id="imageUrl" class="form-control gh-input-text" placeholder="https://domain.com/assets/cover.jpg">
                </div>

                <div class="gh-form-group">
                    <label for="synopsis" class="gh-label">Synopsis Description <span style="font-weight: 400; color: #8b949e;">(Optional)</span></label>
                    <div class="gh-label-desc">Write a comprehensive summary overview about this work repository.</div>
                    <textarea name="synopsis" id="synopsis" class="form-control gh-input-text gh-textarea-md" placeholder="Provide documentation summary info inside this manga dataset..."></textarea>
                </div>

                <div class="gh-form-group">
                    <label for="genres" class="gh-label">Genres</label>
                    <div class="gh-label-desc">Pisahkan dengan tanda koma (e.g., Action, Adventure, Fantasy).</div>
                    <input type="text" name="genres" id="genres" class="form-control gh-input-text" placeholder="Action, Adventure, Comedy">
                </div>

                <div class="pt-3 d-flex gap-2 justify-content-end border-top" style="border-color: #21262d !important;">
                    <a href="dashboard.php" class="btn btn-gh-cancel">Cancel</a>
                    <button type="submit" class="btn btn-gh-primary">Create repository</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const idSearchInput = document.getElementById('idSearchInput');
    const idRows = document.querySelectorAll('.gh-list-item');

    idSearchInput.addEventListener('input', function() {
        const query = idSearchInput.value.toLowerCase().trim();

        idRows.forEach(row => {
            const searchText = row.getAttribute('data-search-text');
            if(searchText.includes(query)) {
                row.style.setProperty('display', 'flex', 'important');
            } else {
                row.style.setProperty('display', 'none', 'important');
            }
        });
    });
});
</script>

</body>
</html>
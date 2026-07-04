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

// Urutkan berdasarkan malId DESCENDING (ID tertinggi paling atas)
usort($mangas, function($a, $b) {
    $idA = $a['malId'] ?? ($a['mal_id'] ?? 0);
    $idB = $b['malId'] ?? ($b['mal_id'] ?? 0);
    return $idB <=> $idA; // Diubah agar ID besar di atas
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
                // Urutkan kembali berdasarkan malId DESCENDING setelah refresh
                usort($mangas, function($a, $b) {
                    $idA = $a['malId'] ?? ($a['mal_id'] ?? 0);
                    $idB = $b['malId'] ?? ($b['mal_id'] ?? 0);
                    return $idB <=> $idA;
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* Modern Minimalist Light Theme */
body {
    background-color: #fafafa;
    color: #171717;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 14px;
    letter-spacing: -0.01em;
}

/* NAVBAR */
.navbar-custom {
    background-color: #ffffff;
    border-bottom: 1px solid #e5e5e5;
    padding: 1rem 0;
}
.brand-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}
.brand-logo-round {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 1px solid #e5e5e5;
    object-fit: cover;
}
.brand-text {
    font-size: 1.05rem;
    font-weight: 700;
    color: #171717;
    letter-spacing: -0.02em;
}
.brand-accent {
    color: #737373;
    font-weight: 400;
    margin-left: 4px;
}
.btn-custom-secondary {
    font-size: 13px;
    font-weight: 500;
    padding: 6px 16px;
    border-radius: 8px;
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    color: #404040;
    text-decoration: none;
    transition: all 0.2s ease;
}
.btn-custom-secondary:hover {
    background-color: #f5f5f5;
    color: #171717;
    border-color: #d4d4d4;
}

/* BREADCRUMB */
.mz-breadcrumb {
    font-size: 13px;
    color: #737373;
    font-weight: 500;
}
.mz-breadcrumb a {
    color: #171717;
    text-decoration: none;
}
.mz-breadcrumb a:hover {
    text-decoration: underline;
}

/* SIDEBAR & CONTAINERS */
.mz-box {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    overflow: hidden;
}
.mz-box-header {
    background-color: #ffffff;
    border-bottom: 1px solid #e5e5e5;
    padding: 16px 20px;
    font-weight: 600;
    font-size: 15px;
    color: #171717;
}
.mz-input-search {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    color: #171717;
    font-size: 13px;
    padding: 8px 14px;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.mz-input-search:focus {
    background-color: #ffffff;
    border-color: #171717;
    box-shadow: 0 0 0 4px rgba(23, 23, 23, 0.08);
    outline: none;
}
.mz-scroll-container {
    max-height: 520px;
    overflow-y: auto;
}
.mz-list-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-bottom: 1px solid #e5e5e5;
    transition: background-color 0.15s ease;
}
.mz-list-item:last-child {
    border-bottom: none;
}
.mz-list-item:hover {
    background-color: #fafafa;
}
.mz-item-title {
    font-weight: 500;
    color: #404040;
    font-size: 13px;
    max-width: 180px;
}
.mz-badge-id {
    font-size: 11px;
    font-weight: 600;
    color: #666666;
    background-color: #f5f5f5;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e5e5e5;
}

/* HEADINGS & GROUP FORMS */
.mz-main-heading {
    font-size: 22px;
    font-weight: 700;
    color: #171717;
    letter-spacing: -0.02em;
}
.mz-form-group {
    margin-bottom: 20px;
}
.mz-label {
    font-size: 13px;
    font-weight: 600;
    color: #404040;
    margin-bottom: 6px;
    display: block;
}
.mz-label-desc {
    font-size: 12px;
    color: #a3a3a3;
    margin-top: -2px;
    margin-bottom: 8px;
}
.mz-input-text {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    color: #171717 !important;
    font-size: 14px;
    padding: 8px 14px;
    border-radius: 8px;
    width: 100%;
    transition: all 0.2s ease;
}
.mz-input-text:focus {
    background-color: #ffffff;
    border-color: #171717;
    box-shadow: 0 0 0 4px rgba(23, 23, 23, 0.08);
    outline: none;
}
.mz-textarea-md {
    min-height: 120px;
    resize: vertical;
}

/* BUTTONS */
.btn-mz-primary {
    background-color: #171717;
    color: #ffffff !important;
    border: 1px solid #171717;
    font-size: 14px;
    font-weight: 500;
    padding: 8px 18px;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.btn-mz-primary:hover {
    background-color: #404040;
    border-color: #404040;
    transform: translateY(-1px);
}
.btn-mz-cancel {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    color: #404040 !important;
    font-size: 14px;
    font-weight: 500;
    padding: 8px 18px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
}
.btn-mz-cancel:hover {
    background-color: #f5f5f5;
    border-color: #d4d4d4;
    color: #171717 !important;
}

/* BANNER NOTIFICATION */
.mz-banner {
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}
.mz-banner-success {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #16a34a;
}
.mz-banner-danger {
    background-color: #fff5f5;
    border: 1px solid #fca5a5;
    color: #ef4444;
}

/* Custom Scrollbar */
.mz-scroll-container::-webkit-scrollbar { width: 5px; }
.mz-scroll-container::-webkit-scrollbar-track { background: #ffffff; }
.mz-scroll-container::-webkit-scrollbar-thumb { background: #e5e5e5; border-radius: 10px; }
.mz-scroll-container::-webkit-scrollbar-thumb:hover { background: #d4d4d4; }
</style>
</head>

<body>

<nav class="navbar navbar-custom sticky-top">
  <div class="container-xl">
    <a class="brand-wrapper" href="dashboard.php">
      <img src="../assets/mangazone.png" alt="M" class="brand-logo-round" onerror="this.style.visibility='hidden'; this.style.width='0'">
      <span class="brand-text">MangaZone<span class="brand-accent">/ repository</span></span>
    </a>
    <a href="logout.php" class="btn-custom-secondary ms-auto">Sign out</a>
  </div>
</nav>

<div class="container-xl py-4">
    <div class="mz-breadcrumb mb-4">
        <a href="dashboard.php">Repositories</a> / <span class="text-secondary">new</span>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4 order-2 order-lg-1">
            <div class="mz-box">
                <div class="mz-box-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold text-dark text-truncate">Database Index Registry</span>
                    <span class="badge rounded-pill" style="background-color: #f5f5f5; color: #666666; border: 1px solid #e5e5e5; font-size: 11px; font-weight: 500; padding: 4px 8px;"><?= count($mangas) ?> items</span>
                </div>
                
                <div class="p-3 border-bottom" style="border-color: #e5e5e5 !important; background-color: #ffffff;">
                    <input type="text" id="idSearchInput" class="form-control mz-input-search w-100" placeholder="Filter tracked IDs or titles...">
                </div>
                
                <div class="mz-scroll-container" id="idListGroup">
                    <?php if(!empty($mangas)): ?>
                        <?php foreach($mangas as $m): 
                            $mId = $m['malId'] ?? ($m['mal_id'] ?? 0);
                            $mTitle = $m['title'] ?? 'Untitled';
                        ?>
                            <div class="mz-list-item" data-search-text="<?= htmlspecialchars(strtolower($mId . ' ' . $mTitle)) ?>">
                                <span class="mz-item-title text-truncate" title="<?= htmlspecialchars($mTitle) ?>">
                                    <?= htmlspecialchars($mTitle) ?>
                                </span>
                                <span class="mz-badge-id">ID <?= htmlspecialchars($mId) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted text-center py-5" style="font-size: 13px; font-weight: 500;">No indexes discovered.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 order-1 order-lg-2">
            <div class="bg-white p-4 rounded-3 border" style="border-color: #e5e5e5; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);">
                <h2 class="mz-main-heading mb-4">Create a new manga repository</h2>

                <?php if (!empty($success_message)): ?>
                    <div class="mz-banner mz-banner-success mb-4">
                        <svg aria-hidden="true" height="16" viewBox="0 0 16 16" width="16" fill="currentColor"><path d="M0 8a8 8 0 1116 0A8 8 0 010 8zm11.58-2.22a.75.75 0 00-1.06-1.06L7 8.19 5.48 6.67a.75.75 0 00-1.06 1.06l2.05 2.05a.75.75 0 001.06 0l4.05-4.05z"></path></svg>
                        <div><?= $success_message ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="mz-banner mz-banner-danger mb-4">
                        <svg aria-hidden="true" height="16" viewBox="0 0 16 16" width="16" fill="currentColor"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.03 11.3c.66 1.235-.236 2.653-1.544 2.653H1.971C.663 15 1.711-2.43 2.15 2.653l6.03-11.3zM1.97 13.5h12.058L8 2.114 1.971 13.5zM8 11.25a.75.75 0 110-1.5.75.75 0 010 1.5zm0-6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 018 5.25z"></path></svg>
                        <div><?= $error_message ?></div>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mz-form-group">
                        <label for="title" class="mz-label">Manga Title</label>
                        <input type="text" name="title" id="title" class="form-control mz-input-text" placeholder="e.g., Chainsaw Man, Berserk" required>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4 mz-form-group">
                            <label for="malId" class="mz-label">MAL ID Reference</label>
                            <div class="mz-label-desc">Unique identifier index</div>
                            <input type="number" name="malId" id="malId" class="form-control mz-input-text" placeholder="e.g., 44485" required>
                        </div>

                        <div class="col-12 col-md-4 mz-form-group">
                            <label for="score" class="mz-label">Rating Score</label>
                            <div class="mz-label-desc">Global target evaluation</div>
                            <input type="text" name="score" id="score" class="form-control mz-input-text" placeholder="0.0" value="0.0">
                        </div>

                        <div class="col-12 col-md-4 mz-form-group">
                            <label for="chapters" class="mz-label">Total Chapters</label>
                            <div class="mz-label-desc">Current build cycle</div>
                            <input type="number" name="chapters" id="chapters" class="form-control mz-input-text" placeholder="0" value="0">
                        </div>
                    </div>

                    <div class="mz-form-group">
                        <label for="imageUrl" class="mz-label">Image Artifact URL</label>
                        <input type="url" name="imageUrl" id="imageUrl" class="form-control mz-input-text" placeholder="https://domain.com/assets/cover.jpg">
                    </div>

                    <div class="mz-form-group">
                        <label for="synopsis" class="mz-label">Synopsis Description <span style="font-weight: 400; color: #a3a3a3;">(Optional)</span></label>
                        <div class="mz-label-desc">Write a comprehensive summary overview about this work repository.</div>
                        <textarea name="synopsis" id="synopsis" class="form-control mz-input-text mz-textarea-md" placeholder="Provide documentation summary info inside this manga dataset..."></textarea>
                    </div>

                    <div class="mz-form-group">
                        <label for="genres" class="mz-label">Genres</label>
                        <div class="mz-label-desc">Pisahkan dengan tanda koma (e.g., Action, Adventure, Fantasy).</div>
                        <input type="text" name="genres" id="genres" class="form-control mz-input-text" placeholder="Action, Adventure, Comedy">
                    </div>

                    <div class="pt-3 d-flex gap-2 justify-content-end border-top" style="border-color: #e5e5e5 !important;">
                        <a href="dashboard.php" class="btn mz-btn-cancel">Cancel</a>
                        <button type="submit" class="btn btn-mz-primary">Create repository</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const idSearchInput = document.getElementById('idSearchInput');
    const idRows = document.querySelectorAll('.mz-list-item');

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
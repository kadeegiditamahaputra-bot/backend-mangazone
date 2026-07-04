<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require "../config/db.php";

$success_message = "";
$error_message = "";
$manga_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$manga_id) {
    header("Location: dashboard.php");
    exit;
}

// =========================================================================
// 1. AMBIL DATA MANGA LANGSUNG DARI DATABASE
// =========================================================================

// Ambil data detail manga berdasarkan malId
$stmt_manga = mysqli_prepare($conn, "SELECT * FROM manga WHERE malId = ?");
mysqli_stmt_bind_param($stmt_manga, "i", $manga_id);
mysqli_stmt_execute($stmt_manga);
$result_manga = mysqli_stmt_get_result($stmt_manga);
$manga_data = mysqli_fetch_assoc($result_manga);
mysqli_stmt_close($stmt_manga);

// Jika data tidak ditemukan di database, tendang kembali ke dashboard
if (!$manga_data) {
    header("Location: dashboard.php");
    exit;
}

// =========================================================================
// 2. LOGIKA PROSES POST ACTION (UPDATE METADATA UTAMA MANGA)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['action_update_manga'])) {
        $title = trim($_POST['title']);
        $score = floatval($_POST['score']);
        $chapters = intval($_POST['chapters']);
        $imageUrl = trim($_POST['imageUrl']);
        $synopsis = trim($_POST['synopsis']);

        if (empty($title)) {
            $error_message = "Manga title cannot be empty.";
        } else {
            try {
                $stmt_update = mysqli_prepare(
                    $conn, 
                    "UPDATE manga SET title = ?, score = ?, chapters = ?, imageUrl = ?, synopsis = ? WHERE malId = ?"
                );
                mysqli_stmt_bind_param($stmt_update, "sdissi", $title, $score, $chapters, $imageUrl, $synopsis, $manga_id);
                
                if (mysqli_stmt_execute($stmt_update)) {
                    $success_message = "Repository settings updated successfully.";
                    
                    // Refresh data display lokal supaya input langsung terupdate di layar
                    $manga_data['title'] = $title;
                    $manga_data['score'] = $score;
                    $manga_data['chapters'] = $chapters;
                    $manga_data['imageUrl'] = $imageUrl;
                    $manga_data['synopsis'] = $synopsis;
                } else {
                    $error_message = "Failed to update database: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt_update);
            } catch (Exception $e) {
                $error_message = "Failed to update metadata: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings · <?= htmlspecialchars($manga_data['title']) ?></title>

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

/* PANEL BOX PANELS */
.mz-box {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}
.mz-box-header {
    background-color: #ffffff;
    border-bottom: 1px solid #e5e5e5;
    padding: 16px 20px;
    border-top-left-radius: 11px;
    border-top-right-radius: 11px;
    font-weight: 600;
    font-size: 15px;
    color: #171717;
}
.mz-box-body { 
    padding: 20px; 
}

/* INTERFACE FORM CONTROL */
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
    margin-top: 4px; 
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
    font-size: 14px; 
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

/* NOTIFICATION BANNER */
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
</style>
</head>

<body>

<nav class="navbar navbar-custom sticky-top">
  <div class="container-xl">
    <a class="brand-wrapper" href="dashboard.php">
      <img src="../assets/mangazone.png" alt="M" class="brand-logo-round" onerror="this.style.visibility='hidden'; this.style.width='0'">
      <span class="brand-text">MangaZone<span class="brand-accent">/ dashboard</span></span>
    </a>
    <a href="logout.php" class="btn-custom-secondary ms-auto">Sign out</a>
  </div>
</nav>

<div class="container-xl py-4">
    
    <div class="mz-breadcrumb mb-4">
        <a href="dashboard.php">Repositories</a> / <a href="dashboard.php"><?= htmlspecialchars($manga_data['title']) ?></a> / <span class="text-secondary">Settings</span>
    </div>

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

    <div class="row g-4">
        
        <div class="col-12">
            <div class="mz-box">
                <div class="mz-box-header">Repository General Settings</div>
                <div class="mz-box-body">
                    <form action="" method="POST">
                        <input type="hidden" name="action_update_manga" value="1">
                        
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="title" class="mz-label">Manga Repository Name</label>
                                <input type="text" name="title" id="title" class="form-control mz-input-text" value="<?= htmlspecialchars($manga_data['title']) ?>" required>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <label class="mz-label">MAL ID Reference</label>
                                <input type="text" class="form-control mz-input-text" style="background-color: #f5f5f5; color: #737373 !important; border-color: #e5e5e5;" value="<?= htmlspecialchars($manga_id) ?>" readonly>
                                <div class="mz-label-desc">ID index lock protected.</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="score" class="mz-label">Global Evaluation Score</label>
                                <input type="text" name="score" id="score" class="form-control mz-input-text" value="<?= htmlspecialchars($manga_data['score'] ?? '0.0') ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="chapters" class="mz-label">Total Catalog Chapters</label>
                                <input type="number" name="chapters" id="chapters" class="form-control mz-input-text" value="<?= htmlspecialchars($manga_data['chapters'] ?? 0) ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="imageUrl" class="mz-label">Image Cover Target URL</label>
                                <input type="url" name="imageUrl" id="imageUrl" class="form-control mz-input-text" value="<?= htmlspecialchars($manga_data['imageUrl'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="synopsis" class="mz-label">Synopsis Description</label>
                                <textarea name="synopsis" id="synopsis" class="form-control mz-input-text mz-textarea-md" placeholder="Describe the overview breakdown here..."><?= htmlspecialchars($manga_data['synopsis'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 d-flex gap-2 justify-content-end border-top" style="border-color: #e5e5e5 !important;">
                            <button type="submit" class="btn btn-mz-primary">Save core configuration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
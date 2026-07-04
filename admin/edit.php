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
// 1. AMBIL DATA MANGA & CHAPTER LANGSUNG DARI DATABASE (REAL)
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

// Ambil data asli daftar chapter dari database berdasarkan manga_id (malId)
$chapter_list = [];
$stmt_ch_list = mysqli_prepare($conn, "SELECT * FROM chapter WHERE manga_id = ? ORDER BY chapter_number ASC");
mysqli_stmt_bind_param($stmt_ch_list, "i", $manga_id);
mysqli_stmt_execute($stmt_ch_list);
$result_ch = mysqli_stmt_get_result($stmt_ch_list);
while ($row = mysqli_fetch_assoc($result_ch)) {
    $chapter_list[] = $row;
}
mysqli_stmt_close($stmt_ch_list);


// =========================================================================
// 2. LOGIKA PROSES POST ACTION (UPDATE MANGA & MANAGEMENT CHAPTER)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION A: Update Metadata Utama Manga
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

    // ACTION B: Tambah Chapter Baru
    if (isset($_POST['action_add_chapter'])) {
        $ch_num = intval($_POST['new_chapter_num']);
        $ch_content = trim($_POST['new_chapter_title']); // Berfungsi sebagai data 'content' di DB

        if ($ch_num <= 0 || empty($ch_content)) {
            $error_message = "Please fill valid chapter index and blueprint content.";
        } else {
            try {
                // Cek apakah nomor chapter tersebut sudah ada sebelumnya untuk manga ini
                $stmt_cek_ch = mysqli_prepare($conn, "SELECT id FROM chapter WHERE manga_id = ? AND chapter_number = ?");
                mysqli_stmt_bind_param($stmt_cek_ch, "ii", $manga_id, $ch_num);
                mysqli_stmt_execute($stmt_cek_ch);
                mysqli_stmt_store_result($stmt_cek_ch);
                
                if (mysqli_stmt_num_rows($stmt_cek_ch) > 0) {
                    $error_message = "Chapter #{$ch_num} already exists in this manifest tree.";
                    mysqli_stmt_close($stmt_cek_ch);
                } else {
                    mysqli_stmt_close($stmt_cek_ch);
                    
                    // Lakukan insert data chapter baru sesuai kolom database kamu (Tanpa release_date)
                    $stmt_insert_ch = mysqli_prepare(
                        $conn, 
                        "INSERT INTO chapter (manga_id, chapter_number, content) VALUES (?, ?, ?)"
                    );
                    mysqli_stmt_bind_param($stmt_insert_ch, "iis", $manga_id, $ch_num, $ch_content);
                    
                    if (mysqli_stmt_execute($stmt_insert_ch)) {
                        $success_message = "Chapter #{$ch_num} deployed successfully into tree.";
                        
                        // Tarik ulang daftar chapter terupdate dari DB agar langsung tampil di baris bawah
                        $chapter_list = [];
                        $stmt_refresh_ch = mysqli_prepare($conn, "SELECT * FROM chapter WHERE manga_id = ? ORDER BY chapter_number ASC");
                        mysqli_stmt_bind_param($stmt_refresh_ch, "i", $manga_id);
                        mysqli_stmt_execute($stmt_refresh_ch);
                        $result_refresh = mysqli_stmt_get_result($stmt_refresh_ch);
                        while ($row = mysqli_fetch_assoc($result_refresh)) {
                            $chapter_list[] = $row;
                        }
                        mysqli_stmt_close($stmt_refresh_ch);
                    } else {
                        $error_message = "Failed to insert chapter: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt_insert_ch);
                }
            } catch (Exception $e) {
                $error_message = "Failed to branch new chapter: " . $e->getMessage();
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
<link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:-apple-system,BlinkMacSystemFont,Segoe+UI,Helvetica,Arial,sans-serif&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

<style>
/* GitHub Dark Theme Palette */
body {
    background-color: #0d1117;
    color: #c9d1d9;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
    font-size: 14px;
}

/* NAVBAR */
.navbar-github {
    background-color: #161b22;
    border-bottom: 1px solid #30363d;
    padding: 0.75rem 0;
}
.brand-wrapper { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.brand-logo-round { width: 32px; height: 32px; border-radius: 6px; border: 1px solid #30363d; }
.brand-text { font-size: 0.95rem; font-weight: 600; color: #f0f6fc; }
.brand-accent { color: #8b949e; font-weight: 400; margin-left: 4px; }

/* BREADCRUMB */
.gh-breadcrumb { font-size: 14px; color: #8b949e; }
.gh-breadcrumb a { color: #58a6ff; text-decoration: none; }
.gh-breadcrumb a:hover { text-decoration: underline; }

/* METADATA BOX PANEL */
.gh-box {
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 6px;
    margin-bottom: 24px;
}
.gh-box-header {
    background-color: #1f242c;
    border-bottom: 1px solid #30363d;
    padding: 12px 16px;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    font-weight: 600;
    color: #e6edf3;
}
.gh-box-body { padding: 16px; }

/* INTERFACE FORM CONTROL */
.gh-label { font-size: 14px; font-weight: 600; color: #e6edf3; margin-bottom: 6px; display: block; }
.gh-label-desc { font-size: 12px; color: #8b949e; margin-top: -4px; margin-bottom: 6px; }
.gh-input-text {
    background-color: #0d1117;
    border: 1px solid #30363d;
    color: #e6edf3 !important;
    font-size: 14px; padding: 5px 12px; border-radius: 6px; width: 100%;
}
.gh-input-text:focus {
    background-color: #0d1117; border-color: #1f6feb; box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.3); outline: none;
}
.gh-textarea-md { font-family: 'Fira Code', monospace; font-size: 13px; min-height: 100px; resize: vertical; }

/* BUTTONS BUTTONS */
.btn-gh-primary { background-color: #238636; color: #ffffff !important; border: 1px solid rgba(240, 246, 252, 0.1); font-size: 14px; font-weight: 500; padding: 5px 16px; border-radius: 6px; }
.btn-gh-primary:hover { background-color: #2ea043; }
.btn-gh-cancel { background-color: #21262d; border: 1px solid #30363d; color: #c9d1d9 !important; font-size: 14px; font-weight: 500; padding: 5px 16px; border-radius: 6px; text-decoration: none; }
.btn-gh-cancel:hover { background-color: #30363d; border-color: #8b949e; }
.btn-gh-danger { background-color: #21262d; border: 1px solid #f85149; color: #f85149 !important; font-size: 13px; padding: 4px 12px; border-radius: 6px; text-decoration: none; }
.btn-gh-danger:hover { background-color: #b62324; color: #ffffff !important; }

/* CHAPTER REGISTRY TABLE/LIST */
.ch-table-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px; border-bottom: 1px solid #21262d;
}
.ch-table-row:last-child { border-bottom: none; }
.ch-table-row:hover { background-color: #1f242c; }
.ch-number-tag { font-family: 'Fira Code', monospace; font-weight: 600; color: #58a6ff; background-color: rgba(56,139,253,0.1); padding: 2px 8px; border-radius: 4px; font-size: 12px; }

/* NOTIFICATION BANNER */
.gh-banner { border-radius: 6px; padding: 12px 16px; font-size: 13px; display: flex; align-items: center; gap: 10px; }
.gh-banner-success { background-color: rgba(56, 139, 253, 0.1); border: 1px solid rgba(56, 139, 253, 0.4); color: #58a6ff; }
.gh-banner-danger { background-color: rgba(248, 81, 73, 0.1); border: 1px solid rgba(248, 81, 73, 0.4); color: #ff7b72; }
</style>
</head>

<body>

<nav class="navbar navbar-github sticky-top">
  <div class="container-xl">
    <a class="brand-wrapper" href="dashboard.php">
      <img src="../assets/mangazone.png" alt="M" class="brand-logo-round" onerror="this.style.visibility='hidden'; this.style.width='0'">
      <span class="brand-text">MangaZone<span class="brand-accent">/ command-center</span></span>
    </a>
  </div>
</nav>

<div class="container-xl py-4">
    
    <div class="gh-breadcrumb mb-4">
        <a href="dashboard.php">Repositories</a> / <a href="dashboard.php"><?= htmlspecialchars($manga_data['title']) ?></a> / <span class="text-white">Settings</span>
    </div>

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

    <div class="row g-4">
        
        <div class="col-12">
            <div class="gh-box">
                <div class="gh-box-header">Repository General Settings</div>
                <div class="gh-box-body">
                    <form action="" method="POST">
                        <input type="hidden" name="action_update_manga" value="1">
                        
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="title" class="gh-label">Manga Repository Name</label>
                                <input type="text" name="title" id="title" class="form-control gh-input-text" value="<?= htmlspecialchars($manga_data['title']) ?>" required>
                            </div>
                            
                            <div class="col-12 col-md-4">
                                <label class="gh-label">MAL ID Reference</label>
                                <input type="text" class="form-control gh-input-text" style="background-color: #21262d; color: #8b949e !important;" value="<?= htmlspecialchars($manga_id) ?>" readonly>
                                <div class="gh-label-desc" style="margin-top: 4px;">ID index lock protected.</div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="score" class="gh-label">Global Evaluation Score</label>
                                <input type="text" name="score" id="score" class="form-control gh-input-text" value="<?= htmlspecialchars($manga_data['score'] ?? '0.0') ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="chapters" class="gh-label">Total Catalog Chapters</label>
                                <input type="number" name="chapters" id="chapters" class="form-control gh-input-text" value="<?= htmlspecialchars($manga_data['chapters'] ?? 0) ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="imageUrl" class="gh-label">Image Cover Target URL</label>
                                <input type="url" name="imageUrl" id="imageUrl" class="form-control gh-input-text" value="<?= htmlspecialchars($manga_data['imageUrl'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="synopsis" class="gh-label">Synopsis Description (README.md)</label>
                                <textarea name="synopsis" id="synopsis" class="form-control gh-input-text gh-textarea-md" placeholder="Describe the overview breakdown here..."><?= htmlspecialchars($manga_data['synopsis'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 d-flex gap-2 justify-content-end border-top" style="border-color: #21262d !important;">
                            <button type="submit" class="btn btn-gh-primary">Save core configuration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="gh-box">
                <div class="gh-box-header d-flex justify-content-between align-items-center">
                    <span>Sub-Manifest Tree (Chapters Management)</span>
                    <span class="badge" style="background-color: #30363d; font-size: 11px;"><?= count($chapter_list) ?> Active Nodes</span>
                </div>
                
                <div class="gh-box-body">
                    <form action="" method="POST" class="row g-2 align-items-end mb-4 pb-4 border-bottom" style="border-color: #21262d !important;">
                        <input type="hidden" name="action_add_chapter" value="1">
                        
                        <div class="col-6 col-md-2">
                            <label for="new_chapter_num" class="gh-label">Ch. Index</label>
                            <input type="number" name="new_chapter_num" id="new_chapter_num" class="form-control gh-input-text" placeholder="e.g. 4" required>
                        </div>
                        <div class="col-6 col-md-7">
                            <label for="new_chapter_title" class="gh-label">Chapter Content Blueprint</label>
                            <input type="text" name="new_chapter_title" id="new_chapter_title" class="form-control gh-input-text" placeholder="e.g. Arrival of the Sovereign Team" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <button type="submit" class="btn btn-gh-primary w-100">+ Deploy Chapter</button>
                        </div>
                    </form>

                    <div class="border rounded-2" style="border-color: #30363d !important; background-color: #0d1117;">
                        <?php if(!empty($chapter_list)): ?>
                            <?php foreach($chapter_list as $ch): ?>
                                <div class="ch-table-row">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="ch-number-tag">ch_<?= sprintf("%02d", $ch['chapter_number']) ?></span>
                                        <span class="text-white fw-semibold" style="font-size: 13px;"><?= htmlspecialchars($ch['content']) ?></span>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="chapter_edit.php?id=<?= $ch['id'] ?>&manga_id=<?= $manga_id ?>" class="btn-gh-cancel" style="font-size: 12px; padding: 2px 10px;">
                                            Modify
                                        </a>
                                        <a href="chapter_delete.php?id=<?= $ch['id'] ?>&manga_id=<?= $manga_id ?>" 
                                           class="btn-gh-danger" 
                                           onclick="return confirm('Hapus permanen index chapter <?= $ch['chapter_number'] ?> ini?')">
                                             Purge
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">No structural chapters deployed yet.</div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
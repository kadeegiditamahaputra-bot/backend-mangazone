<?php
session_start();
if(!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require "../config/db.php";

$json = @file_get_contents("http://localhost/mangazone-backend/api/manga.php");
$api_response = json_decode($json, true);

$mangas = [];

if (!empty($api_response)) {
    if (isset($api_response['data']) && is_array($api_response['data'])) {
        $mangas = $api_response['data'];
    } elseif (is_array($api_response)) {
        $mangas = $api_response;
    }
}

// Koleksi semua genre unik dari API
$genres = [];
if (!empty($mangas)) {
    foreach ($mangas as $m) {
        if (isset($m['genres']) && is_array($m['genres'])) {
            foreach ($m['genres'] as $g) {
                if (isset($g['name'])) {
                    $cleanGenre = strtolower(trim($g['name']));
                    if (!empty($cleanGenre)) {
                        $genres[$cleanGenre] = trim($g['name']);
                    }
                }
            }
        }
    }
}

// Fallback jika API kosong
if (empty($genres)) {
    $genres = [
        'action'        => 'Action',
        'romance'       => 'Romance',
        'fantasy'       => 'Fantasy',
        'sci-fi'        => 'Sci-Fi',
        'slice of life' => 'Slice of Life',
        'adventure'     => 'Adventure'
    ];
}

// Palette warna dot genre
$genreColors = [
    'action'        => '#ff4d4d',
    'romance'       => '#ff7675',
    'fantasy'       => '#a29bfe',
    'sci-fi'        => '#00cec9',
    'slice of life' => '#74b9ff',
    'adventure'     => '#e17055',
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MangaZone - Repository Dashboard</title>

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
.panel-subnav {
    padding-bottom: 20px;
    margin-bottom: 24px;
}
.form-input-custom {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    color: #171717;
    font-size: 14px;
    padding: 8px 14px;
    border-radius: 8px;
    transition: all 0.2s ease;
}
.form-input-custom::placeholder {
    color: #a3a3a3;
}
.form-input-custom:focus {
    background-color: #ffffff;
    border-color: #171717;
    color: #171717;
    box-shadow: 0 0 0 4px rgba(23, 23, 23, 0.08);
    outline: none;
}
.btn-custom-primary {
    background-color: #171717;
    color: #ffffff !important;
    border: 1px solid #171717;
    font-size: 14px;
    font-weight: 500;
    padding: 8px 18px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.btn-custom-primary:hover {
    background-color: #404040;
    border-color: #404040;
    transform: translateY(-1px);
}
.manga-card-ui {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    padding: 20px;
    height: 100%;
    display: flex;
    flex-direction: row;
    gap: 18px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.04);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.manga-card-ui:hover {
    border-color: #d4d4d4;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    transform: translateY(-2px);
}
.manga-cover-box {
    width: 80px;
    height: 110px;
    flex-shrink: 0;
    border-radius: 8px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
    background-color: #f5f5f5;
}
.manga-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.manga-detail-box {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    width: 100%;
}
.manga-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}
.manga-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #171717;
    text-decoration: none;
    word-break: break-all;
    transition: color 0.15s ease;
}
.manga-card-title:hover {
    color: #737373;
}
.ui-badge-id {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    background-color: #f5f5f5;
    border: 1px solid #e5e5e5;
    color: #525252;
    font-family: monospace;
}
.manga-card-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 14px;
    font-size: 12px;
    color: #737373;
    margin-top: 6px;
    font-weight: 500;
}
.ui-genre-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    vertical-align: middle;
    margin-right: 4px;
}
.manga-action-row {
    display: flex;
    gap: 8px;
    margin-top: 14px;
}
.btn-ui-action {
    font-size: 12px;
    font-weight: 500;
    padding: 6px 14px;
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    color: #404040;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}
.btn-ui-action:hover {
    background-color: #fafafa;
    color: #171717;
    border-color: #d4d4d4;
}
.btn-ui-danger {
    color: #ef4444;
    border-color: #fca5a5;
    background-color: #fff5f5;
}
.btn-ui-danger:hover {
    background-color: #ef4444;
    border-color: #ef4444;
    color: #ffffff !important;
}
.empty-state-box {
    background-color: #ffffff;
    border: 1px dashed #d4d4d4;
    border-radius: 12px;
}
</style>
</head>

<body>

<nav class="navbar navbar-custom sticky-top">
  <div class="container-xl">
    <a class="brand-wrapper" href="#">
      <img src="../assets/mangazone.png" alt="M" class="brand-logo-round" onerror="this.style.visibility='hidden'; this.style.width='0'">
      <span class="brand-text">MangaZone<span class="brand-accent">/ dashboard</span></span>
    </a>
    <a href="logout.php" class="btn-custom-secondary ms-auto">Sign out</a>
  </div>
</nav>

<div class="container-xl py-4">

  <div class="panel-subnav">
    <div class="row g-2 align-items-center">
      
      <div class="col-12 col-md-5">
        <input type="text" id="mangaSearch" class="form-control form-input-custom w-100" placeholder="Search manga titles...">
      </div>

      <div class="col-6 col-md-3">
        <select id="genreFilter" class="form-select form-input-custom w-100">
          <option value="">Filter: All Genres</option>
          <?php foreach ($genres as $genreKey => $genreName): ?>
            <option value="<?= htmlspecialchars($genreKey) ?>"><?= htmlspecialchars($genreName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-6 col-md-4 text-end">
        <a href="manga_add.php" class="btn-custom-primary w-100 w-sm-auto">
          <svg class="me-2" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>Add New Manga
        </a>
      </div>

    </div>
  </div>

  <div class="row row-cols-1 row-cols-md-2 g-3" id="mangaGrid">

    <?php if(!empty($mangas)): ?>
        <?php foreach ($mangas as $manga): ?>
          <?php
            $image = $manga['imageUrl'] 
                     ?? ($manga['images']['jpg']['image_url'] ?? 'https://via.placeholder.com/80x110?text=No+Cover');

            $title = $manga['title'] ?? 'Untitled Manga';
            $chapters = $manga['chapters'] ?? 0;
            $score = $manga['score'] ?? '0.0';
            $malId = $manga['malId'] ?? ($manga['mal_id'] ?? 0);
            
            $mangaGenreList = [];
            $primaryGenre = 'adventure'; 

            if (isset($manga['genres']) && is_array($manga['genres'])) {
                foreach ($manga['genres'] as $g) { 
                    if(isset($g['name'])) {
                        $mangaGenreList[] = strtolower(trim($g['name']));
                    }
                }
                if(!empty($mangaGenreList)) {
                    $primaryGenre = $mangaGenreList[0];
                }
            } else {
                $mangaGenreList[] = 'adventure';
                $primaryGenre = 'adventure';
            }
            
            $genreAttribute = implode(',', $mangaGenreList);
            $dotColor = $genreColors[$primaryGenre] ?? '#a3a3a3';
          ?>

          <div class="col manga-item" data-title="<?= htmlspecialchars(strtolower(trim($title))) ?>" data-genres="<?= htmlspecialchars($genreAttribute) ?>">
            <div class="manga-card-ui">
              
              <div class="manga-cover-box">
                <img src="<?= htmlspecialchars($image) ?>" class="manga-cover-img" alt="Cover" loading="lazy">
              </div>

              <div class="manga-detail-box">
                <div>
                  <div class="manga-card-header">
                    <a href="manga_edit.php?id=<?= $malId ?>" class="manga-card-title text-truncate" title="<?= htmlspecialchars($title) ?>">
                      <?= htmlspecialchars($title) ?>
                    </a>
                    <span class="ui-badge-id">#<?= htmlspecialchars($malId) ?></span>
                  </div>

                  <div class="manga-card-meta">
                    <span>
                      <span class="ui-genre-dot" style="background-color: <?= $dotColor ?>;"></span>
                      <?= htmlspecialchars($genres[$primaryGenre] ?? ucfirst($primaryGenre)) ?>
                    </span>
                    <span style="color: #eab308; display: inline-flex; align-items: center; gap: 3px;">
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg><span style="color: #404040; font-weight: 600;"><?= $score ?></span>
                    </span>
                    <span>
                      <?= $chapters ?> Chapters
                    </span>
                  </div>
                </div>

                <div class="manga-action-row">
                  <a href="edit.php?id=<?= $malId ?>" class="btn-ui-action"><svg viewBox="0 0 24 24" width="13" height="13" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4Z"></path></svg>Manage</a>
                  <a href="manga_delete.php?id=<?= $malId ?>" class="btn-ui-action btn-ui-danger">Delete</a>
                </div>

              </div>

            </div>
          </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5 empty-state-box">
            <p class="text-muted mb-1" style="font-size: 15px; font-weight: 500;">⚠️ Data komik tidak ditemukan.</p>
            <small class="text-secondary">Pastikan endpoint API di <code>http://localhost/mangazone-backend/api/manga.php</code> mengembalikan data JSON yang valid.</small>
        </div>
    <?php endif; ?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('mangaSearch');
    const genreFilter = document.getElementById('genreFilter');
    const mangaItems = document.querySelectorAll('.manga-item');

    function filterManga() {
        const searchValue = searchInput.value.toLowerCase().trim();
        const selectedGenre = genreFilter.value;

        mangaItems.forEach(item => {
            const title = item.getAttribute('data-title');
            const genres = item.getAttribute('data-genres').split(',').map(g => g.trim());
            
            const matchesSearch = title.includes(searchValue);
            const matchesGenre = selectedGenre === "" || genres.includes(selectedGenre);

            if (matchesSearch && matchesGenre) {
                item.style.setProperty('display', 'block', 'important');
            } else {
                item.style.setProperty('display', 'none', 'important');
            }
        });
    }

    searchInput.addEventListener('input', filterManga);
    genreFilter.addEventListener('change', filterManga);
});
</script>
</body>
</html>
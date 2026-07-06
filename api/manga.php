<?php

header("Content-Type: application/json");
require_once("../config/db.php");
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] != "GET") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method tidak diizinkan"
    ]);
    exit;
}

// --- FUNGSI UNTUK MENGAMBIL GENRE BERDASARKAN MANGA ID ---
function getMangaGenres($conn, $manga_id) {
    $genres = [];
    $sql = "SELECT g.name FROM genre g 
            JOIN manga_genre mg ON g.id = mg.genre_id 
            WHERE mg.manga_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $manga_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $genres[] = $row['name'];
    }
    
    mysqli_stmt_close($stmt);
    return $genres;
}

// --- FUNGSI UNTUK MAPPING KOLOM AGAR COCOK DENGAN FLUTTER ---
function formatMangaData($row, $genres) {
    return [
        "id" => intval($row['id']),
        "malId" => intval($row['mal_id']), // Map mal_id ke malId
        "title" => $row['title'],
        "imageUrl" => $row['image_url'],   // Map image_url ke imageUrl
        "score" => floatval($row['score']),
        "chapters" => intval($row['chapters']),
        "synopsis" => $row['synopsis'] ?? "",
        "genres" => $genres // Array murni hasil JOIN
    ];
}


// =========================================================
// 1. JIKA MEMINTA DETAIL MANGA BERDASARKAN ID (mal_id)
// =========================================================
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Gunakan prepared statement agar lebih aman
    $sql = "SELECT * FROM manga WHERE mal_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        echo json_encode([
            "success" => false,
            "message" => "Manga tidak ditemukan"
        ]);
        exit;
    }

    $manga_raw = mysqli_fetch_assoc($result);
    
    // Ambil list genre dari tabel relasi menggunakan internal ID manga
    $genres = getMangaGenres($conn, $manga_raw['id']);
    
    // Format data agar sesuai kebutuhan Flutter
    $manga_formatted = formatMangaData($manga_raw, $genres);

    echo json_encode([
        "success" => true,
        "data" => $manga_formatted
    ]);

    mysqli_stmt_close($stmt);

// =========================================================
// 2. JIKA MEMINTA SEMUA DAFTAR MANGA (LIST)
// =========================================================
} else {
    $sql = "SELECT * FROM manga ORDER BY score DESC";
    $result = mysqli_query($conn, $sql);
    $data = [];

    while ($row = mysqli_fetch_assoc($result)) {
        // Ambil genre untuk setiap manga di dalam perulangan (loop)
        $genres = getMangaGenres($conn, $row['id']);
        
        // Format dan masukkan ke array list
        $data[] = formatMangaData($row, $genres);
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
}
?>
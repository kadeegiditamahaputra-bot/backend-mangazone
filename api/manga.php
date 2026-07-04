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

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM manga WHERE malId = $id";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 0){
        echo json_encode([
            "success" => false,
            "message" => "Manga tidak ditemukan"
        ]);
        exit;
    }

    $manga = mysqli_fetch_assoc($result);
    
    // SINKRONISASI TIPE DATA
    $manga['malId'] = intval($manga['malId']);
    $manga['score'] = floatval($manga['score']);
    $manga['chapters'] = intval($manga['chapters']);
    // Decode string JSON dari db agar menjadi array JSON murni saat di-encode ulang
    $manga['genres'] = json_decode($manga['genres']); 

    echo json_encode([
        "success" => true,
        "data" => $manga
    ]);

} else {
    $sql = "SELECT * FROM manga ORDER BY score DESC";
    $result = mysqli_query($conn, $sql);
    $data = [];

    while($row = mysqli_fetch_assoc($result)){
        // SINKRONISASI TIPE DATA UNTUK LIST
        $row['malId'] = intval($row['malId']);
        $row['score'] = floatval($row['score']);
        $row['chapters'] = intval($row['chapters']);
        $row['genres'] = json_decode($row['genres']);
        
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
}
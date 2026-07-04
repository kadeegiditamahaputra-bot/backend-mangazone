<?php

$host = "localhost";
$user = "root";
$pass = "300126";   // sesuaikan dengan password MySQL kamu
$db   = "mangazone_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode([
        "success" => false,
        "message" => "Koneksi database gagal",
        "error" => mysqli_connect_error()
    ]));
}

mysqli_set_charset($conn, "utf8");
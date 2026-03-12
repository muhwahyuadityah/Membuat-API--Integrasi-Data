<?php

define('DB_HOST', 'sql112.infinityfree.com');       // dari panel hosting
define('DB_USER', 'if0_41363898');                   // dari panel hosting
define('DB_PASS', 'whyuudtyaa06');          // password kamu
define('DB_NAME', 'if0_41363898_steam_api');         // nama database lengkap

define('BASE_URL', 'https://steamapi.infinityfreeapp.com/api/');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "status" => [
            "code" => 500,
            "description" => "Koneksi database gagal: " . mysqli_connect_error()
        ]
    ]);
    exit();
}

mysqli_set_charset($conn, "utf8");
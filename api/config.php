<?php

define('DB_HOST', '');       // nama host
define('DB_USER', '');       // nama user
define('DB_PASS', '');          // password database
define('DB_NAME', '');         // nama database 

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

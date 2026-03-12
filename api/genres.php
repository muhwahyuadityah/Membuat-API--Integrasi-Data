<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents("php://input"), true);
$action = $_GET['action'] ?? null;
$id     = $_GET['id'] ?? null;

function response($code, $description, $result = null) {
    $res = [
        "status" => [
            "code"        => $code,
            "description" => $description
        ]
    ];
    if ($result !== null) $res["result"] = $result;
    echo json_encode($res);
    exit();
}

// ============================================================
// GET
// ============================================================
if ($method === 'GET') {

    // GET /genres.php?action=countGenres
    if ($action === 'countGenres') {
        $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM genres");
        $row   = mysqli_fetch_assoc($query);
        response(200, "Total genre berhasil diambil", $row);
    }

    // GET /genres.php?action=genreWithMostGames
    if ($action === 'genreWithMostGames') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.name, COUNT(gm.id) as total_games
            FROM genres g
            LEFT JOIN games gm ON g.id = gm.genre_id
            GROUP BY g.id
            ORDER BY total_games DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Genre dengan game terbanyak", $data);
    }

    // GET /genres.php?action=genreWithMostPurchases
    if ($action === 'genreWithMostPurchases') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.name, COUNT(p.id) as total_purchases
            FROM genres g
            LEFT JOIN games gm ON g.id = gm.genre_id
            LEFT JOIN purchases p ON gm.id = p.game_id
            GROUP BY g.id
            ORDER BY total_purchases DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Genre dengan pembelian terbanyak", $data);
    }

    // GET /genres.php?action=genreWithHighestRating
    if ($action === 'genreWithHighestRating') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.name, ROUND(AVG(r.rating), 2) as avg_rating
            FROM genres g
            LEFT JOIN games gm ON g.id = gm.genre_id
            LEFT JOIN reviews r ON gm.id = r.game_id
            GROUP BY g.id
            ORDER BY avg_rating DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Genre dengan rating tertinggi", $data);
    }

    // GET /genres.php?id=1
    if ($id) {
        $id    = mysqli_real_escape_string($conn, $id);
        $query = mysqli_query($conn, "SELECT * FROM genres WHERE id = $id");
        $row   = mysqli_fetch_assoc($query);
        if (!$row) response(404, "Genre tidak ditemukan");
        response(200, "Data genre berhasil diambil", $row);
    }

    // GET /genres.php
    $query = mysqli_query($conn, "SELECT * FROM genres ORDER BY id ASC");
    $data  = [];
    while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
    response(200, "Data semua genre berhasil diambil", $data);
}

// ============================================================
// POST
// ============================================================
if ($method === 'POST') {
    $name = $input['name'] ?? null;

    if (!$name) response(400, "Field name wajib diisi");

    $name = mysqli_real_escape_string($conn, $name);

    // Cek duplikat
    $check = mysqli_query($conn, "SELECT id FROM genres WHERE name = '$name'");
    if (mysqli_num_rows($check) > 0) {
        response(409, "Genre sudah ada");
    }

    $query = mysqli_query($conn, "INSERT INTO genres (name) VALUES ('$name')");

    if (!$query) response(500, "Gagal menambahkan genre");

    $newId = mysqli_insert_id($conn);
    response(201, "Genre berhasil ditambahkan", ["id" => $newId, "name" => $name]);
}

// ============================================================
// PUT
// ============================================================
if ($method === 'PUT') {
    $id   = $input['id'] ?? null;
    $name = $input['name'] ?? null;

    if (!$id || !$name) response(400, "Field id dan name wajib diisi");

    $id   = mysqli_real_escape_string($conn, $id);
    $name = mysqli_real_escape_string($conn, $name);

    // Cek genre ada
    $check = mysqli_query($conn, "SELECT id FROM genres WHERE id = $id");
    if (mysqli_num_rows($check) === 0) {
        response(404, "Genre tidak ditemukan");
    }

    // Cek duplikat nama di genre lain
    $checkName = mysqli_query($conn, "SELECT id FROM genres WHERE name = '$name' AND id != $id");
    if (mysqli_num_rows($checkName) > 0) {
        response(409, "Nama genre sudah digunakan");
    }

    $query = mysqli_query($conn, "UPDATE genres SET name = '$name' WHERE id = $id");

    if (!$query) response(500, "Gagal mengupdate genre");

    response(200, "Genre berhasil diupdate", ["id" => $id, "name" => $name]);
}

// ============================================================
// DELETE
// ============================================================
if ($method === 'DELETE') {
    $id = $input['id'] ?? null;

    if (!$id) response(400, "Field id wajib diisi");

    $id = mysqli_real_escape_string($conn, $id);

    $check = mysqli_query($conn, "SELECT id FROM genres WHERE id = $id");
    if (mysqli_num_rows($check) === 0) {
        response(404, "Genre tidak ditemukan");
    }

    // Cek apakah genre masih dipakai game
    $checkUsed = mysqli_query($conn, "SELECT id FROM games WHERE genre_id = $id");
    if (mysqli_num_rows($checkUsed) > 0) {
        response(409, "Genre tidak bisa dihapus, masih digunakan oleh game");
    }

    $query = mysqli_query($conn, "DELETE FROM genres WHERE id = $id");

    if (!$query) response(500, "Gagal menghapus genre");

    response(200, "Genre berhasil dihapus");
}

response(405, "Method tidak diizinkan");
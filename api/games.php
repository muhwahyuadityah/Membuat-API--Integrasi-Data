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

    // GET /games.php?action=countGames
    if ($action === 'countGames') {
        $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM games");
        $row   = mysqli_fetch_assoc($query);
        response(200, "Total game berhasil diambil", $row);
    }

    // GET /games.php?action=topSalesGames
    if ($action === 'topSalesGames') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.title, g.price, gn.name as genre,
                   COUNT(p.id) as total_sales
            FROM games g
            LEFT JOIN genres gn ON g.genre_id = gn.id
            LEFT JOIN purchases p ON g.id = p.game_id
            GROUP BY g.id
            ORDER BY total_sales DESC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Top 10 game terlaris", $data);
    }

    // GET /games.php?action=topRatedGames
    if ($action === 'topRatedGames') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.title, g.price, gn.name as genre,
                   ROUND(AVG(r.rating), 2) as avg_rating,
                   COUNT(r.id) as total_reviews
            FROM games g
            LEFT JOIN genres gn ON g.genre_id = gn.id
            LEFT JOIN reviews r ON g.id = r.game_id
            GROUP BY g.id
            HAVING total_reviews > 0
            ORDER BY avg_rating DESC, total_reviews DESC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Top 10 game dengan rating tertinggi", $data);
    }

    // GET /games.php?action=latestGames
    if ($action === 'latestGames') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.title, g.price, g.release_date, gn.name as genre
            FROM games g
            LEFT JOIN genres gn ON g.genre_id = gn.id
            ORDER BY g.release_date DESC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "10 game terbaru", $data);
    }

    // GET /games.php?action=mostReviewedGames
    if ($action === 'mostReviewedGames') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.title, g.price, gn.name as genre,
                   COUNT(r.id) as total_reviews,
                   ROUND(AVG(r.rating), 2) as avg_rating
            FROM games g
            LEFT JOIN genres gn ON g.genre_id = gn.id
            LEFT JOIN reviews r ON g.id = r.game_id
            GROUP BY g.id
            ORDER BY total_reviews DESC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "10 game paling banyak direview", $data);
    }

    // GET /games.php?action=cheapestGames
    if ($action === 'cheapestGames') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.title, g.price, gn.name as genre
            FROM games g
            LEFT JOIN genres gn ON g.genre_id = gn.id
            ORDER BY g.price ASC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "10 game termurah", $data);
    }

    // GET /games.php?action=mostExpensiveGames
    if ($action === 'mostExpensiveGames') {
        $query = mysqli_query($conn, "
            SELECT g.id, g.title, g.price, gn.name as genre
            FROM games g
            LEFT JOIN genres gn ON g.genre_id = gn.id
            ORDER BY g.price DESC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "10 game termahal", $data);
    }

    // GET /games.php?action=gamesByGenre&id=2
    if ($action === 'gamesByGenre') {
        if (!$id) response(400, "Parameter id genre wajib diisi");
        $id    = mysqli_real_escape_string($conn, $id);

        // Cek genre ada
        $check = mysqli_query($conn, "SELECT id FROM genres WHERE id = $id");
        if (mysqli_num_rows($check) === 0) response(404, "Genre tidak ditemukan");

        $query = mysqli_query($conn, "
            SELECT g.id, g.title, g.price, g.release_date,
                   gn.name as genre,
                   ROUND(AVG(r.rating), 2) as avg_rating,
                   COUNT(DISTINCT p.id) as total_sales
            FROM games g
            LEFT JOIN genres gn ON g.genre_id = gn.id
            LEFT JOIN reviews r ON g.id = r.game_id
            LEFT JOIN purchases p ON g.id = p.game_id
            WHERE g.genre_id = $id
            GROUP BY g.id
            ORDER BY g.title ASC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Game berdasarkan genre", $data);
    }

    // GET /games.php?action=gameStats
    if ($action === 'gameStats') {
        $query = mysqli_query($conn, "
            SELECT
                COUNT(*) as total_games,
                ROUND(AVG(price), 2) as avg_price,
                MAX(price) as highest_price,
                MIN(price) as lowest_price
            FROM games
        ");
        $row = mysqli_fetch_assoc($query);
        response(200, "Statistik game", $row);
    }

    // GET /games.php?id=1
    if ($id) {
        $id    = mysqli_real_escape_string($conn, $id);
        $query = mysqli_query($conn, "
            SELECT g.*, gn.name as genre,
                   ROUND(AVG(r.rating), 2) as avg_rating,
                   COUNT(DISTINCT r.id) as total_reviews,
                   COUNT(DISTINCT p.id) as total_purchases
            FROM games g
            LEFT JOIN genres gn ON g.genre_id = gn.id
            LEFT JOIN reviews r ON g.id = r.game_id
            LEFT JOIN purchases p ON g.id = p.game_id
            WHERE g.id = $id
            GROUP BY g.id
        ");
        $row = mysqli_fetch_assoc($query);
        if (!$row) response(404, "Game tidak ditemukan");
        response(200, "Data game berhasil diambil", $row);
    }

    // GET /games.php
    $query = mysqli_query($conn, "
        SELECT g.*, gn.name as genre,
               ROUND(AVG(r.rating), 2) as avg_rating,
               COUNT(DISTINCT p.id) as total_purchases
        FROM games g
        LEFT JOIN genres gn ON g.genre_id = gn.id
        LEFT JOIN reviews r ON g.id = r.game_id
        LEFT JOIN purchases p ON g.id = p.game_id
        GROUP BY g.id
        ORDER BY g.created_at DESC
    ");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
    response(200, "Data semua game berhasil diambil", $data);
}

// ============================================================
// POST
// ============================================================
if ($method === 'POST') {
    $title        = $input['title'] ?? null;
    $genre_id     = $input['genre_id'] ?? null;
    $price        = $input['price'] ?? null;
    $release_date = $input['release_date'] ?? null;

    if (!$title || !$genre_id || $price === null || !$release_date) {
        response(400, "Field title, genre_id, price, dan release_date wajib diisi");
    }

    $title        = mysqli_real_escape_string($conn, $title);
    $genre_id     = mysqli_real_escape_string($conn, $genre_id);
    $price        = mysqli_real_escape_string($conn, $price);
    $release_date = mysqli_real_escape_string($conn, $release_date);

    // Cek genre ada
    $check = mysqli_query($conn, "SELECT id FROM genres WHERE id = $genre_id");
    if (mysqli_num_rows($check) === 0) response(404, "Genre tidak ditemukan");

    // Cek duplikat judul
    $checkTitle = mysqli_query($conn, "SELECT id FROM games WHERE title = '$title'");
    if (mysqli_num_rows($checkTitle) > 0) response(409, "Judul game sudah ada");

    $query = mysqli_query($conn, "
        INSERT INTO games (title, genre_id, price, release_date)
        VALUES ('$title', $genre_id, $price, '$release_date')
    ");

    if (!$query) response(500, "Gagal menambahkan game");

    $newId = mysqli_insert_id($conn);
    response(201, "Game berhasil ditambahkan", [
        "id"           => $newId,
        "title"        => $title,
        "genre_id"     => $genre_id,
        "price"        => $price,
        "release_date" => $release_date
    ]);
}

// ============================================================
// PUT
// ============================================================
if ($method === 'PUT') {
    $id           = $input['id'] ?? null;
    $title        = $input['title'] ?? null;
    $genre_id     = $input['genre_id'] ?? null;
    $price        = $input['price'] ?? null;
    $release_date = $input['release_date'] ?? null;

    if (!$id || !$title || !$genre_id || $price === null || !$release_date) {
        response(400, "Field id, title, genre_id, price, dan release_date wajib diisi");
    }

    $id           = mysqli_real_escape_string($conn, $id);
    $title        = mysqli_real_escape_string($conn, $title);
    $genre_id     = mysqli_real_escape_string($conn, $genre_id);
    $price        = mysqli_real_escape_string($conn, $price);
    $release_date = mysqli_real_escape_string($conn, $release_date);

    // Cek game ada
    $check = mysqli_query($conn, "SELECT id FROM games WHERE id = $id");
    if (mysqli_num_rows($check) === 0) response(404, "Game tidak ditemukan");

    // Cek genre ada
    $checkGenre = mysqli_query($conn, "SELECT id FROM genres WHERE id = $genre_id");
    if (mysqli_num_rows($checkGenre) === 0) response(404, "Genre tidak ditemukan");

    // Cek duplikat judul di game lain
    $checkTitle = mysqli_query($conn, "SELECT id FROM games WHERE title = '$title' AND id != $id");
    if (mysqli_num_rows($checkTitle) > 0) response(409, "Judul game sudah digunakan");

    $query = mysqli_query($conn, "
        UPDATE games
        SET title = '$title', genre_id = $genre_id,
            price = $price, release_date = '$release_date'
        WHERE id = $id
    ");

    if (!$query) response(500, "Gagal mengupdate game");

    response(200, "Game berhasil diupdate", [
        "id"           => $id,
        "title"        => $title,
        "genre_id"     => $genre_id,
        "price"        => $price,
        "release_date" => $release_date
    ]);
}

// ============================================================
// DELETE
// ============================================================
if ($method === 'DELETE') {
    $id = $input['id'] ?? null;

    if (!$id) response(400, "Field id wajib diisi");

    $id = mysqli_real_escape_string($conn, $id);

    $check = mysqli_query($conn, "SELECT id FROM games WHERE id = $id");
    if (mysqli_num_rows($check) === 0) response(404, "Game tidak ditemukan");

    $query = mysqli_query($conn, "DELETE FROM games WHERE id = $id");

    if (!$query) response(500, "Gagal menghapus game");

    response(200, "Game berhasil dihapus");
}

response(405, "Method tidak diizinkan");
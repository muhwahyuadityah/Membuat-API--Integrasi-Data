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

    // GET /reviews.php?action=countReviews
    if ($action === 'countReviews') {
        $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM reviews");
        $row   = mysqli_fetch_assoc($query);
        response(200, "Total review berhasil diambil", $row);
    }

    // GET /reviews.php?action=reviewsByGame&id=1
    if ($action === 'reviewsByGame') {
        if (!$id) response(400, "Parameter id game wajib diisi");
        $id = mysqli_real_escape_string($conn, $id);

        // Cek game ada
        $check = mysqli_query($conn, "SELECT id FROM games WHERE id = $id");
        if (mysqli_num_rows($check) === 0) response(404, "Game tidak ditemukan");

        $query = mysqli_query($conn, "
            SELECT r.id, r.rating, r.comment, r.created_at,
                   u.username,
                   g.title as game_title
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN games g ON r.game_id = g.id
            WHERE r.game_id = $id
            ORDER BY r.created_at DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Review berdasarkan game", $data);
    }

    // GET /reviews.php?action=reviewsByUser&id=1
    if ($action === 'reviewsByUser') {
        if (!$id) response(400, "Parameter id user wajib diisi");
        $id = mysqli_real_escape_string($conn, $id);

        // Cek user ada
        $check = mysqli_query($conn, "SELECT id FROM users WHERE id = $id");
        if (mysqli_num_rows($check) === 0) response(404, "User tidak ditemukan");

        $query = mysqli_query($conn, "
            SELECT r.id, r.rating, r.comment, r.created_at,
                   u.username,
                   g.title as game_title, g.price,
                   gn.name as genre
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN games g ON r.game_id = g.id
            LEFT JOIN genres gn ON g.genre_id = gn.id
            WHERE r.user_id = $id
            ORDER BY r.created_at DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Review berdasarkan user", $data);
    }

    // GET /reviews.php?action=topRatedReviews
    if ($action === 'topRatedReviews') {
        $query = mysqli_query($conn, "
            SELECT r.id, r.rating, r.comment, r.created_at,
                   u.username,
                   g.title as game_title
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN games g ON r.game_id = g.id
            WHERE r.rating = 5
            ORDER BY r.created_at DESC
            LIMIT 20
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Review dengan rating bintang 5", $data);
    }

    // GET /reviews.php?action=lowestRatedReviews
    if ($action === 'lowestRatedReviews') {
        $query = mysqli_query($conn, "
            SELECT r.id, r.rating, r.comment, r.created_at,
                   u.username,
                   g.title as game_title
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN games g ON r.game_id = g.id
            WHERE r.rating = 1
            ORDER BY r.created_at DESC
            LIMIT 20
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Review dengan rating bintang 1", $data);
    }

    // GET /reviews.php?action=ratingDistribution
    if ($action === 'ratingDistribution') {
        $query = mysqli_query($conn, "
            SELECT rating,
                   COUNT(*) as total,
                   ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reviews), 2) as percentage
            FROM reviews
            GROUP BY rating
            ORDER BY rating DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Distribusi rating semua review", $data);
    }

    // GET /reviews.php?action=avgRatingByGenre
    if ($action === 'avgRatingByGenre') {
        $query = mysqli_query($conn, "
            SELECT gn.name as genre,
                   ROUND(AVG(r.rating), 2) as avg_rating,
                   COUNT(r.id) as total_reviews
            FROM reviews r
            JOIN games g ON r.game_id = g.id
            LEFT JOIN genres gn ON g.genre_id = gn.id
            GROUP BY gn.id
            ORDER BY avg_rating DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Rata-rata rating berdasarkan genre", $data);
    }

    // GET /reviews.php?action=recentReviews
    if ($action === 'recentReviews') {
        $query = mysqli_query($conn, "
            SELECT r.id, r.rating, r.comment, r.created_at,
                   u.username,
                   g.title as game_title,
                   gn.name as genre
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN games g ON r.game_id = g.id
            LEFT JOIN genres gn ON g.genre_id = gn.id
            ORDER BY r.created_at DESC
            LIMIT 20
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "20 review terbaru", $data);
    }

    // GET /reviews.php?action=reviewStats
    if ($action === 'reviewStats') {
        $query = mysqli_query($conn, "
            SELECT
                COUNT(*) as total_reviews,
                ROUND(AVG(rating), 2) as avg_rating,
                MAX(rating) as highest_rating,
                MIN(rating) as lowest_rating,
                SUM(rating = 5) as bintang_5,
                SUM(rating = 4) as bintang_4,
                SUM(rating = 3) as bintang_3,
                SUM(rating = 2) as bintang_2,
                SUM(rating = 1) as bintang_1
            FROM reviews
        ");
        $row = mysqli_fetch_assoc($query);
        response(200, "Statistik semua review", $row);
    }

    // GET /reviews.php?id=1
    if ($id) {
        $id    = mysqli_real_escape_string($conn, $id);
        $query = mysqli_query($conn, "
            SELECT r.id, r.rating, r.comment, r.created_at,
                   u.username, u.email,
                   g.title as game_title, g.price,
                   gn.name as genre
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN games g ON r.game_id = g.id
            LEFT JOIN genres gn ON g.genre_id = gn.id
            WHERE r.id = $id
        ");
        $row = mysqli_fetch_assoc($query);
        if (!$row) response(404, "Review tidak ditemukan");
        response(200, "Data review berhasil diambil", $row);
    }

    // GET /reviews.php
    $query = mysqli_query($conn, "
        SELECT r.id, r.rating, r.comment, r.created_at,
               u.username,
               g.title as game_title,
               gn.name as genre
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN games g ON r.game_id = g.id
        LEFT JOIN genres gn ON g.genre_id = gn.id
        ORDER BY r.created_at DESC
    ");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
    response(200, "Data semua review berhasil diambil", $data);
}

// ============================================================
// POST
// ============================================================
if ($method === 'POST') {
    $user_id = $input['user_id'] ?? null;
    $game_id = $input['game_id'] ?? null;
    $rating  = $input['rating'] ?? null;
    $comment = $input['comment'] ?? '';

    if (!$user_id || !$game_id || !$rating) {
        response(400, "Field user_id, game_id, dan rating wajib diisi");
    }

    if ($rating < 1 || $rating > 5) {
        response(400, "Rating harus antara 1 sampai 5");
    }

    $user_id = mysqli_real_escape_string($conn, $user_id);
    $game_id = mysqli_real_escape_string($conn, $game_id);
    $rating  = mysqli_real_escape_string($conn, $rating);
    $comment = mysqli_real_escape_string($conn, $comment);

    // Cek user ada
    $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE id = $user_id");
    if (mysqli_num_rows($checkUser) === 0) response(404, "User tidak ditemukan");

    // Cek game ada
    $checkGame = mysqli_query($conn, "SELECT id FROM games WHERE id = $game_id");
    if (mysqli_num_rows($checkGame) === 0) response(404, "Game tidak ditemukan");

    // Cek user sudah pernah review game ini
    $checkDup = mysqli_query($conn, "
        SELECT id FROM reviews
        WHERE user_id = $user_id AND game_id = $game_id
    ");
    if (mysqli_num_rows($checkDup) > 0) {
        response(409, "User sudah memberikan review untuk game ini");
    }

    // Cek user sudah membeli game ini
    $checkPurchase = mysqli_query($conn, "
        SELECT id FROM purchases
        WHERE user_id = $user_id AND game_id = $game_id
    ");
    if (mysqli_num_rows($checkPurchase) === 0) {
        response(403, "User harus membeli game terlebih dahulu sebelum memberikan review");
    }

    $query = mysqli_query($conn, "
        INSERT INTO reviews (user_id, game_id, rating, comment)
        VALUES ($user_id, $game_id, $rating, '$comment')
    ");

    if (!$query) response(500, "Gagal menambahkan review");

    $newId = mysqli_insert_id($conn);
    response(201, "Review berhasil ditambahkan", [
        "id"      => $newId,
        "user_id" => $user_id,
        "game_id" => $game_id,
        "rating"  => $rating,
        "comment" => $comment
    ]);
}

// ============================================================
// PUT
// ============================================================
if ($method === 'PUT') {
    $id      = $input['id'] ?? null;
    $rating  = $input['rating'] ?? null;
    $comment = $input['comment'] ?? '';

    if (!$id || !$rating) {
        response(400, "Field id dan rating wajib diisi");
    }

    if ($rating < 1 || $rating > 5) {
        response(400, "Rating harus antara 1 sampai 5");
    }

    $id      = mysqli_real_escape_string($conn, $id);
    $rating  = mysqli_real_escape_string($conn, $rating);
    $comment = mysqli_real_escape_string($conn, $comment);

    // Cek review ada
    $check = mysqli_query($conn, "SELECT id FROM reviews WHERE id = $id");
    if (mysqli_num_rows($check) === 0) response(404, "Review tidak ditemukan");

    $query = mysqli_query($conn, "
        UPDATE reviews
        SET rating = $rating, comment = '$comment'
        WHERE id = $id
    ");

    if (!$query) response(500, "Gagal mengupdate review");

    response(200, "Review berhasil diupdate", [
        "id"      => $id,
        "rating"  => $rating,
        "comment" => $comment
    ]);
}

// ============================================================
// DELETE
// ============================================================
if ($method === 'DELETE') {
    $id = $input['id'] ?? null;

    if (!$id) response(400, "Field id wajib diisi");

    $id = mysqli_real_escape_string($conn, $id);

    $check = mysqli_query($conn, "SELECT id FROM reviews WHERE id = $id");
    if (mysqli_num_rows($check) === 0) response(404, "Review tidak ditemukan");

    $query = mysqli_query($conn, "DELETE FROM reviews WHERE id = $id");

    if (!$query) response(500, "Gagal menghapus review");

    response(200, "Review berhasil dihapus");
}

response(405, "Method tidak diizinkan");
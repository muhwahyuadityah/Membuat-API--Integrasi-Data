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

    // GET /purchases.php?action=countPurchases
    if ($action === 'countPurchases') {
        $query = mysqli_query($conn, "SELECT COUNT(*) as total FROM purchases");
        $row   = mysqli_fetch_assoc($query);
        response(200, "Total pembelian berhasil diambil", $row);
    }

    // GET /purchases.php?action=purchasesByUser&id=1
    if ($action === 'purchasesByUser') {
        if (!$id) response(400, "Parameter id user wajib diisi");
        $id = mysqli_real_escape_string($conn, $id);

        // Cek user ada
        $check = mysqli_query($conn, "SELECT id FROM users WHERE id = $id");
        if (mysqli_num_rows($check) === 0) response(404, "User tidak ditemukan");

        $query = mysqli_query($conn, "
            SELECT p.id, p.purchase_date,
                   u.username, u.email,
                   g.title as game_title, g.price,
                   gn.name as genre
            FROM purchases p
            JOIN users u ON p.user_id = u.id
            JOIN games g ON p.game_id = g.id
            LEFT JOIN genres gn ON g.genre_id = gn.id
            WHERE p.user_id = $id
            ORDER BY p.purchase_date DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Riwayat pembelian user", $data);
    }

    // GET /purchases.php?action=purchasesByGame&id=1
    if ($action === 'purchasesByGame') {
        if (!$id) response(400, "Parameter id game wajib diisi");
        $id = mysqli_real_escape_string($conn, $id);

        // Cek game ada
        $check = mysqli_query($conn, "SELECT id FROM games WHERE id = $id");
        if (mysqli_num_rows($check) === 0) response(404, "Game tidak ditemukan");

        $query = mysqli_query($conn, "
            SELECT p.id, p.purchase_date,
                   u.username, u.email,
                   g.title as game_title, g.price
            FROM purchases p
            JOIN users u ON p.user_id = u.id
            JOIN games g ON p.game_id = g.id
            WHERE p.game_id = $id
            ORDER BY p.purchase_date DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Daftar pembeli game", $data);
    }

    // GET /purchases.php?action=totalRevenue
    if ($action === 'totalRevenue') {
        $query = mysqli_query($conn, "
            SELECT
                COUNT(p.id) as total_transactions,
                SUM(g.price) as total_revenue,
                ROUND(AVG(g.price), 2) as avg_transaction
            FROM purchases p
            JOIN games g ON p.game_id = g.id
        ");
        $row = mysqli_fetch_assoc($query);
        response(200, "Total revenue platform", $row);
    }

    // GET /purchases.php?action=revenueByGenre
    if ($action === 'revenueByGenre') {
        $query = mysqli_query($conn, "
            SELECT gn.name as genre,
                   COUNT(p.id) as total_sales,
                   SUM(g.price) as total_revenue,
                   ROUND(AVG(g.price), 2) as avg_price
            FROM purchases p
            JOIN games g ON p.game_id = g.id
            LEFT JOIN genres gn ON g.genre_id = gn.id
            GROUP BY gn.id
            ORDER BY total_revenue DESC
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Revenue berdasarkan genre", $data);
    }

    // GET /purchases.php?action=recentPurchases
    if ($action === 'recentPurchases') {
        $query = mysqli_query($conn, "
            SELECT p.id, p.purchase_date,
                   u.username,
                   g.title as game_title, g.price,
                   gn.name as genre
            FROM purchases p
            JOIN users u ON p.user_id = u.id
            JOIN games g ON p.game_id = g.id
            LEFT JOIN genres gn ON g.genre_id = gn.id
            ORDER BY p.purchase_date DESC
            LIMIT 20
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "20 transaksi terbaru", $data);
    }

    // GET /purchases.php?action=topSpenders
    if ($action === 'topSpenders') {
        $query = mysqli_query($conn, "
            SELECT u.id, u.username, u.email,
                   COUNT(p.id) as total_purchases,
                   SUM(g.price) as total_spent
            FROM purchases p
            JOIN users u ON p.user_id = u.id
            JOIN games g ON p.game_id = g.id
            GROUP BY u.id
            ORDER BY total_spent DESC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "Top 10 user dengan pengeluaran terbesar", $data);
    }

    // GET /purchases.php?id=1
    if ($id) {
        $id    = mysqli_real_escape_string($conn, $id);
        $query = mysqli_query($conn, "
            SELECT p.id, p.purchase_date,
                   u.username, u.email,
                   g.title as game_title, g.price,
                   gn.name as genre
            FROM purchases p
            JOIN users u ON p.user_id = u.id
            JOIN games g ON p.game_id = g.id
            LEFT JOIN genres gn ON g.genre_id = gn.id
            WHERE p.id = $id
        ");
        $row = mysqli_fetch_assoc($query);
        if (!$row) response(404, "Data pembelian tidak ditemukan");
        response(200, "Data pembelian berhasil diambil", $row);
    }

    // GET /purchases.php
    $query = mysqli_query($conn, "
        SELECT p.id, p.purchase_date,
               u.username, u.email,
               g.title as game_title, g.price,
               gn.name as genre
        FROM purchases p
        JOIN users u ON p.user_id = u.id
        JOIN games g ON p.game_id = g.id
        LEFT JOIN genres gn ON g.genre_id = gn.id
        ORDER BY p.purchase_date DESC
    ");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
    response(200, "Data semua pembelian berhasil diambil", $data);
}

// ============================================================
// POST
// ============================================================
if ($method === 'POST') {
    $user_id = $input['user_id'] ?? null;
    $game_id = $input['game_id'] ?? null;

    if (!$user_id || !$game_id) {
        response(400, "Field user_id dan game_id wajib diisi");
    }

    $user_id = mysqli_real_escape_string($conn, $user_id);
    $game_id = mysqli_real_escape_string($conn, $game_id);

    // Cek user ada
    $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE id = $user_id");
    if (mysqli_num_rows($checkUser) === 0) response(404, "User tidak ditemukan");

    // Cek game ada
    $checkGame = mysqli_query($conn, "SELECT id FROM games WHERE id = $game_id");
    if (mysqli_num_rows($checkGame) === 0) response(404, "Game tidak ditemukan");

    // Cek sudah pernah beli
    $checkDup = mysqli_query($conn, "
        SELECT id FROM purchases
        WHERE user_id = $user_id AND game_id = $game_id
    ");
    if (mysqli_num_rows($checkDup) > 0) {
        response(409, "User sudah membeli game ini sebelumnya");
    }

    $query = mysqli_query($conn, "
        INSERT INTO purchases (user_id, game_id)
        VALUES ($user_id, $game_id)
    ");

    if (!$query) response(500, "Gagal menambahkan pembelian");

    $newId = mysqli_insert_id($conn);
    response(201, "Pembelian berhasil dicatat", [
        "id"      => $newId,
        "user_id" => $user_id,
        "game_id" => $game_id
    ]);
}

// ============================================================
// PUT
// ============================================================
if ($method === 'PUT') {
    $id      = $input['id'] ?? null;
    $user_id = $input['user_id'] ?? null;
    $game_id = $input['game_id'] ?? null;

    if (!$id || !$user_id || !$game_id) {
        response(400, "Field id, user_id, dan game_id wajib diisi");
    }

    $id      = mysqli_real_escape_string($conn, $id);
    $user_id = mysqli_real_escape_string($conn, $user_id);
    $game_id = mysqli_real_escape_string($conn, $game_id);

    // Cek purchase ada
    $check = mysqli_query($conn, "SELECT id FROM purchases WHERE id = $id");
    if (mysqli_num_rows($check) === 0) response(404, "Data pembelian tidak ditemukan");

    // Cek user ada
    $checkUser = mysqli_query($conn, "SELECT id FROM users WHERE id = $user_id");
    if (mysqli_num_rows($checkUser) === 0) response(404, "User tidak ditemukan");

    // Cek game ada
    $checkGame = mysqli_query($conn, "SELECT id FROM games WHERE id = $game_id");
    if (mysqli_num_rows($checkGame) === 0) response(404, "Game tidak ditemukan");

    // Cek duplikat di record lain
    $checkDup = mysqli_query($conn, "
        SELECT id FROM purchases
        WHERE user_id = $user_id AND game_id = $game_id AND id != $id
    ");
    if (mysqli_num_rows($checkDup) > 0) {
        response(409, "User sudah memiliki pembelian game ini");
    }

    $query = mysqli_query($conn, "
        UPDATE purchases
        SET user_id = $user_id, game_id = $game_id
        WHERE id = $id
    ");

    if (!$query) response(500, "Gagal mengupdate pembelian");

    response(200, "Data pembelian berhasil diupdate", [
        "id"      => $id,
        "user_id" => $user_id,
        "game_id" => $game_id
    ]);
}

// ============================================================
// DELETE
// ============================================================
if ($method === 'DELETE') {
    $id = $input['id'] ?? null;

    if (!$id) response(400, "Field id wajib diisi");

    $id = mysqli_real_escape_string($conn, $id);

    $check = mysqli_query($conn, "SELECT id FROM purchases WHERE id = $id");
    if (mysqli_num_rows($check) === 0) response(404, "Data pembelian tidak ditemukan");

    $query = mysqli_query($conn, "DELETE FROM purchases WHERE id = $id");

    if (!$query) response(500, "Gagal menghapus pembelian");

    response(200, "Data pembelian berhasil dihapus");
}

response(405, "Method tidak diizinkan");
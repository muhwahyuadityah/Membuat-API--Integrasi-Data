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

    // GET /users.php?action=countUsers
    if ($action === 'countUsers') {
        $query  = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
        $row    = mysqli_fetch_assoc($query);
        response(200, "Total users berhasil diambil", $row);
    }

    // GET /users.php?action=userWithMostPurchases
    if ($action === 'userWithMostPurchases') {
        $query = mysqli_query($conn, "
            SELECT u.id, u.username, u.email, COUNT(p.id) as total_purchases
            FROM users u
            LEFT JOIN purchases p ON u.id = p.user_id
            GROUP BY u.id
            ORDER BY total_purchases DESC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "User dengan pembelian terbanyak", $data);
    }

    // GET /users.php?action=userWithMostReviews
    if ($action === 'userWithMostReviews') {
        $query = mysqli_query($conn, "
            SELECT u.id, u.username, u.email, COUNT(r.id) as total_reviews
            FROM users u
            LEFT JOIN reviews r ON u.id = r.user_id
            GROUP BY u.id
            ORDER BY total_reviews DESC
            LIMIT 10
        ");
        $data = [];
        while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
        response(200, "User dengan review terbanyak", $data);
    }

    // GET /users.php?id=1
    if ($id) {
        $id    = mysqli_real_escape_string($conn, $id);
        $query = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
        $row   = mysqli_fetch_assoc($query);
        if (!$row) response(404, "User tidak ditemukan");
        response(200, "Data user berhasil diambil", $row);
    }

    // GET /users.php
    $query = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
    $data  = [];
    while ($row = mysqli_fetch_assoc($query)) $data[] = $row;
    response(200, "Data semua user berhasil diambil", $data);
}

// ============================================================
// POST
// ============================================================
if ($method === 'POST') {
    $username = $input['username'] ?? null;
    $email    = $input['email'] ?? null;

    if (!$username || !$email) {
        response(400, "Field username dan email wajib diisi");
    }

    $username = mysqli_real_escape_string($conn, $username);
    $email    = mysqli_real_escape_string($conn, $email);

    // Cek email duplikat
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        response(409, "Email sudah digunakan");
    }

    $query = mysqli_query($conn, "
        INSERT INTO users (username, email)
        VALUES ('$username', '$email')
    ");

    if (!$query) response(500, "Gagal menambahkan user");

    $newId = mysqli_insert_id($conn);
    response(201, "User berhasil ditambahkan", ["id" => $newId, "username" => $username, "email" => $email]);
}

// ============================================================
// PUT
// ============================================================
if ($method === 'PUT') {
    $id       = $input['id'] ?? null;
    $username = $input['username'] ?? null;
    $email    = $input['email'] ?? null;

    if (!$id || !$username || !$email) {
        response(400, "Field id, username, dan email wajib diisi");
    }

    $id       = mysqli_real_escape_string($conn, $id);
    $username = mysqli_real_escape_string($conn, $username);
    $email    = mysqli_real_escape_string($conn, $email);

    // Cek user ada
    $check = mysqli_query($conn, "SELECT id FROM users WHERE id = $id");
    if (mysqli_num_rows($check) === 0) {
        response(404, "User tidak ditemukan");
    }

    // Cek email duplikat di user lain
    $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $id");
    if (mysqli_num_rows($checkEmail) > 0) {
        response(409, "Email sudah digunakan user lain");
    }

    $query = mysqli_query($conn, "
        UPDATE users SET username = '$username', email = '$email'
        WHERE id = $id
    ");

    if (!$query) response(500, "Gagal mengupdate user");

    response(200, "User berhasil diupdate", ["id" => $id, "username" => $username, "email" => $email]);
}

// ============================================================
// DELETE
// ============================================================
if ($method === 'DELETE') {
    $id = $input['id'] ?? null;

    if (!$id) response(400, "Field id wajib diisi");

    $id = mysqli_real_escape_string($conn, $id);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE id = $id");
    if (mysqli_num_rows($check) === 0) {
        response(404, "User tidak ditemukan");
    }

    $query = mysqli_query($conn, "DELETE FROM users WHERE id = $id");

    if (!$query) response(500, "Gagal menghapus user");

    response(200, "User berhasil dihapus");
}

response(405, "Method tidak diizinkan");
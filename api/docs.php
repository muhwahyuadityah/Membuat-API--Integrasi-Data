<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steam API — Documentation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0a0e1a;
            color: #c7d5e0;
            line-height: 1.6;
        }

        /* ── NAVBAR ── */
        nav {
            background: #0d1117;
            border-bottom: 1px solid #1e3a5f;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 700;
            color: #66c0f4;
            text-decoration: none;
        }

        .nav-brand span { color: #ffffff; }

        .nav-links { display: flex; gap: 24px; }

        .nav-links a {
            color: #8b9dc3;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .nav-links a:hover { color: #66c0f4; }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, #0d1117 0%, #0a1628 50%, #0d1117 100%);
            border-bottom: 1px solid #1e3a5f;
            padding: 60px 40px;
            text-align: center;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(102, 192, 244, 0.1);
            border: 1px solid rgba(102, 192, 244, 0.3);
            color: #66c0f4;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .hero h1 {
            font-size: 42px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .hero h1 span { color: #66c0f4; }

        .hero p {
            color: #8b9dc3;
            font-size: 16px;
            max-width: 600px;
            margin: 0 auto 30px;
        }

        .base-url-box {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #0d1117;
            border: 1px solid #1e3a5f;
            border-radius: 8px;
            padding: 12px 20px;
            font-family: monospace;
            font-size: 15px;
        }

        .base-url-box .label {
            color: #8b9dc3;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .base-url-box .url { color: #66c0f4; }

        /* ── STATS ── */
        .stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 30px 40px;
            background: #0d1117;
            border-bottom: 1px solid #1e3a5f;
            flex-wrap: wrap;
        }

        .stat-card {
            background: #0a0e1a;
            border: 1px solid #1e3a5f;
            border-radius: 8px;
            padding: 16px 28px;
            text-align: center;
            min-width: 140px;
        }

        .stat-card .num {
            font-size: 28px;
            font-weight: 700;
            color: #66c0f4;
        }

        .stat-card .desc {
            font-size: 12px;
            color: #8b9dc3;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── LAYOUT ── */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .section-title {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #1e3a5f;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 22px;
            background: #66c0f4;
            border-radius: 2px;
        }

        /* ── ENDPOINT CARDS ── */
        .endpoint-group { margin-bottom: 40px; }

        .endpoint-group h3 {
            font-size: 16px;
            color: #66c0f4;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .endpoint-group h3 .file-badge {
            background: rgba(102, 192, 244, 0.1);
            border: 1px solid rgba(102, 192, 244, 0.2);
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-family: monospace;
        }

        .endpoint-table {
            width: 100%;
            border-collapse: collapse;
            background: #0d1117;
            border: 1px solid #1e3a5f;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .endpoint-table th {
            background: #111827;
            color: #8b9dc3;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 16px;
            text-align: left;
        }

        .endpoint-table td {
            padding: 10px 16px;
            border-top: 1px solid #1a2744;
            font-size: 13px;
            vertical-align: middle;
        }

        .endpoint-table tr:hover td { background: rgba(102, 192, 244, 0.03); }

        .method {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            font-family: monospace;
            min-width: 60px;
            text-align: center;
        }

        .method.get    { background: rgba(34, 197, 94, 0.15);  color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
        .method.post   { background: rgba(59, 130, 246, 0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); }
        .method.put    { background: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); }
        .method.delete { background: rgba(239, 68, 68, 0.15);  color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }

        .url-cell {
            font-family: monospace;
            color: #c7d5e0;
            font-size: 13px;
        }

        .url-cell .param { color: #f59e0b; }

        /* ── CODE BLOCKS ── */
        .code-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 40px;
        }

        @media (max-width: 768px) { .code-grid { grid-template-columns: 1fr; } }

        .code-card {
            background: #0d1117;
            border: 1px solid #1e3a5f;
            border-radius: 8px;
            overflow: hidden;
        }

        .code-card-header {
            background: #111827;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #1e3a5f;
        }

        .code-card-header .title {
            font-size: 12px;
            color: #8b9dc3;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .code-card-header .lang {
            font-size: 11px;
            color: #66c0f4;
            font-family: monospace;
        }

        .code-card pre {
            padding: 16px;
            font-family: monospace;
            font-size: 13px;
            line-height: 1.7;
            overflow-x: auto;
            color: #c7d5e0;
        }

        .code-card pre .key    { color: #66c0f4; }
        .code-card pre .val    { color: #a3e635; }
        .code-card pre .str    { color: #f97316; }
        .code-card pre .num    { color: #c084fc; }
        .code-card pre .method { color: #f59e0b; background: none; border: none; padding: 0; min-width: auto; font-size: 13px; }

        /* ── ACTION TAGS ── */
        .actions-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 40px;
        }

        .action-tag {
            background: #0d1117;
            border: 1px solid #1e3a5f;
            border-radius: 6px;
            padding: 6px 14px;
            font-family: monospace;
            font-size: 12px;
            color: #66c0f4;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-tag .endpoint-label {
            color: #8b9dc3;
            font-size: 11px;
        }

        /* ── FOOTER ── */
        footer {
            background: #0d1117;
            border-top: 1px solid #1e3a5f;
            padding: 24px 40px;
            text-align: center;
            color: #8b9dc3;
            font-size: 13px;
        }

        footer span { color: #66c0f4; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <a class="nav-brand" href="<?= BASE_URL ?>docs.php">
        🎮 <span>Steam</span>API
    </a>
    <div class="nav-links">
        <a href="<?= BASE_URL ?>docs.php">Docs</a>
        <a href="<?= BASE_URL ?>index.php">API Explorer</a>
        <a href="<?= BASE_URL ?>games.php">Games</a>
        <a href="<?= BASE_URL ?>users.php">Users</a>
    </div>
</nav>

<!-- HERO -->
<div class="hero">
    <div class="hero-badge">REST API Documentation</div>
    <h1>Steam <span>API</span></h1>
    <p>Platform penjualan game PC berbasis REST API menggunakan PHP Native dan MySQL.</p>
    <div class="base-url-box">
        <span class="label">Base URL</span>
        <span class="url"><?= BASE_URL ?></span>
    </div>
</div>

<!-- STATS -->
<?php
$totalGames     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM games"))['t'];
$totalUsers     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users"))['t'];
$totalPurchases = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM purchases"))['t'];
$totalReviews   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM reviews"))['t'];
$totalGenres    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM genres"))['t'];
?>
<div class="stats">
    <div class="stat-card">
        <div class="num"><?= $totalGames ?></div>
        <div class="desc">Games</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $totalUsers ?></div>
        <div class="desc">Users</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $totalPurchases ?></div>
        <div class="desc">Purchases</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $totalReviews ?></div>
        <div class="desc">Reviews</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= $totalGenres ?></div>
        <div class="desc">Genres</div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="container">

    <!-- ENDPOINTS -->
    <div class="section-title">Endpoints</div>

    <?php
    $endpoints = [
        'users.php' => [
            ['GET',    '/users.php',                              'Ambil semua user'],
            ['GET',    '/users.php?id={id}',                     'Ambil user by ID'],
            ['GET',    '/users.php?action=countUsers',            'Total jumlah user'],
            ['GET',    '/users.php?action=userWithMostPurchases', 'User dengan pembelian terbanyak'],
            ['GET',    '/users.php?action=userWithMostReviews',   'User dengan review terbanyak'],
            ['POST',   '/users.php',                              'Tambah user baru'],
            ['PUT',    '/users.php',                              'Update data user'],
            ['DELETE', '/users.php',                              'Hapus user'],
        ],
        'genres.php' => [
            ['GET',    '/genres.php',                                'Ambil semua genre'],
            ['GET',    '/genres.php?id={id}',                        'Ambil genre by ID'],
            ['GET',    '/genres.php?action=countGenres',              'Total jumlah genre'],
            ['GET',    '/genres.php?action=genreWithMostGames',       'Genre dengan game terbanyak'],
            ['GET',    '/genres.php?action=genreWithMostPurchases',   'Genre dengan pembelian terbanyak'],
            ['GET',    '/genres.php?action=genreWithHighestRating',   'Genre dengan rating tertinggi'],
            ['POST',   '/genres.php',                                'Tambah genre baru'],
            ['PUT',    '/genres.php',                                'Update genre'],
            ['DELETE', '/genres.php',                                'Hapus genre'],
        ],
        'games.php' => [
            ['GET',    '/games.php',                                 'Ambil semua game'],
            ['GET',    '/games.php?id={id}',                         'Ambil game by ID'],
            ['GET',    '/games.php?action=countGames',               'Total jumlah game'],
            ['GET',    '/games.php?action=topSalesGames',            'Top 10 game terlaris'],
            ['GET',    '/games.php?action=topRatedGames',            'Top 10 game rating tertinggi'],
            ['GET',    '/games.php?action=latestGames',              'Top 10 game terbaru'],
            ['GET',    '/games.php?action=mostReviewedGames',        'Top 10 game paling banyak direview'],
            ['GET',    '/games.php?action=cheapestGames',            'Top 10 game termurah'],
            ['GET',    '/games.php?action=mostExpensiveGames',       'Top 10 game termahal'],
            ['GET',    '/games.php?action=gameStats',                'Statistik keseluruhan game'],
            ['GET',    '/games.php?action=gamesByGenre&id={id}',     'Game berdasarkan genre'],
            ['POST',   '/games.php',                                 'Tambah game baru'],
            ['PUT',    '/games.php',                                 'Update data game'],
            ['DELETE', '/games.php',                                 'Hapus game'],
        ],
        'purchases.php' => [
            ['GET',    '/purchases.php',                                  'Semua data pembelian'],
            ['GET',    '/purchases.php?id={id}',                          'Pembelian by ID'],
            ['GET',    '/purchases.php?action=countPurchases',            'Total transaksi'],
            ['GET',    '/purchases.php?action=totalRevenue',              'Total revenue platform'],
            ['GET',    '/purchases.php?action=revenueByGenre',            'Revenue berdasarkan genre'],
            ['GET',    '/purchases.php?action=recentPurchases',           'Transaksi terbaru'],
            ['GET',    '/purchases.php?action=topSpenders',               'Top 10 user pengeluaran terbesar'],
            ['GET',    '/purchases.php?action=purchasesByUser&id={id}',   'Riwayat beli per user'],
            ['GET',    '/purchases.php?action=purchasesByGame&id={id}',   'Daftar pembeli per game'],
            ['POST',   '/purchases.php',                                  'Tambah pembelian baru'],
            ['PUT',    '/purchases.php',                                  'Update data pembelian'],
            ['DELETE', '/purchases.php',                                  'Hapus pembelian'],
        ],
        'reviews.php' => [
            ['GET',    '/reviews.php',                                  'Semua review'],
            ['GET',    '/reviews.php?id={id}',                          'Review by ID'],
            ['GET',    '/reviews.php?action=countReviews',              'Total review'],
            ['GET',    '/reviews.php?action=topRatedReviews',           'Review bintang 5'],
            ['GET',    '/reviews.php?action=lowestRatedReviews',        'Review bintang 1'],
            ['GET',    '/reviews.php?action=ratingDistribution',        'Distribusi rating'],
            ['GET',    '/reviews.php?action=avgRatingByGenre',          'Rata-rata rating per genre'],
            ['GET',    '/reviews.php?action=recentReviews',             '20 review terbaru'],
            ['GET',    '/reviews.php?action=reviewStats',               'Statistik semua review'],
            ['GET',    '/reviews.php?action=reviewsByGame&id={id}',     'Review berdasarkan game'],
            ['GET',    '/reviews.php?action=reviewsByUser&id={id}',     'Review berdasarkan user'],
            ['POST',   '/reviews.php',                                  'Tambah review baru'],
            ['PUT',    '/reviews.php',                                  'Update review'],
            ['DELETE', '/reviews.php',                                  'Hapus review'],
        ],
    ];

    foreach ($endpoints as $file => $rows) {
        echo "<div class='endpoint-group'>";
        echo "<h3><span class='file-badge'>{$file}</span></h3>";
        echo "<table class='endpoint-table'>";
        echo "<thead><tr><th>Method</th><th>Endpoint</th><th>Deskripsi</th></tr></thead><tbody>";
        foreach ($rows as [$method, $url, $desc]) {
            $methodLower = strtolower($method);
            $urlFormatted = preg_replace('/\{(\w+)\}/', '<span class="param">{$1}</span>', htmlspecialchars($url));
            echo "<tr>
                <td><span class='method {$methodLower}'>{$method}</span></td>
                <td class='url-cell'>{$urlFormatted}</td>
                <td>{$desc}</td>
            </tr>";
        }
        echo "</tbody></table></div>";
    }
    ?>

    <!-- CONTOH REQUEST & RESPONSE -->
    <div class="section-title">Contoh Request & Response</div>

    <div class="code-grid">
        <div class="code-card">
            <div class="code-card-header">
                <span class="title">POST — Tambah User</span>
                <span class="lang">JSON Body</span>
            </div>
            <pre>{
  <span class="key">"username"</span>: <span class="str">"john_doe"</span>,
  <span class="key">"email"</span>:    <span class="str">"john@email.com"</span>
}</pre>
        </div>
        <div class="code-card">
            <div class="code-card-header">
                <span class="title">Response — Berhasil</span>
                <span class="lang">JSON</span>
            </div>
            <pre>{
  <span class="key">"status"</span>: {
    <span class="key">"code"</span>:        <span class="num">201</span>,
    <span class="key">"description"</span>: <span class="str">"User berhasil ditambahkan"</span>
  },
  <span class="key">"result"</span>: {
    <span class="key">"id"</span>:       <span class="num">21</span>,
    <span class="key">"username"</span>: <span class="str">"john_doe"</span>,
    <span class="key">"email"</span>:    <span class="str">"john@email.com"</span>
  }
}</pre>
        </div>
        <div class="code-card">
            <div class="code-card-header">
                <span class="title">GET — Top Sales Games</span>
                <span class="lang">URL</span>
            </div>
            <pre><span class="method">GET</span> /games.php?action=topSalesGames</pre>
        </div>
        <div class="code-card">
            <div class="code-card-header">
                <span class="title">Response — Error</span>
                <span class="lang">JSON</span>
            </div>
            <pre>{
  <span class="key">"status"</span>: {
    <span class="key">"code"</span>:        <span class="num">404</span>,
    <span class="key">"description"</span>: <span class="str">"Data tidak ditemukan"</span>
  }
}</pre>
        </div>
        <div class="code-card">
            <div class="code-card-header">
                <span class="title">POST — Tambah Review</span>
                <span class="lang">JSON Body</span>
            </div>
            <pre>{
  <span class="key">"user_id"</span>: <span class="num">1</span>,
  <span class="key">"game_id"</span>: <span class="num">3</span>,
  <span class="key">"rating"</span>:  <span class="num">5</span>,
  <span class="key">"comment"</span>: <span class="str">"Game terbaik!"</span>
}</pre>
        </div>
        <div class="code-card">
            <div class="code-card-header">
                <span class="title">DELETE — Hapus Game</span>
                <span class="lang">JSON Body</span>
            </div>
            <pre>{
  <span class="key">"id"</span>: <span class="num">5</span>
}</pre>
        </div>
    </div>

    <!-- SEMUA ACTION -->
    <div class="section-title">Daftar Semua Action</div>
    <div class="actions-grid">
        <?php
        $actions = [
            'users.php'     => ['countUsers','userWithMostPurchases','userWithMostReviews'],
            'genres.php'    => ['countGenres','genreWithMostGames','genreWithMostPurchases','genreWithHighestRating'],
            'games.php'     => ['countGames','topSalesGames','topRatedGames','latestGames','mostReviewedGames','cheapestGames','mostExpensiveGames','gamesByGenre','gameStats'],
            'purchases.php' => ['countPurchases','totalRevenue','revenueByGenre','recentPurchases','topSpenders','purchasesByUser','purchasesByGame'],
            'reviews.php'   => ['countReviews','reviewsByGame','reviewsByUser','topRatedReviews','lowestRatedReviews','ratingDistribution','avgRatingByGenre','recentReviews','reviewStats'],
        ];
        foreach ($actions as $file => $acts) {
            foreach ($acts as $act) {
                echo "<div class='action-tag'>
                    <span class='endpoint-label'>{$file}</span>
                    ?action={$act}
                </div>";
            }
        }
        ?>
    </div>

</div>

<!-- FOOTER -->
<footer>
    <p>🎮 <span>Steam API</span> — Dibangun dengan PHP Native + MySQL &nbsp;|&nbsp; <?= date('Y') ?></p>
</footer>

</body>
</html>
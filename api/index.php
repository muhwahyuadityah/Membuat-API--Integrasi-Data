<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Steam API — Explorer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0a0e1a;
            color: #c7d5e0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── NAVBAR ── */
        nav {
            background: #0d1117;
            border-bottom: 1px solid #1e3a5f;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            flex-shrink: 0;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 700;
            color: #66c0f4;
            text-decoration: none;
        }

        .nav-brand span { color: #ffffff; }

        .nav-links { display: flex; gap: 20px; align-items: center; }

        .nav-links a {
            color: #8b9dc3;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }

        .nav-links a:hover { color: #66c0f4; }

        .nav-badge {
            background: rgba(102,192,244,0.15);
            border: 1px solid rgba(102,192,244,0.3);
            color: #66c0f4;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
        }

        /* ── MAIN LAYOUT ── */
        .main {
            display: grid;
            grid-template-columns: 300px 1fr;
            flex: 1;
            overflow: hidden;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            background: #0d1117;
            border-right: 1px solid #1e3a5f;
            overflow-y: auto;
            padding: 16px;
        }

        .sidebar-section { margin-bottom: 20px; }

        .sidebar-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #4a6080;
            margin-bottom: 8px;
            padding: 0 4px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            color: #8b9dc3;
            transition: all 0.15s;
            border: 1px solid transparent;
        }

        .sidebar-item:hover {
            background: rgba(102,192,244,0.05);
            color: #c7d5e0;
            border-color: #1e3a5f;
        }

        .sidebar-item.active {
            background: rgba(102,192,244,0.1);
            color: #66c0f4;
            border-color: rgba(102,192,244,0.3);
        }

        .sidebar-item .method-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .dot-get    { background: #22c55e; }
        .dot-post   { background: #3b82f6; }
        .dot-put    { background: #f59e0b; }
        .dot-delete { background: #ef4444; }

        .sidebar-item .action-name {
            font-family: monospace;
            font-size: 12px;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ── RIGHT PANEL ── */
        .panel {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ── REQUEST BAR ── */
        .request-bar {
            background: #0d1117;
            border-bottom: 1px solid #1e3a5f;
            padding: 12px 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-shrink: 0;
        }

        .method-select {
            background: #111827;
            border: 1px solid #1e3a5f;
            color: #f59e0b;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            font-family: monospace;
            cursor: pointer;
            outline: none;
        }

        .url-input {
            flex: 1;
            background: #111827;
            border: 1px solid #1e3a5f;
            color: #c7d5e0;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-family: monospace;
            outline: none;
            transition: border-color 0.2s;
        }

        .url-input:focus { border-color: #66c0f4; }

        .send-btn {
            background: #66c0f4;
            color: #0a0e1a;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .send-btn:hover { background: #4fa8d8; }
        .send-btn:disabled { background: #2a4a6a; color: #4a6080; cursor: not-allowed; }

        /* ── TABS ── */
        .tabs {
            background: #0d1117;
            border-bottom: 1px solid #1e3a5f;
            display: flex;
            padding: 0 20px;
            flex-shrink: 0;
        }

        .tab {
            padding: 10px 16px;
            font-size: 12px;
            color: #8b9dc3;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tab:hover { color: #c7d5e0; }
        .tab.active { color: #66c0f4; border-bottom-color: #66c0f4; }

        /* ── CONTENT AREA ── */
        .content-area {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
        }

        .pane {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid #1e3a5f;
        }

        .pane:last-child { border-right: none; }

        .pane-header {
            background: #111827;
            padding: 8px 16px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #4a6080;
            border-bottom: 1px solid #1e3a5f;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .pane-header .status-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-2xx { background: rgba(34,197,94,0.15);  color: #22c55e; }
        .status-4xx { background: rgba(239,68,68,0.15);  color: #ef4444; }
        .status-5xx { background: rgba(245,158,11,0.15); color: #f59e0b; }

        textarea.body-input {
            flex: 1;
            background: #0a0e1a;
            border: none;
            color: #c7d5e0;
            font-family: monospace;
            font-size: 13px;
            padding: 16px;
            resize: none;
            outline: none;
            line-height: 1.6;
        }

       .response-output {
            flex: 1;
            overflow-y: auto;
            overflow-x: auto;
            padding: 16px;
            font-family: monospace;
            font-size: 13px;
            line-height: 1.7;
            background: #0a0e1a;
        }

        .response-placeholder {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #2a4a6a;
            gap: 8px;
        }

        .response-placeholder .icon { font-size: 36px; }
        .response-placeholder p { font-size: 13px; }

        /* ── QUICK ACTIONS ── */
        .quick-actions {
            background: #0d1117;
            border-top: 1px solid #1e3a5f;
            padding: 10px 20px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .quick-btn {
            background: #111827;
            border: 1px solid #1e3a5f;
            color: #8b9dc3;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-family: monospace;
            cursor: pointer;
            transition: all 0.15s;
        }

        .quick-btn:hover {
            background: rgba(102,192,244,0.08);
            border-color: rgba(102,192,244,0.3);
            color: #66c0f4;
        }

        /* ── JSON HIGHLIGHT ── */
        .json-key    { color: #66c0f4; }
        .json-string { color: #f97316; }
        .json-number { color: #c084fc; }
        .json-bool   { color: #22c55e; }
        .json-null   { color: #ef4444; }

        /* ── LOADING ── */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 8px;
            color: #66c0f4;
            font-size: 13px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #1e3a5f;
            border-top-color: #66c0f4;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0a0e1a; }
        ::-webkit-scrollbar-thumb { background: #1e3a5f; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #2a5580; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <a class="nav-brand" href="<?= BASE_URL ?>index.php">
        🎮 <span>Steam</span>API
    </a>
    <div class="nav-links">
        <span class="nav-badge">API Explorer</span>
        <a href="<?= BASE_URL ?>docs.php">Documentation</a>
        <a href="<?= BASE_URL ?>games.php" target="_blank">Games</a>
        <a href="<?= BASE_URL ?>users.php" target="_blank">Users</a>
    </div>
</nav>

<!-- MAIN -->
<div class="main">

    <!-- SIDEBAR -->
    <div class="sidebar">

        <?php
        $sidebarItems = [
            'USERS' => [
                ['GET',    'users.php',                              'GET All Users'],
                ['GET',    'users.php?action=countUsers',            'Count Users'],
                ['GET',    'users.php?action=userWithMostPurchases', 'Most Purchases'],
                ['GET',    'users.php?action=userWithMostReviews',   'Most Reviews'],
                ['POST',   'users.php',                              'POST User',    '{"username":"john","email":"john@mail.com"}'],
                ['PUT',    'users.php',                              'PUT User',     '{"id":1,"username":"john_new","email":"john2@mail.com"}'],
                ['DELETE', 'users.php',                              'DELETE User',  '{"id":1}'],
            ],
            'GENRES' => [
                ['GET',    'genres.php',                               'GET All Genres'],
                ['GET',    'genres.php?action=countGenres',             'Count Genres'],
                ['GET',    'genres.php?action=genreWithMostGames',      'Most Games'],
                ['GET',    'genres.php?action=genreWithMostPurchases',  'Most Purchases'],
                ['GET',    'genres.php?action=genreWithHighestRating',  'Highest Rating'],
                ['POST',   'genres.php',                               'POST Genre',   '{"name":"Indie"}'],
                ['PUT',    'genres.php',                               'PUT Genre',    '{"id":1,"name":"Action RPG"}'],
                ['DELETE', 'genres.php',                               'DELETE Genre', '{"id":1}'],
            ],
            'GAMES' => [
                ['GET',    'games.php',                               'GET All Games'],
                ['GET',    'games.php?action=countGames',             'Count Games'],
                ['GET',    'games.php?action=topSalesGames',          'Top Sales'],
                ['GET',    'games.php?action=topRatedGames',          'Top Rated'],
                ['GET',    'games.php?action=latestGames',            'Latest Games'],
                ['GET',    'games.php?action=mostReviewedGames',      'Most Reviewed'],
                ['GET',    'games.php?action=cheapestGames',          'Cheapest'],
                ['GET',    'games.php?action=mostExpensiveGames',     'Most Expensive'],
                ['GET',    'games.php?action=gameStats',              'Game Stats'],
                ['GET',    'games.php?action=gamesByGenre&id=1',      'By Genre'],
                ['POST',   'games.php',                               'POST Game',    '{"title":"New Game","genre_id":1,"price":29.99,"release_date":"2024-01-01"}'],
                ['PUT',    'games.php',                               'PUT Game',     '{"id":1,"title":"Updated","genre_id":2,"price":39.99,"release_date":"2024-06-01"}'],
                ['DELETE', 'games.php',                               'DELETE Game',  '{"id":1}'],
            ],
            'PURCHASES' => [
                ['GET',    'purchases.php',                                'GET All Purchases'],
                ['GET',    'purchases.php?action=countPurchases',          'Count Purchases'],
                ['GET',    'purchases.php?action=totalRevenue',            'Total Revenue'],
                ['GET',    'purchases.php?action=revenueByGenre',          'Revenue by Genre'],
                ['GET',    'purchases.php?action=recentPurchases',         'Recent Purchases'],
                ['GET',    'purchases.php?action=topSpenders',             'Top Spenders'],
                ['GET',    'purchases.php?action=purchasesByUser&id=1',    'By User'],
                ['GET',    'purchases.php?action=purchasesByGame&id=1',    'By Game'],
                ['POST',   'purchases.php',                                'POST Purchase', '{"user_id":1,"game_id":5}'],
                ['PUT',    'purchases.php',                                'PUT Purchase',  '{"id":1,"user_id":1,"game_id":6}'],
                ['DELETE', 'purchases.php',                                'DELETE Purchase','{"id":1}'],
            ],
            'REVIEWS' => [
                ['GET',    'reviews.php',                               'GET All Reviews'],
                ['GET',    'reviews.php?action=countReviews',           'Count Reviews'],
                ['GET',    'reviews.php?action=topRatedReviews',        'Top Rated'],
                ['GET',    'reviews.php?action=lowestRatedReviews',     'Lowest Rated'],
                ['GET',    'reviews.php?action=ratingDistribution',     'Distribution'],
                ['GET',    'reviews.php?action=avgRatingByGenre',       'Avg by Genre'],
                ['GET',    'reviews.php?action=recentReviews',          'Recent Reviews'],
                ['GET',    'reviews.php?action=reviewStats',            'Review Stats'],
                ['GET',    'reviews.php?action=reviewsByGame&id=1',     'By Game'],
                ['GET',    'reviews.php?action=reviewsByUser&id=1',     'By User'],
                ['POST',   'reviews.php',                               'POST Review',  '{"user_id":1,"game_id":2,"rating":5,"comment":"Mantap!"}'],
                ['PUT',    'reviews.php',                               'PUT Review',   '{"id":1,"rating":4,"comment":"Update review"}'],
                ['DELETE', 'reviews.php',                               'DELETE Review','{"id":1}'],
            ],
        ];

        foreach ($sidebarItems as $section => $items) {
            echo "<div class='sidebar-section'>";
            echo "<div class='sidebar-label'>{$section}</div>";
            foreach ($items as $item) {
                $method = $item[0];
                $url    = $item[1];
                $label  = $item[2];
                $body   = isset($item[3]) ? htmlspecialchars($item[3]) : '';
                $methodLower = strtolower($method);
                echo "<div class='sidebar-item' 
                    data-method='{$method}' 
                    data-url='{$url}' 
                    data-body='{$body}'
                    onclick='loadRequest(this)'>
                    <div class='method-dot dot-{$methodLower}'></div>
                    <span class='action-name'>{$label}</span>
                </div>";
            }
            echo "</div>";
        }
        ?>

    </div>

    <!-- RIGHT PANEL -->
    <div class="panel">

        <!-- REQUEST BAR -->
        <div class="request-bar">
            <select class="method-select" id="methodSelect" onchange="updateMethodColor()">
                <option value="GET">GET</option>
                <option value="POST">POST</option>
                <option value="PUT">PUT</option>
                <option value="DELETE">DELETE</option>
            </select>
            <input type="text"
                   class="url-input"
                   id="urlInput"
                   value="<?= BASE_URL ?>games.php?action=topSalesGames"
                   placeholder="<?= BASE_URL ?>endpoint.php">
            <button class="send-btn" id="sendBtn" onclick="sendRequest()">▶ Send</button>
        </div>

        <!-- TABS -->
        <div class="tabs">
            <div class="tab active" onclick="switchTab('body')">Request Body</div>
            <div class="tab" onclick="switchTab('headers')">Headers</div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">

            <!-- LEFT PANE: REQUEST -->
            <div class="pane">
                <div class="pane-header">
                    <span id="paneLeftLabel">Request Body</span>
                </div>

                <!-- BODY TAB -->
                <div id="bodyTab">
                    <textarea class="body-input" id="bodyInput" placeholder='// Body JSON untuk POST / PUT / DELETE
// Kosongkan untuk GET

{
  "username": "john",
  "email": "john@mail.com"
}'></textarea>
                </div>

                <!-- HEADERS TAB -->
                <div id="headersTab" style="display:none; padding:16px; font-family:monospace; font-size:13px; line-height:1.8;">
                    <div style="color:#4a6080; margin-bottom:12px; font-size:11px; text-transform:uppercase; letter-spacing:1px;">Default Headers</div>
                    <div><span style="color:#66c0f4;">Content-Type</span>: <span style="color:#f97316;">application/json</span></div>
                    <div><span style="color:#66c0f4;">Accept</span>: <span style="color:#f97316;">application/json</span></div>
                    <div style="margin-top:16px; color:#4a6080; font-size:11px; text-transform:uppercase; letter-spacing:1px;">CORS Headers</div>
                    <div><span style="color:#66c0f4;">Access-Control-Allow-Origin</span>: <span style="color:#f97316;">*</span></div>
                </div>
            </div>

            <!-- RIGHT PANE: RESPONSE -->
            <div class="pane">
                <div class="pane-header">
                    <span>Response</span>
                    <span id="statusBadge"></span>
                </div>
                <div class="response-output" id="responseOutput">
                    <div class="response-placeholder">
                        <div class="icon">🎮</div>
                        <p>Pilih endpoint dari sidebar atau klik Send</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="quick-actions">
            <span style="font-size:11px; color:#4a6080; text-transform:uppercase; letter-spacing:1px; margin-right:4px;">Quick:</span>
            <?php
            $quickActions = [
                'Top Sales'     => 'games.php?action=topSalesGames',
                'Top Rated'     => 'games.php?action=topRatedGames',
                'Latest Games'  => 'games.php?action=latestGames',
                'Revenue'       => 'purchases.php?action=totalRevenue',
                'Top Spenders'  => 'purchases.php?action=topSpenders',
                'Review Stats'  => 'reviews.php?action=reviewStats',
                'Game Stats'    => 'games.php?action=gameStats',
                'Distribution'  => 'reviews.php?action=ratingDistribution',
            ];
            foreach ($quickActions as $label => $url) {
                echo "<button class='quick-btn' onclick=\"quickLoad('{$url}')\">{$label}</button>";
            }
            ?>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '<?= BASE_URL ?>';

    // ── LOAD REQUEST FROM SIDEBAR ──
    function loadRequest(el) {
        document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
        el.classList.add('active');

        const method = el.dataset.method;
        const url    = el.dataset.url;
        const body   = el.dataset.body;

        document.getElementById('methodSelect').value = method;
        document.getElementById('urlInput').value     = BASE_URL + url;

        if (body) {
            try {
                document.getElementById('bodyInput').value = JSON.stringify(JSON.parse(body), null, 2);
            } catch(e) {
                document.getElementById('bodyInput').value = body;
            }
        } else {
            document.getElementById('bodyInput').value = '';
        }

        updateMethodColor();
    }

    // ── QUICK LOAD ──
    function quickLoad(url) {
        document.getElementById('methodSelect').value = 'GET';
        document.getElementById('urlInput').value     = BASE_URL + url;
        document.getElementById('bodyInput').value    = '';
        updateMethodColor();
        sendRequest();
    }

    // ── METHOD COLOR ──
    function updateMethodColor() {
        const method = document.getElementById('methodSelect').value;
        const colors = { GET:'#22c55e', POST:'#3b82f6', PUT:'#f59e0b', DELETE:'#ef4444' };
        document.getElementById('methodSelect').style.color = colors[method] || '#c7d5e0';
    }

    // ── SWITCH TAB ──
    function switchTab(tab) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById('bodyTab').style.display    = tab === 'body'    ? 'flex' : 'none';
        document.getElementById('headersTab').style.display = tab === 'headers' ? 'block' : 'none';
        document.getElementById('paneLeftLabel').textContent = tab === 'body' ? 'Request Body' : 'Headers';
        if (tab === 'body') {
            document.getElementById('bodyTab').style.flexDirection = 'column';
            document.getElementById('bodyTab').style.height = '100%';
        }
    }

    // ── SEND REQUEST ──
    async function sendRequest() {
        const method  = document.getElementById('methodSelect').value;
        const url     = document.getElementById('urlInput').value;
        const bodyRaw = document.getElementById('bodyInput').value.trim();
        const output  = document.getElementById('responseOutput');
        const btn     = document.getElementById('sendBtn');
        const badge   = document.getElementById('statusBadge');

        btn.disabled = true;
        btn.textContent = '⏳ Sending...';
        badge.textContent = '';
        badge.className = 'status-badge';

        output.innerHTML = `<div class="loading"><div class="spinner"></div> Mengirim request...</div>`;

        const options = {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }
        };

        if (['POST','PUT','DELETE'].includes(method) && bodyRaw) {
            try {
                JSON.parse(bodyRaw);
                options.body = bodyRaw;
            } catch(e) {
                output.innerHTML = `<div style="color:#ef4444; padding:16px; font-family:monospace;">
                    ❌ JSON tidak valid!<br><br>${e.message}
                </div>`;
                btn.disabled = false;
                btn.textContent = '▶ Send';
                return;
            }
        }

        try {
            const start    = performance.now();
            const response = await fetch(url, options);
            const elapsed  = Math.round(performance.now() - start);
            const data     = await response.json();
            const code     = data?.status?.code || response.status;

            // Status badge
            let badgeClass = 'status-2xx';
            if (code >= 400 && code < 500) badgeClass = 'status-4xx';
            if (code >= 500) badgeClass = 'status-5xx';
            badge.textContent = `${code} · ${elapsed}ms`;
            badge.className   = `status-badge ${badgeClass}`;

            output.innerHTML = `<pre style="margin:0; white-space:pre; word-break:normal;">${syntaxHighlight(JSON.stringify(data, null, 2))}</pre>`;

        } catch(err) {
            badge.textContent = 'Error';
            badge.className   = 'status-badge status-4xx';
            output.innerHTML  = `<div style="color:#ef4444; font-family:monospace; padding:0;">
                ❌ Request gagal!<br><br>${err.message}
            </div>`;
        }

        btn.disabled    = false;
        btn.textContent = '▶ Send';
    }

    // ── JSON SYNTAX HIGHLIGHT ──
        function syntaxHighlight(json) {
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(
                /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
                match => {
                    let cls = 'json-number';
                    if (/^"/.test(match)) {
                        cls = /:$/.test(match) ? 'json-key' : 'json-string';
                    } else if (/true|false/.test(match)) {
                        cls = 'json-bool';
                    } else if (/null/.test(match)) {
                        cls = 'json-null';
                    }
                    return `<span class="${cls}">${match}</span>`;
                }
            );
        }

    // ── INIT ──
    updateMethodColor();

    // Bodyinput flex fix
    document.getElementById('bodyTab').style.display       = 'flex';
    document.getElementById('bodyTab').style.flexDirection = 'column';
    document.getElementById('bodyTab').style.height        = '100%';
    document.querySelector('.body-input').style.flex       = '1';
</script>

</body>
</html>

<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Team.php';
require_once 'classes/Player.php';

$db        = Database::getInstance()->getConn();
$teamObj   = new Team($db);
$playerObj = new Player($db);

$query   = trim($_GET['q'] ?? '');
$filter  = $_GET['filter'] ?? 'all'; // all | teams | players
$teams   = [];
$players = [];

if (strlen($query) >= 2) {
    if ($filter !== 'players') $teams   = $teamObj->search($query);
    if ($filter !== 'teams')   $players = $playerObj->search($query);
}
$totalResults = count($teams) + count($players);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-container">
    <div class="page-header">
        <h1>Search</h1>
        <p>Find teams and players across all 6 leagues</p>
    </div>

    <form method="GET" action="" class="search-bar">
        <input type="text" name="q" class="search-input"
               placeholder="Search teams or players…"
               value="<?= htmlspecialchars($query) ?>"
               autofocus>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- Filter tabs -->
    <?php if ($query !== ''): ?>
    <div style="display:flex;gap:8px;margin-bottom:20px;">
        <a href="?q=<?= urlencode($query) ?>&filter=all"
           class="league-tab <?= $filter === 'all' ? 'active' : '' ?>">All Results</a>
        <a href="?q=<?= urlencode($query) ?>&filter=teams"
           class="league-tab <?= $filter === 'teams' ? 'active' : '' ?>">Teams</a>
        <a href="?q=<?= urlencode($query) ?>&filter=players"
           class="league-tab <?= $filter === 'players' ? 'active' : '' ?>">Players</a>
    </div>
    <?php endif; ?>

    <?php if (strlen($query) >= 2 && $totalResults === 0): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <p>No results found for "<strong><?= htmlspecialchars($query) ?></strong>".</p>
            <p style="font-size:13px;margin-top:4px;">Try a different spelling or search term.</p>
        </div>
    </div>

    <?php elseif (strlen($query) < 2 && $query !== ''): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">✏️</div>
            <p>Please enter at least 2 characters.</p>
        </div>
    </div>

    <?php elseif ($query === ''): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">⚽</div>
            <p>Search for any team or player in the database.</p>
        </div>
    </div>

    <?php else: ?>

    <?php if (!empty($teams)): ?>
    <div class="section-label">Teams (<?= count($teams) ?>)</div>
    <div class="search-results-grid" style="margin-top:12px;margin-bottom:24px;">
        <?php foreach ($teams as $t): ?>
        <a href="<?= BASE_URL ?>/team.php?id=<?= $t['id'] ?>" class="result-card">
            <div class="icon"><?= htmlspecialchars($t['short_name']) ?></div>
            <div class="info">
                <div class="name"><?= htmlspecialchars($t['name']) ?></div>
                <div class="sub"><?= htmlspecialchars($t['league_name']) ?></div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($players)): ?>
    <div class="section-label">Players (<?= count($players) ?>)</div>
    <div class="search-results-grid" style="margin-top:12px;">
        <?php foreach ($players as $p): ?>
        <a href="<?= BASE_URL ?>/player.php?id=<?= $p['id'] ?>" class="result-card">
            <div class="icon">
                <span class="pos-badge pos-<?= $p['position'] ?>"><?= $p['position'] ?></span>
            </div>
            <div class="info">
                <div class="name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="sub">
                    <?= htmlspecialchars($p['team_name']) ?> ·
                    <?= htmlspecialchars($p['nationality'] ?? '') ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

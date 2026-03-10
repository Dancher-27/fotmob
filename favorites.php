<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Favorite.php';
require_once 'classes/Match.php';

if (!User::isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

$db     = Database::getInstance()->getConn();
$favObj = new Favorite($db);
$matchObj = new FootballMatch($db);

$userId  = (int)$_SESSION['user_id'];

// Handle remove action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_type'], $_POST['remove_id'])) {
    $favObj->remove($userId, $_POST['remove_type'], (int)$_POST['remove_id']);
    header('Location: favorites.php');
    exit();
}

$favTeams   = $favObj->getFavoriteTeams($userId);
$favPlayers = $favObj->getFavoritePlayers($userId);

// Upcoming matches for favorited teams
$upcomingFav = [];
foreach ($favTeams as $t) {
    $upcoming = $matchObj->getUpcomingByTeam((int)$t['id'], 1);
    if (!empty($upcoming)) {
        $upcomingFav[] = $upcoming[0];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorites — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-container">
    <div class="page-header">
        <h1>My Favorites</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    </div>

    <!-- Favorite teams -->
    <div class="section-label">Favorite Teams (<?= count($favTeams) ?>)</div>
    <?php if (empty($favTeams)): ?>
    <div class="card" style="margin-top:12px;margin-bottom:24px;">
        <div class="empty-state">
            <div class="empty-icon">🏟️</div>
            <p>No favorite teams yet.</p>
            <a href="<?= BASE_URL ?>/standings.php" class="btn btn-secondary">Browse Teams</a>
        </div>
    </div>
    <?php else: ?>
    <div class="fav-grid" style="margin-top:12px;margin-bottom:24px;">
        <?php foreach ($favTeams as $t): ?>
        <div class="fav-card">
            <a href="<?= BASE_URL ?>/team.php?id=<?= $t['id'] ?>">
                <div class="fav-icon"><?= htmlspecialchars($t['short_name']) ?></div>
            </a>
            <div class="fav-info">
                <div class="fav-name">
                    <a href="<?= BASE_URL ?>/team.php?id=<?= $t['id'] ?>"
                       style="color:var(--text);"><?= htmlspecialchars($t['name']) ?></a>
                </div>
                <div class="fav-sub"><?= htmlspecialchars($t['league_name']) ?></div>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="remove_type" value="team">
                <input type="hidden" name="remove_id"   value="<?= $t['id'] ?>">
                <button type="submit" class="btn-fav" title="Remove favorite"
                        onclick="return confirm('Remove from favorites?')">✕</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Upcoming for fav teams -->
    <?php if (!empty($upcomingFav)): ?>
    <div class="section-label">Next Matches for Your Teams</div>
    <div class="matches-list" style="margin-top:12px;margin-bottom:24px;">
        <?php foreach ($upcomingFav as $m): ?>
        <a href="<?= BASE_URL ?>/match.php?id=<?= $m['id'] ?>" class="match-row">
            <div class="match-time">
                <?= date('d M', strtotime($m['match_date'])) ?>
                <div style="font-size:11px;color:var(--text-dim);"><?= date('H:i', strtotime($m['match_date'])) ?></div>
            </div>
            <div class="match-team home">
                <span><?= htmlspecialchars($m['home_name']) ?></span>
                <div class="team-badge"><?= htmlspecialchars($m['home_short']) ?></div>
            </div>
            <div class="match-score scheduled"><?= date('H:i', strtotime($m['match_date'])) ?></div>
            <div class="match-team away">
                <div class="team-badge"><?= htmlspecialchars($m['away_short']) ?></div>
                <span><?= htmlspecialchars($m['away_name']) ?></span>
            </div>
            <div class="match-league"><?= htmlspecialchars($m['league_name']) ?></div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Favorite players -->
    <div class="section-label">Favorite Players (<?= count($favPlayers) ?>)</div>
    <?php if (empty($favPlayers)): ?>
    <div class="card" style="margin-top:12px;">
        <div class="empty-state">
            <div class="empty-icon">⚽</div>
            <p>No favorite players yet.</p>
            <a href="<?= BASE_URL ?>/search.php" class="btn btn-secondary">Find Players</a>
        </div>
    </div>
    <?php else: ?>
    <div class="fav-grid" style="margin-top:12px;">
        <?php foreach ($favPlayers as $p): ?>
        <div class="fav-card">
            <a href="<?= BASE_URL ?>/player.php?id=<?= $p['id'] ?>">
                <div class="fav-icon">
                    <span class="pos-badge pos-<?= $p['position'] ?>"><?= $p['position'] ?></span>
                </div>
            </a>
            <div class="fav-info">
                <div class="fav-name">
                    <a href="<?= BASE_URL ?>/player.php?id=<?= $p['id'] ?>"
                       style="color:var(--text);"><?= htmlspecialchars($p['name']) ?></a>
                </div>
                <div class="fav-sub">
                    <?= htmlspecialchars($p['team_name']) ?> ·
                    <?= htmlspecialchars($p['nationality'] ?? '') ?>
                </div>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="remove_type" value="player">
                <input type="hidden" name="remove_id"   value="<?= $p['id'] ?>">
                <button type="submit" class="btn-fav" title="Remove favorite"
                        onclick="return confirm('Remove from favorites?')">✕</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

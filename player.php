<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Player.php';
require_once 'classes/Favorite.php';

$db        = Database::getInstance()->getConn();
$playerObj = new Player($db);
$favObj    = new Favorite($db);

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$player = $playerObj->getById($id);

if (!$player) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fav_action']) && User::isLoggedIn()) {
    $favObj->toggle((int)$_SESSION['user_id'], 'player', $id);
    header('Location: ' . BASE_URL . '/player.php?id=' . $id);
    exit();
}

$stats     = $playerObj->getStats($id);
$goalFeed  = $playerObj->getGoalEvents($id, 5);
$isFav     = User::isLoggedIn()
             ? $favObj->isFavorite((int)$_SESSION['user_id'], 'player', $id)
             : false;

$posLabels = ['GK' => 'Goalkeeper', 'DEF' => 'Defender', 'MID' => 'Midfielder', 'FWD' => 'Forward'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($player['name']) ?> — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-container">

    <!-- Player hero -->
    <div class="player-hero">
        <div class="player-avatar">
            <?php
            $posEmoji = match($player['position']) {
                'GK'  => '🧤', 'DEF' => '🛡️', 'MID' => '🎯', 'FWD' => '⚡',
                default => '⚽'
            };
            echo $posEmoji;
            ?>
        </div>
        <div class="player-info" style="flex:1;">
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <h1><?= htmlspecialchars($player['name']) ?></h1>
                <?php if (User::isLoggedIn()): ?>
                <form method="POST" action="">
                    <input type="hidden" name="fav_action" value="toggle">
                    <button type="submit" class="btn-fav <?= $isFav ? 'active' : '' ?>">
                        <?= $isFav ? '★ Favorited' : '☆ Add Favorite' ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <div class="sub">
                <a href="<?= BASE_URL ?>/team.php?id=<?= $player['team_id'] ?>" style="color:var(--blue);">
                    <?= htmlspecialchars($player['team_name']) ?>
                </a>
                · <?= htmlspecialchars($player['league_name']) ?>
            </div>
            <div class="team-hero-meta" style="margin-top:12px;">
                <div class="meta-item">
                    <strong><span class="pos-badge pos-<?= $player['position'] ?>"><?= $player['position'] ?></span></strong>
                    <?= $posLabels[$player['position']] ?? $player['position'] ?>
                </div>
                <?php if ($player['nationality']): ?>
                <div class="meta-item">
                    <strong><?= htmlspecialchars($player['nationality']) ?></strong>
                    Nationality
                </div>
                <?php endif; ?>
                <?php if ($player['age']): ?>
                <div class="meta-item">
                    <strong><?= $player['age'] ?></strong>
                    Age
                </div>
                <?php endif; ?>
                <?php if ($player['number']): ?>
                <div class="meta-item">
                    <strong>#<?= $player['number'] ?></strong>
                    Shirt
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Season stats -->
    <?php if ($stats): ?>
    <div class="section-label">2025/26 Season Stats</div>
    <div class="stat-grid" style="margin-top:12px;">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['matches_played'] ?></div>
            <div class="stat-label">Appearances</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--goal);"><?= $stats['goals'] ?></div>
            <div class="stat-label">Goals</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--blue);"><?= $stats['assists'] ?></div>
            <div class="stat-label">Assists</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['goals'] + $stats['assists'] ?></div>
            <div class="stat-label">G + A</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:var(--goal);"><?= $stats['yellow_cards'] ?></div>
            <div class="stat-label">Yellow Cards</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['minutes_played'] ?></div>
            <div class="stat-label">Minutes</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Goal feed -->
    <?php if (!empty($goalFeed)): ?>
    <div class="section-label" style="margin-top:24px;">Recent Goals</div>
    <div class="card" style="margin-top:12px;">
        <div class="table-wrapper">
            <table class="squad-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Match</th>
                        <th>Score</th>
                        <th>Minute</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($goalFeed as $g): ?>
                <tr>
                    <td style="color:var(--text-muted);"><?= date('d M Y', strtotime($g['match_date'])) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/match.php?id=<?= $g['match_id'] ?>"
                           style="color:var(--text);font-weight:600;">
                            <?= htmlspecialchars($g['home_name']) ?> vs <?= htmlspecialchars($g['away_name']) ?>
                        </a>
                    </td>
                    <td style="font-weight:700;"><?= $g['home_score'] ?> - <?= $g['away_score'] ?></td>
                    <td style="color:var(--goal);font-weight:700;"><?= $g['minute'] ?>'</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- No stats fallback -->
    <?php if (!$stats && empty($goalFeed)): ?>
    <div class="card" style="margin-top:20px;">
        <div class="empty-state">
            <div class="empty-icon">📊</div>
            <p>No stats available for this player yet.</p>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

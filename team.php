<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Team.php';
require_once 'classes/Match.php';
require_once 'classes/Standing.php';
require_once 'classes/Favorite.php';

$db         = Database::getInstance()->getConn();
$teamObj    = new Team($db);
$matchObj   = new FootballMatch($db);
$standingObj = new Standing($db);
$favObj     = new Favorite($db);

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$team = $teamObj->getById($id);

if (!$team) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

// Handle favorite toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fav_action']) && User::isLoggedIn()) {
    $favObj->toggle((int)$_SESSION['user_id'], 'team', $id);
    header('Location: ' . BASE_URL . '/team.php?id=' . $id);
    exit();
}

$squad    = $teamObj->getSquad($id);
$recent   = $matchObj->getRecentByTeam($id, 5);
$upcoming = $matchObj->getUpcomingByTeam($id, 3);
$position = $standingObj->getTeamPosition((int)$team['league_id'], $id);
$isFav    = User::isLoggedIn()
            ? $favObj->isFavorite((int)$_SESSION['user_id'], 'team', $id)
            : false;

// Group squad by position
$squadByPos = ['GK' => [], 'DEF' => [], 'MID' => [], 'FWD' => []];
foreach ($squad as $p) {
    $squadByPos[$p['position']][] = $p;
}

function resultBadge(array $match, int $teamId): string {
    if ($match['status'] !== 'finished') return '';
    $isHome = (int)$match['home_team_id'] === $teamId;
    $scored  = $isHome ? (int)$match['home_score'] : (int)$match['away_score'];
    $conceded = $isHome ? (int)$match['away_score'] : (int)$match['home_score'];
    if ($scored > $conceded)  return '<span class="result-badge result-w">W</span>';
    if ($scored === $conceded) return '<span class="result-badge result-d">D</span>';
    return '<span class="result-badge result-l">L</span>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($team['name']) ?> — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-container">

    <!-- Team hero -->
    <div class="team-hero">
        <div class="team-hero-badge"><?= htmlspecialchars($team['short_name']) ?></div>
        <div class="team-hero-info">
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <h1><?= htmlspecialchars($team['name']) ?></h1>
                <!-- Favorite button -->
                <?php if (User::isLoggedIn()): ?>
                <form method="POST" action="">
                    <input type="hidden" name="fav_action" value="toggle">
                    <button type="submit" class="btn-fav <?= $isFav ? 'active' : '' ?>">
                        <?= $isFav ? '★ Favorited' : '☆ Add Favorite' ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>
            <p><?= htmlspecialchars($team['league_name']) ?> · <?= htmlspecialchars($team['country']) ?></p>
            <div class="team-hero-meta">
                <?php if ($team['stadium']): ?>
                <div class="meta-item">
                    <strong><?= htmlspecialchars($team['stadium']) ?></strong>
                    Stadium
                </div>
                <?php endif; ?>
                <?php if ($team['founded']): ?>
                <div class="meta-item">
                    <strong><?= $team['founded'] ?></strong>
                    Founded
                </div>
                <?php endif; ?>
                <?php if ($team['coach']): ?>
                <div class="meta-item">
                    <strong><?= htmlspecialchars($team['coach']) ?></strong>
                    Manager
                </div>
                <?php endif; ?>
                <?php if ($position): ?>
                <div class="meta-item">
                    <strong>#<?= $position ?></strong>
                    League Position
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="two-col">
        <div>
            <!-- Recent matches -->
            <?php if (!empty($recent)): ?>
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header"><h2>Recent Results</h2></div>
                <div class="card-body" style="padding:0;">
                    <div class="matches-list">
                        <?php foreach ($recent as $m): ?>
                        <a href="<?= BASE_URL ?>/match.php?id=<?= $m['id'] ?>" class="match-row"
                           style="margin:0;border-radius:0;border-left:none;border-right:none;">
                            <div class="match-time">
                                <?= date('d M', strtotime($m['match_date'])) ?>
                                <div style="font-size:10px;color:var(--text-dim);"><?= htmlspecialchars($m['league_name']) ?></div>
                            </div>
                            <div class="match-team home">
                                <?= resultBadge($m, $id) ?>
                                <span><?= htmlspecialchars($m['home_name']) ?></span>
                                <div class="team-badge"><?= htmlspecialchars($m['home_short']) ?></div>
                            </div>
                            <div class="match-score">
                                <?= $m['home_score'] ?><span class="score-sep">-</span><?= $m['away_score'] ?>
                            </div>
                            <div class="match-team away">
                                <div class="team-badge"><?= htmlspecialchars($m['away_short']) ?></div>
                                <span><?= htmlspecialchars($m['away_name']) ?></span>
                            </div>
                            <div class="match-league"></div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Squad -->
            <div class="card">
                <div class="card-header"><h2>Squad</h2></div>
                <div class="table-wrapper">
                    <table class="squad-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Player</th>
                                <th>Position</th>
                                <th>Nationality</th>
                                <th>Age</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($squadByPos as $pos => $players):
                            if (empty($players)) continue;
                            foreach ($players as $p):
                        ?>
                        <tr>
                            <td style="color:var(--text-dim);font-weight:700;"><?= $p['number'] ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/player.php?id=<?= $p['id'] ?>" class="player-link">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </td>
                            <td><span class="pos-badge pos-<?= $p['position'] ?>"><?= $p['position'] ?></span></td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($p['nationality'] ?? '—') ?></td>
                            <td><?= $p['age'] ?? '—' ?></td>
                        </tr>
                        <?php endforeach; endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Upcoming sidebar -->
        <div>
            <?php if (!empty($upcoming)): ?>
            <div class="card">
                <div class="card-header"><h2>Next Fixtures</h2></div>
                <div class="card-body" style="padding:0;">
                    <?php foreach ($upcoming as $m): ?>
                    <a href="<?= BASE_URL ?>/match.php?id=<?= $m['id'] ?>" class="match-row"
                       style="border-radius:0;border-left:none;border-right:none;">
                        <div class="match-time">
                            <?= date('d M', strtotime($m['match_date'])) ?>
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
                        <div class="match-league"></div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

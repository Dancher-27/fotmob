<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Standing.php';
require_once 'classes/League.php';
require_once 'classes/Player.php';

$db          = Database::getInstance()->getConn();
$standingObj = new Standing($db);
$leagueObj   = new League($db);
$playerObj   = new Player($db);

$leagues    = $leagueObj->getAll();
$leagueId   = isset($_GET['league']) ? (int)$_GET['league'] : 1;
$league     = $leagueObj->getById($leagueId) ?? $leagues[0];
$standings  = $standingObj->getByLeague($leagueId);
$topScorers = $playerObj->getTopScorers($leagueId, 8);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standings — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-container">
    <div class="page-header">
        <h1>Standings</h1>
        <p>Season <?= htmlspecialchars($league['season']) ?></p>
    </div>

    <!-- League tabs -->
    <div class="league-tabs" style="margin-bottom:24px;">
        <?php foreach ($leagues as $l): ?>
        <a href="?league=<?= $l['id'] ?>"
           class="league-tab <?= (int)$l['id'] === $leagueId ? 'active' : '' ?>">
            <?= htmlspecialchars($l['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="two-col">
        <!-- Standings table -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2>
                        <?= htmlspecialchars($league['name']) ?>
                        <span class="league-badge"><?= htmlspecialchars($league['country']) ?></span>
                    </h2>
                </div>
                <div class="table-wrapper">
                    <table class="standings-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Team</th>
                                <th>P</th>
                                <th>W</th>
                                <th>D</th>
                                <th>L</th>
                                <th>GF</th>
                                <th>GA</th>
                                <th>GD</th>
                                <th>Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($standings as $i => $row):
                            $pos     = $i + 1;
                            $gd      = (int)$row['goals_for'] - (int)$row['goals_against'];
                            $gdClass = $gd > 0 ? 'gd-pos' : ($gd < 0 ? 'gd-neg' : '');
                        ?>
                        <tr class="pos-<?= $pos ?>">
                            <td><span class="pos-num"><?= $pos ?></span></td>
                            <td>
                                <a href="<?= BASE_URL ?>/team.php?id=<?= $row['team_id'] ?>" class="team-link">
                                    <div class="team-badge"><?= htmlspecialchars($row['short_name']) ?></div>
                                    <?= htmlspecialchars($row['team_name']) ?>
                                </a>
                            </td>
                            <td><?= $row['played'] ?></td>
                            <td class="text-win fw-bold"><?= $row['won'] ?></td>
                            <td><?= $row['drawn'] ?></td>
                            <td class="text-loss"><?= $row['lost'] ?></td>
                            <td><?= $row['goals_for'] ?></td>
                            <td><?= $row['goals_against'] ?></td>
                            <td class="<?= $gdClass ?>">
                                <?= ($gd > 0 ? '+' : '') . $gd ?>
                            </td>
                            <td class="pts-cell"><?= $row['points'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Legend -->
            <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:12px;font-size:12px;color:var(--text-dim);">
                <span><span style="color:var(--goal);">●</span> 1st place</span>
                <span><span style="color:var(--blue);">●</span> Champions League spots</span>
                <span><span style="color:var(--win);">W</span> Win &nbsp;
                      <span style="color:var(--draw);">D</span> Draw &nbsp;
                      <span style="color:var(--loss);">L</span> Loss</span>
            </div>
        </div>

        <!-- Top Scorers sidebar -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2>Top Scorers</h2>
                </div>
                <?php if (empty($topScorers)): ?>
                <div class="card-body">
                    <p class="text-muted" style="text-align:center;padding:20px 0;">No data available.</p>
                </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="squad-table" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Player</th>
                                <th>Club</th>
                                <th>G</th>
                                <th>A</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topScorers as $i => $p): ?>
                        <tr>
                            <td style="color:var(--text-dim);font-weight:700;"><?= $i+1 ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/player.php?id=<?= $p['id'] ?>"
                                   class="player-link" style="font-size:13px;">
                                    <?= htmlspecialchars($p['name']) ?>
                                </a>
                            </td>
                            <td style="color:var(--text-muted);"><?= htmlspecialchars($p['team_name']) ?></td>
                            <td style="font-weight:800;color:var(--goal);"><?= $p['goals'] ?></td>
                            <td style="color:var(--blue);"><?= $p['assists'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

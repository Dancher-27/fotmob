<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Match.php';

$db       = Database::getInstance()->getConn();
$matchObj = new FootballMatch($db);

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$match = $matchObj->getMatchById($id);

if (!$match) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$events  = $matchObj->getEvents($id);
$stats   = $matchObj->getStats($id);
$lineups = $matchObj->getLineups($id);

// Separate stats by team
$homeStats = null;
$awayStats = null;
foreach ($stats as $s) {
    if ((int)$s['team_id'] === (int)$match['home_team_id']) $homeStats = $s;
    else $awayStats = $s;
}

// Separate lineups by team
$homeLineup = array_values(array_filter($lineups, fn($p) => (int)$p['team_id'] === (int)$match['home_team_id']));
$awayLineup = array_values(array_filter($lineups, fn($p) => (int)$p['team_id'] === (int)$match['away_team_id']));

$statusLabel = match($match['status']) {
    'live'      => '<span class="status-pill status-live">Live</span>',
    'finished'  => '<span class="status-pill status-finished">Full Time</span>',
    default     => '<span class="status-pill status-scheduled">Upcoming</span>',
};

function eventIcon(string $type): string {
    return match($type) {
        'goal'         => '⚽',
        'yellow_card'  => '🟨',
        'red_card'     => '🟥',
        'substitution' => '🔄',
        default        => '•',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($match['home_name'] . ' vs ' . $match['away_name']) ?> — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-container">

    <!-- Hero score panel -->
    <div class="match-hero">
        <div class="league-info">
            <?= htmlspecialchars($match['league_name']) ?> —
            <?= date('l, d F Y · H:i', strtotime($match['match_date'])) ?>
            <?php if ($match['matchday']): ?>· MD <?= $match['matchday'] ?><?php endif; ?>
        </div>

        <div class="teams-row">
            <div class="team-block">
                <a href="<?= BASE_URL ?>/team.php?id=<?= $match['home_team_id'] ?>">
                    <div class="big-team-badge"><?= htmlspecialchars($match['home_short']) ?></div>
                    <div class="team-name"><?= htmlspecialchars($match['home_name']) ?></div>
                </a>
            </div>

            <div>
                <?php if ($match['status'] !== 'scheduled'): ?>
                <div class="big-score">
                    <span><?= $match['home_score'] ?></span>
                    <span class="sep">-</span>
                    <span><?= $match['away_score'] ?></span>
                </div>
                <?php else: ?>
                <div style="font-size:20px;font-weight:700;color:var(--text-muted);">
                    <?= date('H:i', strtotime($match['match_date'])) ?>
                </div>
                <?php endif; ?>
                <div style="text-align:center;margin-top:10px;">
                    <?= $statusLabel ?>
                </div>
            </div>

            <div class="team-block">
                <a href="<?= BASE_URL ?>/team.php?id=<?= $match['away_team_id'] ?>">
                    <div class="big-team-badge"><?= htmlspecialchars($match['away_short']) ?></div>
                    <div class="team-name"><?= htmlspecialchars($match['away_name']) ?></div>
                </a>
            </div>
        </div>

        <div class="match-meta">
            📍 <?= htmlspecialchars($match['venue'] ?: 'Venue TBC') ?>
        </div>
    </div>

    <div class="two-col">
        <div>
            <!-- Events timeline -->
            <?php if (!empty($events)): ?>
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header"><h2>Match Events</h2></div>
                <div class="card-body">
                    <div class="events-list">
                        <?php foreach ($events as $e):
                            $isHome = (int)$e['team_id'] === (int)$match['home_team_id'];
                        ?>
                        <div class="event-item <?= $isHome ? 'home-event' : 'away-event' ?>">
                            <div class="event-left">
                                <?php if ($isHome): ?>
                                <?= eventIcon($e['event_type']) ?>
                                <strong><?= htmlspecialchars($e['player_name'] ?? '—') ?></strong>
                                <?php endif; ?>
                            </div>
                            <div class="event-minute"><?= $e['minute'] ?>'</div>
                            <div class="event-right">
                                <?php if (!$isHome): ?>
                                <strong><?= htmlspecialchars($e['player_name'] ?? '—') ?></strong>
                                <?= eventIcon($e['event_type']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lineups -->
            <?php if (!empty($homeLineup) || !empty($awayLineup)): ?>
            <div class="card">
                <div class="card-header"><h2>Lineups</h2></div>
                <div class="card-body">
                    <div class="lineup-grid">
                        <div class="lineup-col">
                            <h3><?= htmlspecialchars($match['home_name']) ?></h3>
                            <?php foreach ($homeLineup as $p): ?>
                            <div class="lineup-player">
                                <span class="lineup-number"><?= $p['number'] ?></span>
                                <span class="pos-badge pos-<?= $p['position'] ?>"><?= $p['position'] ?></span>
                                <a href="<?= BASE_URL ?>/player.php?id=<?= $p['player_id'] ?>"
                                   class="player-link" style="font-size:13px;">
                                    <?= htmlspecialchars($p['player_name']) ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="lineup-col">
                            <h3><?= htmlspecialchars($match['away_name']) ?></h3>
                            <?php foreach ($awayLineup as $p): ?>
                            <div class="lineup-player">
                                <span class="lineup-number"><?= $p['number'] ?></span>
                                <span class="pos-badge pos-<?= $p['position'] ?>"><?= $p['position'] ?></span>
                                <a href="<?= BASE_URL ?>/player.php?id=<?= $p['player_id'] ?>"
                                   class="player-link" style="font-size:13px;">
                                    <?= htmlspecialchars($p['player_name']) ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Stats sidebar -->
        <div>
            <?php if ($homeStats && $awayStats): ?>
            <div class="card">
                <div class="card-header"><h2>Match Stats</h2></div>
                <div class="card-body">
                    <!-- Team names -->
                    <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;
                                color:var(--text-muted);margin-bottom:16px;">
                        <span style="color:var(--purple-light);"><?= htmlspecialchars($match['home_short']) ?></span>
                        <span style="color:var(--blue);"><?= htmlspecialchars($match['away_short']) ?></span>
                    </div>

                    <?php
                    $statRows = [
                        ['Possession %',        'possession'],
                        ['Shots',               'shots'],
                        ['Shots on Target',     'shots_on_target'],
                        ['Corners',             'corners'],
                        ['Fouls',               'fouls'],
                        ['Yellow Cards',        'yellow_cards'],
                    ];
                    foreach ($statRows as [$label, $key]):
                        $h   = (int)$homeStats[$key];
                        $a   = (int)$awayStats[$key];
                        $tot = $h + $a;
                        $hPct = $tot > 0 ? round($h / $tot * 100) : 50;
                        $aPct = 100 - $hPct;
                    ?>
                    <div class="stat-row">
                        <div class="stat-label">
                            <span class="val" style="color:var(--purple-light);"><?= $h ?></span>
                            <span><?= $label ?></span>
                            <span class="val" style="color:var(--blue);"><?= $a ?></span>
                        </div>
                        <div class="stat-bar">
                            <div class="bar-home" style="width:<?= $hPct ?>%;"></div>
                            <div class="bar-away" style="width:<?= $aPct ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php elseif ($match['status'] === 'scheduled'): ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-icon">📊</div>
                        <p>Stats will be available after kick-off.</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

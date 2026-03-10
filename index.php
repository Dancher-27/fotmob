<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Match.php';
require_once 'classes/League.php';

$db      = Database::getInstance()->getConn();
$matchObj = new FootballMatch($db);
$leagueObj = new League($db);

$leagues  = $leagueObj->getAll();
$tab      = isset($_GET['tab']) ? (int)$_GET['tab'] : 0; // 0 = all
$section  = $_GET['section'] ?? 'results'; // results | upcoming

$todayMatches   = $matchObj->getTodaysMatches();
$recentResults  = $matchObj->getRecentResults(20);
$upcomingMatches = $matchObj->getUpcoming(20);

// Filter by league tab
function filterByLeague(array $matches, int $leagueId): array {
    if ($leagueId === 0) return $matches;
    return array_values(array_filter($matches, fn($m) => (int)$m['league_id'] === $leagueId));
}

$filteredResults  = filterByLeague($recentResults,  $tab);
$filteredUpcoming = filterByLeague($upcomingMatches, $tab);
$filteredToday    = filterByLeague($todayMatches,    $tab);

function matchTime(string $date, string $status): string {
    if ($status === 'live')      return '<span class="status-live">LIVE</span>';
    if ($status === 'finished')  return date('H:i', strtotime($date));
    return date('H:i', strtotime($date));
}

function matchScoreHtml(array $m): string {
    if ($m['status'] === 'finished' || $m['status'] === 'live') {
        return '<div class="match-score">'
            . htmlspecialchars($m['home_score'])
            . '<span class="score-sep">-</span>'
            . htmlspecialchars($m['away_score'])
            . '</div>';
    }
    return '<div class="match-score scheduled">'
        . date('H:i', strtotime($m['match_date']))
        . '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="page-container">
    <div class="page-header">
        <h1>Football Matches</h1>
        <p><?= date('l, F j, Y') ?></p>
    </div>

    <!-- League tabs -->
    <div class="league-tabs">
        <a href="?tab=0&section=<?= $section ?>"
           class="league-tab <?= $tab === 0 ? 'active' : '' ?>">All Leagues</a>
        <?php foreach ($leagues as $l): ?>
        <a href="?tab=<?= $l['id'] ?>&section=<?= $section ?>"
           class="league-tab <?= $tab === (int)$l['id'] ? 'active' : '' ?>">
            <?= htmlspecialchars($l['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Section toggle -->
    <div style="display:flex;gap:10px;margin-bottom:20px;">
        <a href="?tab=<?= $tab ?>&section=results"
           class="btn <?= $section === 'results' ? 'btn-primary' : 'btn-secondary' ?>">
            Recent Results
        </a>
        <a href="?tab=<?= $tab ?>&section=upcoming"
           class="btn <?= $section === 'upcoming' ? 'btn-primary' : 'btn-secondary' ?>">
            Upcoming
        </a>
        <?php if (!empty($filteredToday)): ?>
        <a href="?tab=<?= $tab ?>&section=today"
           class="btn <?= $section === 'today' ? 'btn-primary' : 'btn-secondary' ?>">
            Today
        </a>
        <?php endif; ?>
    </div>

    <!-- Match list -->
    <?php
    $displayMatches = match($section) {
        'today'    => $filteredToday,
        'upcoming' => $filteredUpcoming,
        default    => $filteredResults,
    };

    // Group by league
    $grouped = [];
    foreach ($displayMatches as $m) {
        $grouped[$m['league_name']][] = $m;
    }
    ?>

    <?php if (empty($displayMatches)): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-icon">📅</div>
            <p>No matches found for this filter.</p>
        </div>
    </div>
    <?php else: ?>

    <?php foreach ($grouped as $leagueName => $matches): ?>
    <div class="section-label"><?= htmlspecialchars($leagueName) ?></div>
    <div class="matches-list mb-16">
        <?php foreach ($matches as $m): ?>
        <a href="<?= BASE_URL ?>/match.php?id=<?= $m['id'] ?>" class="match-row">
            <div class="match-time">
                <?= matchTime($m['match_date'], $m['status']) ?>
                <div style="font-size:11px;color:var(--text-dim);margin-top:2px;">
                    <?= date('d M', strtotime($m['match_date'])) ?>
                </div>
            </div>
            <div class="match-team home">
                <span><?= htmlspecialchars($m['home_name']) ?></span>
                <div class="team-badge"><?= htmlspecialchars($m['home_short']) ?></div>
            </div>
            <?= matchScoreHtml($m) ?>
            <div class="match-team away">
                <div class="team-badge"><?= htmlspecialchars($m['away_short']) ?></div>
                <span><?= htmlspecialchars($m['away_name']) ?></span>
            </div>
            <div class="match-league"><?= htmlspecialchars($m['league_name']) ?></div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

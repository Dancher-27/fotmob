<?php
// navbar.php — included on every page after session_start()
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="<?= BASE_URL ?>/index.php" class="navbar-logo">
            <span class="logo-icon">⚽</span>
            <span class="logo-text"><?= SITE_NAME ?></span>
        </a>

        <ul class="navbar-links">
            <li><a href="<?= BASE_URL ?>/index.php"
                   class="<?= $currentPage === 'index' ? 'active' : '' ?>">Matches</a></li>
            <li><a href="<?= BASE_URL ?>/standings.php?league=1"
                   class="<?= $currentPage === 'standings' ? 'active' : '' ?>">Standings</a></li>
            <li><a href="<?= BASE_URL ?>/search.php"
                   class="<?= $currentPage === 'search' ? 'active' : '' ?>">Search</a></li>
            <?php if (User::isLoggedIn()): ?>
            <li><a href="<?= BASE_URL ?>/favorites.php"
                   class="<?= $currentPage === 'favorites' ? 'active' : '' ?>">Favorites</a></li>
            <?php endif; ?>
        </ul>

        <div class="navbar-user">
            <?php if (User::isLoggedIn()): ?>
                <span class="user-greeting">Hi, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-sm">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php"    class="btn btn-outline-sm">Login</a>
                <a href="<?= BASE_URL ?>/register.php" class="btn btn-primary-sm">Register</a>
            <?php endif; ?>
        </div>

        <button class="navbar-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<script>
document.getElementById('navToggle').addEventListener('click', function() {
    document.querySelector('.navbar-links').classList.toggle('open');
});
</script>

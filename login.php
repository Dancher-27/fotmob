<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';

if (User::isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit();
}

$db   = Database::getInstance()->getConn();
$user = new User($db);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif ($user->login($username, $password)) {
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">⚽</div>
            <h1><?= SITE_NAME ?></h1>
            <p>Sign in to your account</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="Your username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Your password"
                       required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>

        <div class="auth-footer">
            Don't have an account?
            <a href="<?= BASE_URL ?>/register.php">Create one</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

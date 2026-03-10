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
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = $user->register($username, $email, $password);
        if ($result === true) {
            $success = 'Account created! You can now <a href="' . BASE_URL . '/login.php">sign in</a>.';
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon">⚽</div>
            <h1><?= SITE_NAME ?></h1>
            <p>Create your account</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control"
                       placeholder="Choose a username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       required minlength="3" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="your@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="At least 6 characters"
                       required minlength="6" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" class="form-control"
                       placeholder="Repeat your password"
                       required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">
            Already have an account?
            <a href="<?= BASE_URL ?>/login.php">Sign in</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

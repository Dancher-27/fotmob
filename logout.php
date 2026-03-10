<?php
session_name("kickoff");
session_start();
require_once 'includes/config.php';
require_once 'classes/Database.php';
require_once 'classes/User.php';

$db   = Database::getInstance()->getConn();
$user = new User($db);
$user->logout();

header('Location: ' . BASE_URL . '/index.php');
exit();

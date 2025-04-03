<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$loggedIn = isLoggedIn();
$cars = getAvailableCars();
?>

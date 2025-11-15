<?php
require_once '../config/config.php';

// Destroy session
session_destroy();

// Redirect to login
redirect('auth/login.php');
?>


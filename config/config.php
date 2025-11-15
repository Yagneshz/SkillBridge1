<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL Configuration
// Update this to match your actual server path
// For PHP built-in server: http://localhost:8000/
define('BASE_URL', 'http://localhost:8000/');
define('BASE_PATH', __DIR__ . '/../');

// File Upload Paths
define('UPLOAD_DIR', BASE_PATH . 'uploads/');
define('RESUME_DIR', UPLOAD_DIR . 'resumes/');
define('PROFILE_DIR', UPLOAD_DIR . 'profiles/');
define('LOGO_DIR', UPLOAD_DIR . 'logos/');

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
if (!file_exists(RESUME_DIR)) {
    mkdir(RESUME_DIR, 0777, true);
}
if (!file_exists(PROFILE_DIR)) {
    mkdir(PROFILE_DIR, 0777, true);
}
if (!file_exists(LOGO_DIR)) {
    mkdir(LOGO_DIR, 0777, true);
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Helper Functions
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('auth/login.php');
    }
}

function requireUserType($allowedTypes) {
    requireLogin();
    if (!in_array($_SESSION['user_type'], $allowedTypes)) {
        redirect('index.php');
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}
?>


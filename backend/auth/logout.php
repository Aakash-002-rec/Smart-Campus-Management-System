<?php
/**
 * Smart Campus - Logout Handler
 * Completely invalidates PHP sessions and redirects safely to login page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session values
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// If called via JSON/API, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Logged out successfully', 'redirect' => '../../frontend/pages/login.php']);
    exit();
}

// Direct HTML redirection
header("Location: ../../frontend/pages/login.php");
exit();
?>

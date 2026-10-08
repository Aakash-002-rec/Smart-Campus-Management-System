<?php
/**
 * Database Configuration
 * Connects to MySQL in XAMPP
 */

$host = '127.0.0.1'; // Use IPv4 127.0.0.1 to avoid Windows localhost/IPv6 ::1 resolution issues
$user = 'root';
$password = ''; // Default XAMPP password is empty
$database = 'smart_campus';
$port = 3306;

// Set mysqli to not throw fatal uncaught exceptions so we can handle errors gracefully
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $user, $password, $database, $port);

// If 127.0.0.1 failed, attempt localhost fallback
if ($conn->connect_error) {
    $conn = @new mysqli('localhost', $user, $password, $database, $port);
}

if ($conn->connect_error) {
    // If called from an API, return JSON error rather than crashing
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed. Ensure MySQL is started in XAMPP (Port 3306). Details: ' . $conn->connect_error
    ]);
    exit();
}

$conn->set_charset("utf8mb4");
?>

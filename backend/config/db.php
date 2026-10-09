<?php
/**
 * Database Configuration
 * Supports production environment variables (Railway, Render, Koyeb, Docker)
 * and falls back gracefully to local XAMPP MySQL (127.0.0.1 / localhost).
 */

// Production Environment Variables with Local XAMPP Fallbacks
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : '');
$database = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: 'smart_campus';
$port = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: 3306);

// Set mysqli to not throw fatal uncaught exceptions so we can handle errors gracefully
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $user, $password, $database, $port);

// If 127.0.0.1 failed, attempt localhost fallback (for local Windows XAMPP environments)
if ($conn->connect_error && ($host === '127.0.0.1' || $host === 'localhost')) {
    $conn = @new mysqli('localhost', $user, $password, $database, $port);
}

if ($conn->connect_error) {
    // Check if this is an API/AJAX request
    $is_api = (
        (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
        (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        (isset($_SERVER['SCRIPT_NAME']) && strpos($_SERVER['SCRIPT_NAME'], '/backend/') !== false)
    );

    if ($is_api) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed. Please verify database server status and configuration.'
        ]);
        exit();
    } else {
        // Human-friendly error page for direct browser navigation
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Database Unavailable - Smart Campus</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background: #fdfbf7; font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
                .card { max-width: 520px; border-radius: 16px; border: 1px solid #e0d7c6; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
            </style>
        </head>
        <body>
            <div class="card p-4 text-center">
                <h4 class="fw-bold text-dark">Database Unavailable</h4>
                <p class="text-muted small mt-2">Could not connect to the database. In production, check environment variables (<code>MYSQLHOST</code>, <code>MYSQLUSER</code>, <code>MYSQLPASSWORD</code>, <code>MYSQLDATABASE</code>). In local development, ensure MySQL is running in XAMPP.</p>
                <div class="mt-3">
                    <a href="javascript:location.reload()" class="btn btn-outline-primary btn-sm rounded-pill px-4">Retry</a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

$conn->set_charset("utf8mb4");
?>

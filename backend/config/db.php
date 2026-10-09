<?php
/**
 * Database Configuration
 * Supports production cloud database environment variables, private configuration files,
 * and seamless local XAMPP fallback.
 */

// 1. Load private credentials file if present (excluded from Git)
$credentials_file = __DIR__ . '/db_credentials.php';
$credentials = file_exists($credentials_file) ? include $credentials_file : [];

// 2. Determine environment ('auto', 'local', or 'production')
$configured_env = $credentials['environment'] ?? 'auto';

if ($configured_env === 'auto') {
    $server_host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    // Strip port if present (e.g. localhost:8000)
    $clean_host = strtolower(explode(':', $server_host)[0]);

    $is_local = (
        empty($clean_host) ||
        $clean_host === 'localhost' ||
        $clean_host === '127.0.0.1' ||
        $clean_host === '::1' ||
        php_sapi_name() === 'cli'
    );
    $active_env = $is_local ? 'local' : 'production';
} else {
    $active_env = ($configured_env === 'production') ? 'production' : 'local';
}

// 3. Resolve Database Credentials based on environment
if ($active_env === 'production') {
    $host     = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: ($credentials['production']['host'] ?? '127.0.0.1');
    $port     = (int)(getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: ($credentials['production']['port'] ?? 3306));
    $user     = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: ($credentials['production']['user'] ?? 'root');
    $database = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: ($credentials['production']['database'] ?? 'smart_campus');
    $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : ($credentials['production']['password'] ?? ''));
} else {
    // Local XAMPP Environment
    $host     = getenv('LOCAL_DB_HOST') ?: ($credentials['local']['host'] ?? '127.0.0.1');
    $port     = (int)(getenv('LOCAL_DB_PORT') ?: ($credentials['local']['port'] ?? 3306));
    $user     = getenv('LOCAL_DB_USER') ?: ($credentials['local']['user'] ?? 'root');
    $database = getenv('LOCAL_DB_NAME') ?: ($credentials['local']['database'] ?? 'smart_campus');
    $password = getenv('LOCAL_DB_PASS') !== false ? getenv('LOCAL_DB_PASS') : ($credentials['local']['password'] ?? '');
}

// 4. Initialize MySQLi Connection
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $user, $password, $database, $port);

// If local 127.0.0.1 connection failed, attempt localhost fallback (for Windows XAMPP)
if ($conn->connect_error && ($host === '127.0.0.1' || $host === 'localhost')) {
    $conn = @new mysqli('localhost', $user, $password, $database, $port);
}

// 5. Connection Error Handling
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
            'message' => 'Database connection failed. Please verify database server credentials.'
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
                .card { max-width: 540px; border-radius: 16px; border: 1px solid #e0d7c6; box-shadow: 0 10px 30px rgba(0,0,0,0.06); }
            </style>
        </head>
        <body>
            <div class="card p-4 text-center">
                <h4 class="fw-bold text-dark">Database Connection Issue</h4>
                <p class="text-muted small mt-2">
                    Could not establish connection to the MySQL database.
                    <br><br>
                    <strong>Production:</strong> Verify database host (<code><?php echo htmlspecialchars($host); ?></code>), database name (<code><?php echo htmlspecialchars($database); ?></code>), and credentials via environment variables or <code>backend/config/db_credentials.php</code>.
                    <br>
                    <strong>Local:</strong> Ensure MySQL service is running in XAMPP.
                </p>
                <div class="mt-3">
                    <a href="javascript:location.reload()" class="btn btn-outline-primary btn-sm rounded-pill px-4">Retry Connection</a>
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

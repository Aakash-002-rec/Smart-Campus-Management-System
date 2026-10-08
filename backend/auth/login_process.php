<?php
/**
 * Smart Campus - Login Processing Backend
 * Secure authentication using MySQLi Prepared Statements & PHP Sessions
 */

// Start secure session
session_start();

// Set header for JSON response
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../config/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// Sanitize & validate inputs
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($email) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Please provide both email and password.'
    ]);
    exit();
}

// Prepare statement to query the user by email or register number
$stmt = $conn->prepare("SELECT id, name, register_no, email, password, role, department, year FROM users WHERE email = ? OR register_no = ? LIMIT 1");

if (!$stmt) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database query preparation failed: ' . $conn->error
    ]);
    exit();
}

$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify Password (supports plain text AND password_hash if upgraded later)
    $password_matched = false;

    if ($password === $user['password']) {
        $password_matched = true;
    } elseif (password_verify($password, $user['password'])) {
        $password_matched = true;
    }

    if ($password_matched) {
        // Set Session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['register_no'] = $user['register_no'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['department'] = $user['department'];
        $_SESSION['year'] = $user['year'];

        // Determine destination dashboard based on user role
        $redirectUrl = '';
        switch ($user['role']) {
            case 'student':
                $redirectUrl = 'student-dashboard.php';
                break;
            case 'faculty':
                $redirectUrl = 'faculty-dashboard.php';
                break;
            case 'admin':
                $redirectUrl = 'admin-dashboard.php';
                break;
            default:
                $redirectUrl = 'login.php';
                break;
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'name' => $user['name'],
            'role' => ucfirst($user['role']),
            'redirect' => $redirectUrl
        ]);
        $stmt->close();
        $conn->close();
        exit();
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Incorrect password. Please try again.'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No account found with this email address.'
    ]);
    $stmt->close();
    $conn->close();
    exit();
}
?>

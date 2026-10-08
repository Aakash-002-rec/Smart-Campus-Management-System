<?php
/**
 * Smart Campus - Admin: Add Student or Faculty
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Only admins can create users.']);
    exit();
}

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$register_no = isset($_POST['register_no']) ? trim($_POST['register_no']) : null;
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';$role = isset($_POST['role']) ? trim($_POST['role']) : 'student';
$department = isset($_POST['department']) ? trim($_POST['department']) : 'Computer Science';
$year = isset($_POST['year']) ? (int)$_POST['year'] : 1;

if (empty($name) || empty($email) || empty($role)) {
    echo json_encode(['status' => 'error', 'message' => 'Name, Email, and Role are mandatory.']);
    exit();
}

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'A user with this email address already exists.']);
    exit();
}
$check->close();

// Check if register number already exists
if (!empty($register_no)) {
    $check_reg = $conn->prepare("SELECT id FROM users WHERE register_no = ?");
    $check_reg->bind_param("s", $register_no);
    $check_reg->execute();
    if ($check_reg->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A student with this Register / Roll Number already exists.']);
        exit();
    }
    $check_reg->close();
}

$stmt = $conn->prepare("INSERT INTO users (name, register_no, email, password, role, department, year, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("ssssssi", $name, $register_no, $email, $password, $role, $department, $year);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => ucfirst($role) . ' created successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

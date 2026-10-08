<?php
/**
 * Smart Campus - Admin: Delete User (Student or Faculty)
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID.']);
    exit();
}

// Prevent self-deletion
if ($user_id === (int)$_SESSION['user_id']) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete your own active administrator account.']);
    exit();
}

// Ensure clean deletion across child tables
$conn->query("DELETE FROM attendance_records WHERE student_id = " . (int)$user_id);
$conn->query("DELETE FROM student_marks WHERE student_id = " . (int)$user_id);
$conn->query("DELETE FROM assignment_submissions WHERE student_id = " . (int)$user_id);
$conn->query("DELETE FROM attendance WHERE student_id = " . (int)$user_id);
$conn->query("DELETE FROM marks WHERE student_id = " . (int)$user_id);

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

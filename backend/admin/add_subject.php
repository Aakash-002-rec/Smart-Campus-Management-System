<?php
/**
 * Smart Campus - Admin: Add Subject with Planned Course Hours
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$subject_name = isset($_POST['subject_name']) ? trim($_POST['subject_name']) : '';
$subject_code = isset($_POST['subject_code']) ? trim($_POST['subject_code']) : '';
$department = isset($_POST['department']) ? trim($_POST['department']) : '';
$semester = isset($_POST['semester']) ? (int)$_POST['semester'] : 1;
$total_course_hours = isset($_POST['total_course_hours']) ? (int)$_POST['total_course_hours'] : 75;

if (empty($subject_name) || empty($subject_code) || empty($department)) {
    echo json_encode(['status' => 'error', 'message' => 'Subject name, code, and department are required.']);
    exit();
}

if ($total_course_hours < 30 || $total_course_hours > 150) {
    $total_course_hours = 75;
}

$stmt = $conn->prepare("INSERT INTO subjects (subject_name, subject_code, department, semester, total_course_hours) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssii", $subject_name, $subject_code, $department, $semester, $total_course_hours);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Course added to curriculum with ' . $total_course_hours . ' planned hours successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

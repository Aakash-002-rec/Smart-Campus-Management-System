<?php
/**
 * Smart Campus - Update / Record Student Attendance
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

// Auth check: faculty or admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['faculty', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
$subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$total_classes = isset($_POST['total_classes']) ? (int)$_POST['total_classes'] : 0;
$attended_classes = isset($_POST['attended_classes']) ? (int)$_POST['attended_classes'] : 0;

if ($student_id <= 0 || $subject_id <= 0 || $total_classes < 0 || $attended_classes < 0 || $attended_classes > $total_classes) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid attendance inputs. Attended classes cannot exceed total classes.']);
    exit();
}

// Check if attendance record already exists for this student & subject
$check = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND subject_id = ?");
$check->bind_param("ii", $student_id, $subject_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Update existing record
    $row = $res->fetch_assoc();
    $update = $conn->prepare("UPDATE attendance SET total_classes = ?, attended_classes = ?, attendance_date = CURDATE() WHERE id = ?");
    $update->bind_param("iii", $total_classes, $attended_classes, $row['id']);
    $success = $update->execute();
    $update->close();
} else {
    // Insert new record
    $insert = $conn->prepare("INSERT INTO attendance (student_id, subject_id, total_classes, attended_classes, attendance_date) VALUES (?, ?, ?, ?, CURDATE())");
    $insert->bind_param("iiii", $student_id, $subject_id, $total_classes, $attended_classes);
    $success = $insert->execute();
    $insert->close();
}
$check->close();

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Attendance record updated successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update attendance: ' . $conn->error]);
}
$conn->close();
?>

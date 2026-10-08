<?php
/**
 * Smart Campus - Enter / Update Student Academic Marks
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
$internal1 = isset($_POST['internal1']) ? (float)$_POST['internal1'] : 0.0;
$internal2 = isset($_POST['internal2']) ? (float)$_POST['internal2'] : 0.0;
$assignment_mark = isset($_POST['assignment_mark']) ? (float)$_POST['assignment_mark'] : 0.0;

if ($student_id <= 0 || $subject_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please select a valid student and subject.']);
    exit();
}

// Check if marks record already exists
$check = $conn->prepare("SELECT id FROM marks WHERE student_id = ? AND subject_id = ?");
$check->bind_param("ii", $student_id, $subject_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    // Update
    $row = $res->fetch_assoc();
    $update = $conn->prepare("UPDATE marks SET internal1 = ?, internal2 = ?, assignment_mark = ? WHERE id = ?");
    $update->bind_param("dddi", $internal1, $internal2, $assignment_mark, $row['id']);
    $success = $update->execute();
    $update->close();
} else {
    // Insert
    $insert = $conn->prepare("INSERT INTO marks (student_id, subject_id, internal1, internal2, assignment_mark) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("iiddd", $student_id, $subject_id, $internal1, $internal2, $assignment_mark);
    $success = $insert->execute();
    $insert->close();
}
$check->close();

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Student marks updated successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update marks: ' . $conn->error]);
}
$conn->close();
?>

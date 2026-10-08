<?php
/**
 * Smart Campus - Fetch Student Submissions for a Specific Assignment
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['faculty', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$assignment_id = isset($_GET['assignment_id']) ? (int)$_GET['assignment_id'] : 0;

if ($assignment_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid assignment ID.']);
    exit();
}

// Get assignment details
$asg_stmt = $conn->prepare("
    SELECT a.id, a.title, a.due_date, s.subject_name, s.subject_code, a.attachment_path, a.attachment_name
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.id = ?
");
$asg_stmt->bind_param("i", $assignment_id);
$asg_stmt->execute();
$assignment = $asg_stmt->get_result()->fetch_assoc();
$asg_stmt->close();

if (!$assignment) {
    echo json_encode(['status' => 'error', 'message' => 'Assignment not found.']);
    exit();
}

// Fetch submissions
$sub_stmt = $conn->prepare("
    SELECT sub.id, sub.student_id, sub.file_path, sub.file_name, sub.file_size, sub.remarks, sub.status, sub.submitted_at,
           u.name AS student_name, u.register_no, u.department, u.year
    FROM assignment_submissions sub
    JOIN users u ON sub.student_id = u.id
    WHERE sub.assignment_id = ?
    ORDER BY sub.submitted_at DESC
");
$sub_stmt->bind_param("i", $assignment_id);
$sub_stmt->execute();
$res = $sub_stmt->get_result();

$submissions = [];
while ($row = $res->fetch_assoc()) {
    $row['formatted_date'] = date('d M Y, h:i A', strtotime($row['submitted_at']));
    $row['size_formatted'] = $row['file_size'] > 1048576 
        ? round($row['file_size'] / 1048576, 2) . ' MB' 
        : round($row['file_size'] / 1024, 1) . ' KB';
    $submissions[] = $row;
}
$sub_stmt->close();
$conn->close();

echo json_encode([
    'status' => 'success',
    'assignment' => $assignment,
    'submissions' => $submissions,
    'total_submissions' => count($submissions)
]);
?>

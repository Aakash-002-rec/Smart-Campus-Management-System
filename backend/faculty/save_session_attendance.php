<?php
/**
 * Smart Campus - Faculty Session Attendance Handler
 * Saves session metadata and student attendance statuses transactionally.
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please login as faculty.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$faculty_id = (int)$_SESSION['user_id'];
$subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$session_date = isset($_POST['session_date']) ? trim($_POST['session_date']) : date('Y-m-d');
$period_number = isset($_POST['period_number']) ? (int)$_POST['period_number'] : 1;
$start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '09:00:00';
$end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '10:00:00';
$topic = isset($_POST['topic']) ? trim($_POST['topic']) : 'General Lecture';
$attendance_data = isset($_POST['attendance']) && is_array($_POST['attendance']) ? $_POST['attendance'] : [];

if ($subject_id <= 0 || empty($session_date) || empty($topic)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide subject, date, period, and lecture topic.']);
    exit();
}

if (empty($attendance_data)) {
    echo json_encode(['status' => 'error', 'message' => 'No student attendance records received.']);
    exit();
}

// Start Transaction
$conn->begin_transaction();

try {
    // 1. Insert Attendance Session
    $stmt = $conn->prepare("
        INSERT INTO attendance_sessions (subject_id, faculty_id, session_date, period_number, start_time, end_time, topic) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisisss", $subject_id, $faculty_id, $session_date, $period_number, $start_time, $end_time, $topic);
    $stmt->execute();
    $session_id = $stmt->insert_id;
    $stmt->close();

    // 2. Insert Student Attendance Records
    $rec_stmt = $conn->prepare("
        INSERT INTO attendance_records (session_id, student_id, status) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE status = VALUES(status), marked_at = CURRENT_TIMESTAMP
    ");

    $present_count = 0;
    $absent_count = 0;
    $late_count = 0;

    foreach ($attendance_data as $student_id => $status) {
        $sid = (int)$student_id;
        $st = in_array($status, ['Present', 'Absent', 'Late']) ? $status : 'Present';

        if ($st === 'Present') $present_count++;
        elseif ($st === 'Late') $late_count++;
        else $absent_count++;

        $rec_stmt->bind_param("iis", $session_id, $sid, $st);
        $rec_stmt->execute();
    }
    $rec_stmt->close();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Attendance saved successfully.',
        'session_id' => $session_id,
        'summary' => [
            'total' => count($attendance_data),
            'present' => $present_count,
            'late' => $late_count,
            'absent' => $absent_count
        ]
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save attendance: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

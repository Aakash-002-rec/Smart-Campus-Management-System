<?php
/**
 * Smart Campus - Faculty Multi-Assessment Mark Entry Handler
 * Validates 0 <= marks <= max_marks and stores assessments and student scores.
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
$assessment_name = isset($_POST['assessment_name']) ? trim($_POST['assessment_name']) : '';
$assessment_type = isset($_POST['assessment_type']) ? trim($_POST['assessment_type']) : 'internal';
$max_marks = isset($_POST['max_marks']) ? (float)$_POST['max_marks'] : 50.00;
$assessment_date = isset($_POST['assessment_date']) ? trim($_POST['assessment_date']) : date('Y-m-d');
$marks_data = isset($_POST['marks']) && is_array($_POST['marks']) ? $_POST['marks'] : [];
$remarks_data = isset($_POST['remarks']) && is_array($_POST['remarks']) ? $_POST['remarks'] : [];

if ($subject_id <= 0 || empty($assessment_name) || $max_marks <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide valid subject, assessment name, and maximum marks (> 0).']);
    exit();
}

if (empty($marks_data)) {
    echo json_encode(['status' => 'error', 'message' => 'No student marks submitted.']);
    exit();
}

// Validate mark ranges
foreach ($marks_data as $student_id => $score) {
    $numeric_score = (float)$score;
    if ($numeric_score < 0 || $numeric_score > $max_marks) {
        echo json_encode([
            'status' => 'error',
            'message' => "Invalid mark ({$numeric_score}) entered. Marks must be between 0 and {$max_marks}."
        ]);
        exit();
    }
}

$conn->begin_transaction();

try {
    // 1. Find or Create Assessment
    $check_stmt = $conn->prepare("
        SELECT id FROM assessments 
        WHERE subject_id = ? AND assessment_name = ?
        LIMIT 1
    ");
    $check_stmt->bind_param("is", $subject_id, $assessment_name);
    $check_stmt->execute();
    $ass_res = $check_stmt->get_result();

    if ($ass_res->num_rows > 0) {
        $assessment_id = (int)$ass_res->fetch_assoc()['id'];
        $update_ass = $conn->prepare("UPDATE assessments SET max_marks = ?, assessment_date = ?, assessment_type = ? WHERE id = ?");
        $update_ass->bind_param("dssi", $max_marks, $assessment_date, $assessment_type, $assessment_id);
        $update_ass->execute();
        $update_ass->close();
    } else {
        $ins_ass = $conn->prepare("INSERT INTO assessments (subject_id, assessment_name, assessment_type, max_marks, assessment_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $ins_ass->bind_param("issdsi", $subject_id, $assessment_name, $assessment_type, $max_marks, $assessment_date, $faculty_id);
        $ins_ass->execute();
        $assessment_id = $ins_ass->insert_id;
        $ins_ass->close();
    }
    $check_stmt->close();

    // 2. Insert or Update Student Marks
    $m_stmt = $conn->prepare("
        INSERT INTO student_marks (assessment_id, student_id, marks, remarks)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE marks = VALUES(marks), remarks = VALUES(remarks), updated_at = CURRENT_TIMESTAMP
    ");

    $saved_count = 0;
    foreach ($marks_data as $student_id => $score) {
        $sid = (int)$student_id;
        $scored_val = (float)$score;
        $remark = isset($remarks_data[$student_id]) ? trim($remarks_data[$student_id]) : '';

        $m_stmt->bind_param("iids", $assessment_id, $sid, $scored_val, $remark);
        $m_stmt->execute();
        $saved_count++;
    }
    $m_stmt->close();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Assessment marks saved successfully.',
        'assessment_id' => $assessment_id,
        'records_saved' => $saved_count
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save marks: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

<?php
/**
 * Smart Campus - Student Assignment File/PDF Submission (Up to 50 MB)
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Only students can submit assignments.']);
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$assignment_id = isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0;
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

if ($assignment_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid assignment specified.']);
    exit();
}

// Verify assignment exists and get due date
$asg_stmt = $conn->prepare("SELECT id, title, due_date FROM assignments WHERE id = ?");
$asg_stmt->bind_param("i", $assignment_id);
$asg_stmt->execute();
$asg = $asg_stmt->get_result()->fetch_assoc();
$asg_stmt->close();

if (!$asg) {
    echo json_encode(['status' => 'error', 'message' => 'Assignment not found.']);
    exit();
}

// Check if file is uploaded
if (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['status' => 'error', 'message' => 'Please select a file to submit (e.g. PDF, Document, or Photo).']);
    exit();
}

$file = $_FILES['submission_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE   => 'Uploaded file exceeds server limit.',
        UPLOAD_ERR_FORM_SIZE  => 'Uploaded file exceeds HTML form limit.',
        UPLOAD_ERR_PARTIAL    => 'File upload was incomplete.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'File upload was stopped by extension.'
    ];
    $msg = $upload_errors[$file['error']] ?? 'An error occurred during file upload.';
    echo json_encode(['status' => 'error', 'message' => $msg]);
    exit();
}

$max_bytes = 50 * 1024 * 1024; // 50 MB Limit
if ($file['size'] > $max_bytes) {
    echo json_encode(['status' => 'error', 'message' => 'File size exceeds the 50 MB limit. Please compress or choose a smaller file.']);
    exit();
}

$orig_name = basename($file['name']);
$ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
$allowed_extensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'webp', 'zip', 'rar'];

if (!in_array($ext, $allowed_extensions)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Allowed formats: PDF, Images (JPG, PNG), Word Documents (DOCX), and ZIP files.']);
    exit();
}

$target_dir = __DIR__ . '/../../uploads/submissions/';
if (!is_dir($target_dir)) {
    @mkdir($target_dir, 0777, true);
}

// Check if late submission
$due_time = strtotime($asg['due_date'] . ' 23:59:59');
$is_late = (time() > $due_time);
$submission_status = $is_late ? 'Late' : 'Submitted';

$new_filename = 'sub_' . $assignment_id . '_' . $student_id . '_' . time() . '.' . $ext;
$target_path = $target_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save submission file on server.']);
    exit();
}

$file_path = 'uploads/submissions/' . $new_filename;
$file_size = (int)$file['size'];

// Insert or update submission record
$stmt = $conn->prepare("
    INSERT INTO assignment_submissions (assignment_id, student_id, file_path, file_name, file_size, remarks, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        file_path = VALUES(file_path),
        file_name = VALUES(file_name),
        file_size = VALUES(file_size),
        remarks = VALUES(remarks),
        status = VALUES(status),
        submitted_at = CURRENT_TIMESTAMP
");
$stmt->bind_param("iississ", $assignment_id, $student_id, $file_path, $orig_name, $file_size, $remarks, $submission_status);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Assignment submitted successfully! (' . $orig_name . ')',
        'submission' => [
            'file_name' => $orig_name,
            'file_path' => $file_path,
            'file_size' => $file_size,
            'status' => $submission_status,
            'submitted_at' => date('d M Y, h:i A')
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

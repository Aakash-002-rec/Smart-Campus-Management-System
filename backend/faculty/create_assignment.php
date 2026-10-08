<?php
/**
 * Smart Campus - Create / Post New Assignment (Homework) with File/Photo Attachment
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['faculty', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$subject_id = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$due_date = isset($_POST['due_date']) ? trim($_POST['due_date']) : '';
$created_by = (int)$_SESSION['user_id'];

if ($subject_id <= 0 || empty($title) || empty($due_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Subject, title, and due date are required.']);
    exit();
}

$attachment_path = null;
$attachment_name = null;

// Handle file/photo upload up to 50 MB
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['attachment'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'Uploaded file exceeds server upload size limit.',
            UPLOAD_ERR_FORM_SIZE  => 'Uploaded file exceeds form limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
        ];
        $msg = $upload_errors[$file['error']] ?? 'Error occurred during file upload.';
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit();
    }

    $max_bytes = 50 * 1024 * 1024; // 50 MB
    if ($file['size'] > $max_bytes) {
        echo json_encode(['status' => 'error', 'message' => 'File size exceeds maximum allowed limit of 50 MB.']);
        exit();
    }

    $orig_name = basename($file['name']);
    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'zip', 'rar'];

    if (!in_array($ext, $allowed_extensions)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Allowed: PDF, Images (JPG, PNG, WEBP), Word/Excel documents, and ZIP archives.']);
        exit();
    }

    $target_dir = __DIR__ . '/../../uploads/assignments/';
    if (!is_dir($target_dir)) {
        @mkdir($target_dir, 0777, true);
    }

    $safe_name = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($orig_name, PATHINFO_FILENAME));
    $new_filename = 'asg_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
    $target_path = $target_dir . $new_filename;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file to destination server directory.']);
        exit();
    }

    $attachment_path = 'uploads/assignments/' . $new_filename;
    $attachment_name = $orig_name;
}

$stmt = $conn->prepare("INSERT INTO assignments (subject_id, title, description, due_date, created_by, attachment_path, attachment_name) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssiss", $subject_id, $title, $description, $due_date, $created_by, $attachment_path, $attachment_name);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Assignment created and posted successfully!' . ($attachment_name ? ' File attached: ' . $attachment_name : '')
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

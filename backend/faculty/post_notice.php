<?php
/**
 * Smart Campus - Publish Notice / Circular
 */
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['faculty', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$posted_by = (int)$_SESSION['user_id'];

if (empty($title) || empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide both title and notice content.']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO notices (title, content, posted_by, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("ssi", $title, $content, $posted_by);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Notice published successfully to all students!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

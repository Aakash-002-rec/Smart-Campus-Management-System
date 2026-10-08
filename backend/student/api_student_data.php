<?php
/**
 * Smart Campus - Student Data REST API
 * Returns JSON data for student metrics, subjects, attendance, marks, and early warnings
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/get_student_data.php';

// Allow student_id via session OR query parameter for flexibility & React testing
$student_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_GET['student_id']) ? (int)$_GET['student_id'] : 1);

$data = getStudentFullData($conn, $student_id);

echo json_encode([
    'status' => 'success',
    'timestamp' => date('c'),
    'data' => $data
]);
$conn->close();
?>

<?php
require_once __DIR__ . '/../backend/config/db.php';

// 1. Add attachment columns to assignments table if they don't exist
$cols = $conn->query("SHOW COLUMNS FROM assignments LIKE 'attachment_path'");
if ($cols && $cols->num_rows == 0) {
    $alter = $conn->query("ALTER TABLE assignments ADD COLUMN attachment_path VARCHAR(255) DEFAULT NULL, ADD COLUMN attachment_name VARCHAR(255) DEFAULT NULL");
    if ($alter) {
        echo "[OK] Added attachment columns to assignments table.\n";
    } else {
        echo "[ERROR] Failed to add columns: " . $conn->error . "\n";
    }
} else {
    echo "[INFO] Attachment columns already exist in assignments table.\n";
}

// 2. Create assignment_submissions table
$sql = "CREATE TABLE IF NOT EXISTS assignment_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    student_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL DEFAULT 0,
    remarks TEXT DEFAULT NULL,
    status ENUM('Submitted', 'Reviewed', 'Late') DEFAULT 'Submitted',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_asg_stu (assignment_id, student_id)
)";

if ($conn->query($sql)) {
    echo "[OK] assignment_submissions table created/verified successfully.\n";
} else {
    echo "[ERROR] Error creating assignment_submissions table: " . $conn->error . "\n";
}
?>

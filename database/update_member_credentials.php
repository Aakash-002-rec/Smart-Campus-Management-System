<?php
/**
 * Update all users to have email formatted as <xxx>campus@gmail.com
 * and a unique password for each member.
 */
require_once __DIR__ . '/../backend/config/db.php';

echo "=== Updating Member Credentials ===\n\n";

$result = $conn->query("SELECT id, name, register_no, role FROM users ORDER BY id ASC");
if (!$result) {
    die("Error fetching users: " . $conn->error . "\n");
}

$updated = 0;
$credentials = [];

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $name = trim($row['name']);
    $role = $row['role'];
    $reg = trim($row['register_no'] ?? '');

    // Generate unique email formatted like xxxcampus@gmail.com
    $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode(' ', str_replace(['Dr. ', 'Prof. '], '', $name))[0]));
    
    if ($role === 'admin') {
        $email = "admincampus@gmail.com";
        $password = "Admin@Campus2026";
    } elseif ($role === 'faculty') {
        $email = $clean_name . "campus@gmail.com";
        $password = "Faculty@" . (!empty($reg) ? $reg : "FAC0" . $id);
    } else {
        // Student: use firstname or reg_no if name collision
        $reg_suffix = !empty($reg) ? strtolower($reg) : "stu" . str_pad($id, 3, '0', STR_PAD_LEFT);
        $email = $clean_name . "campus@gmail.com";
        
        // Ensure email uniqueness
        if (isset($credentials[$email])) {
            $email = $clean_name . "." . $reg_suffix . "campus@gmail.com";
        }
        
        // Unique password for student
        $clean_reg = !empty($reg) ? strtoupper($reg) : "23CS" . str_pad($id, 3, '0', STR_PAD_LEFT);
        $password = ucfirst($clean_name) . "@" . substr($clean_reg, -3);
    }

    $credentials[$email] = true;

    // Update user in database
    $stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
    $stmt->bind_param("ssi", $email, $password, $id);
    $stmt->execute();
    $stmt->close();

    $updated++;
    echo sprintf("[%02d] %-25s | %-10s | %-32s | %-18s\n", $id, substr($name, 0, 24), $role, $email, $password);
}

echo "\n✔ Successfully updated credentials for {$updated} members!\n";
?>

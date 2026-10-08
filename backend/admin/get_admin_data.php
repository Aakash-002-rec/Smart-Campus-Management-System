<?php
/**
 * Smart Campus - Admin Central Data Engine
 * Computes institutional metrics, user rosters, subject listings, and global early warning records.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../student/get_student_data.php';

function getAdminDashboardData($conn) {
    $data = [
        'students' => [],
        'faculty' => [],
        'subjects' => [],
        'at_risk_students' => [],
        'stats' => [
            'total_students' => 0,
            'total_faculty' => 0,
            'total_subjects' => 0,
            'campus_avg_attendance' => 0,
            'campus_at_risk_count' => 0
        ]
    ];

    // 1. Fetch Students
    $stu_res = $conn->query("SELECT id, name, register_no, email, password, department, year, created_at FROM users WHERE role = 'student' ORDER BY register_no ASC");
    $total_att_sum = 0;
    if ($stu_res) {
        while ($row = $stu_res->fetch_assoc()) {
            $stu_eval = getStudentFullData($conn, $row['id']);
            $row['overall_attendance'] = $stu_eval['stats']['overall_attendance'];
            $row['average_marks'] = $stu_eval['stats']['average_marks'];
            $row['is_at_risk'] = $stu_eval['stats']['is_at_risk'];
            $row['risk_level'] = $stu_eval['stats']['risk_level'];
            $row['warnings'] = $stu_eval['stats']['warnings'];

            $data['students'][] = $row;
            $total_att_sum += $row['overall_attendance'];

            if ($row['is_at_risk']) {
                $data['at_risk_students'][] = $row;
            }
        }
    }
    $data['stats']['total_students'] = count($data['students']);
    $data['stats']['campus_at_risk_count'] = count($data['at_risk_students']);
    $data['stats']['campus_avg_attendance'] = $data['stats']['total_students'] > 0 ? round($total_att_sum / $data['stats']['total_students'], 1) : 0;

    // 2. Fetch Faculty with Assigned Subjects
    $fac_res = $conn->query("
        SELECT
            u.id,
            u.name,
            u.email,
            u.department,
            u.created_at,
            GROUP_CONCAT(CONCAT(s.subject_name, ' (', s.subject_code, ')') SEPARATOR ', ') AS assigned_subjects,
            COUNT(s.id) AS assigned_count
        FROM users u
        LEFT JOIN subjects s ON u.id = s.faculty_id
        WHERE u.role = 'faculty'
        GROUP BY u.id
        ORDER BY u.name ASC
    ");
    if ($fac_res) {
        while ($row = $fac_res->fetch_assoc()) {
            $data['faculty'][] = $row;
        }
    }
    $data['stats']['total_faculty'] = count($data['faculty']);

    // 3. Fetch Subjects with Assigned Faculty Name
    $sub_res = $conn->query("
        SELECT 
            s.id, 
            s.subject_name, 
            s.subject_code, 
            s.department, 
            s.semester, 
            COALESCE(s.total_course_hours, 75) AS total_course_hours,
            s.faculty_id,
            COALESCE(u.name, 'Unassigned') AS faculty_name,
            u.email AS faculty_email
        FROM subjects s
        LEFT JOIN users u ON s.faculty_id = u.id
        ORDER BY s.subject_name ASC
    ");
    if ($sub_res) {
        while ($row = $sub_res->fetch_assoc()) {
            $sid = (int)$row['id'];
            $cnt_res = $conn->query("SELECT COUNT(*) AS cnt FROM attendance_sessions WHERE subject_id = $sid");
            $conducted = $cnt_res ? (int)$cnt_res->fetch_assoc()['cnt'] : 0;
            $row['conducted_sessions'] = $conducted;
            $row['remaining_hours'] = max(0, (int)$row['total_course_hours'] - $conducted);
            $row['progress_pct'] = (int)$row['total_course_hours'] > 0 ? round(($conducted / (int)$row['total_course_hours']) * 100, 1) : 0;
            $data['subjects'][] = $row;
        }
    }
    $data['stats']['total_subjects'] = count($data['subjects']);

    return $data;
}
?>

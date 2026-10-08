<?php
/**
 * Smart Campus - Faculty Data Engine
 * Fetches assigned subject metrics, session history, assessment lists, and student performance.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../student/get_student_data.php';

function getFacultyDashboardData($conn) {
    $data = [
        'faculty' => null,
        'assigned_subjects' => [],
        'subjects' => [],
        'students' => [],
        'recent_sessions' => [],
        'assessments' => [],
        'at_risk_students' => [],
        'stats' => [
            'total_students' => 0,
            'total_subjects' => 0,
            'assigned_subjects_count' => 0,
            'total_sessions_conducted' => 0,
            'at_risk_count' => 0
        ]
    ];

    $faculty_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 2;

    // 1. Fetch Faculty Profile
    $stmt = $conn->prepare("SELECT id, name, email, role, department FROM users WHERE id = ?");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $data['faculty'] = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 2. Fetch Subjects (Prioritizing Assigned Course for this Faculty)
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
            (s.faculty_id = $faculty_id) AS is_assigned
        FROM subjects s
        LEFT JOIN users u ON s.faculty_id = u.id
        ORDER BY (s.faculty_id = $faculty_id) DESC, s.subject_name ASC
    ");
    if ($sub_res) {
        while ($row = $sub_res->fetch_assoc()) {
            $sid = (int)$row['id'];
            $c_res = $conn->query("SELECT COUNT(*) AS cnt FROM attendance_sessions WHERE subject_id = $sid");
            $conducted = $c_res ? (int)$c_res->fetch_assoc()['cnt'] : 0;
            $row['conducted_sessions'] = $conducted;
            $row['remaining_hours'] = max(0, (int)$row['total_course_hours'] - $conducted);
            $row['progress_percentage'] = (int)$row['total_course_hours'] > 0 ? round(($conducted / (int)$row['total_course_hours']) * 100, 1) : 0;
            
            $data['subjects'][] = $row;
            if ($row['is_assigned']) {
                $data['assigned_subjects'][] = $row;
            }
        }
    }
    $data['stats']['total_subjects'] = count($data['subjects']);
    $data['stats']['assigned_subjects_count'] = count($data['assigned_subjects']);

    // 3. Fetch Recent Sessions (Conducted by this faculty or for their assigned subjects)
    $sess_stmt = $conn->prepare("
        SELECT 
            s.id AS session_id,
            s.session_date,
            s.period_number,
            s.topic,
            s.start_time,
            s.end_time,
            sub.subject_name,
            sub.subject_code,
            COUNT(r.id) AS total_students_marked,
            SUM(CASE WHEN r.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
            SUM(CASE WHEN r.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
            SUM(CASE WHEN r.status = 'Late' THEN 1 ELSE 0 END) AS late_count
        FROM attendance_sessions s
        JOIN subjects sub ON s.subject_id = sub.id
        LEFT JOIN attendance_records r ON s.id = r.session_id
        WHERE s.faculty_id = ? OR sub.faculty_id = ?
        GROUP BY s.id
        ORDER BY s.session_date DESC, s.period_number DESC
        LIMIT 20
    ");
    $sess_stmt->bind_param("ii", $faculty_id, $faculty_id);
    $sess_stmt->execute();
    $sess_res = $sess_stmt->get_result();
    if ($sess_res && $sess_res->num_rows > 0) {
        while ($row = $sess_res->fetch_assoc()) {
            $data['recent_sessions'][] = $row;
        }
    } else {
        // Fallback to all latest sessions if none specifically tagged
        $fallback_res = $conn->query("
            SELECT 
                s.id AS session_id,
                s.session_date,
                s.period_number,
                s.topic,
                s.start_time,
                s.end_time,
                sub.subject_name,
                sub.subject_code,
                COUNT(r.id) AS total_students_marked,
                SUM(CASE WHEN r.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN r.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN r.status = 'Late' THEN 1 ELSE 0 END) AS late_count
            FROM attendance_sessions s
            JOIN subjects sub ON s.subject_id = sub.id
            LEFT JOIN attendance_records r ON s.id = r.session_id
            GROUP BY s.id
            ORDER BY s.session_date DESC, s.period_number DESC
            LIMIT 20
        ");
        if ($fallback_res) {
            while ($row = $fallback_res->fetch_assoc()) {
                $data['recent_sessions'][] = $row;
            }
        }
    }
    $sess_stmt->close();

    $tot_sess_stmt = $conn->prepare("SELECT COUNT(*) AS total_all FROM attendance_sessions WHERE faculty_id = ?");
    $tot_sess_stmt->bind_param("i", $faculty_id);
    $tot_sess_stmt->execute();
    $tot_res = $tot_sess_stmt->get_result();
    $faculty_conducted = $tot_res ? (int)$tot_res->fetch_assoc()['total_all'] : 0;
    $tot_sess_stmt->close();

    $data['stats']['total_sessions_conducted'] = $faculty_conducted > 0 ? $faculty_conducted : count($data['recent_sessions']);

    // 4. Fetch All Students & Rule-Based Analytics
    $stu_res = $conn->query("SELECT id, name, register_no, email, department, year FROM users WHERE role = 'student' ORDER BY register_no ASC");
    if ($stu_res) {
        while ($row = $stu_res->fetch_assoc()) {
            $stu_data = getStudentFullData($conn, $row['id']);
            $row['overall_attendance'] = $stu_data['stats']['overall_attendance'];
            $row['average_marks'] = $stu_data['stats']['average_marks'];
            $row['is_at_risk'] = $stu_data['stats']['is_at_risk'];
            $row['risk_level'] = $stu_data['stats']['risk_level'];
            $row['warnings'] = $stu_data['stats']['warnings'];
            $row['recommendations'] = $stu_data['stats']['recommendations'];
            $row['attendance_status'] = $stu_data['stats']['attendance_status'];
            $row['overall_grade'] = $stu_data['stats']['overall_grade'];

            $data['students'][] = $row;

            if ($row['is_at_risk']) {
                $data['at_risk_students'][] = $row;
            }
        }
    }
    $data['stats']['total_students'] = count($data['students']);
    $data['stats']['at_risk_count'] = count($data['at_risk_students']);

    // 5. Fetch Assessments List
    $ass_res = $conn->query("
        SELECT a.id, a.subject_id, a.assessment_name, a.assessment_type, a.max_marks, a.assessment_date, sub.subject_name, sub.subject_code,
               (sub.faculty_id = $faculty_id) AS is_my_course,
               COUNT(sm.id) AS marks_entered_count,
               AVG(sm.marks) AS class_average_mark
        FROM assessments a
        JOIN subjects sub ON a.subject_id = sub.id
        LEFT JOIN student_marks sm ON a.id = sm.assessment_id
        GROUP BY a.id
        ORDER BY (sub.faculty_id = $faculty_id) DESC, a.assessment_date DESC, a.id DESC
    ");
    if ($ass_res) {
        while ($row = $ass_res->fetch_assoc()) {
            $row['class_average_mark'] = $row['class_average_mark'] !== null ? round((float)$row['class_average_mark'], 1) : 0;
            $data['assessments'][] = $row;
        }
    }

    return $data;
}
?>

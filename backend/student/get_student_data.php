<?php
/**
 * Smart Campus - Student Data Provider & Analytics Engine
 * Dynamically computes attendance, multi-assessment marks, course progress, and rule-based insights.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function getStudentFullData($conn, $student_id) {
    $data = [
        'student' => null,
        'attendance' => [],
        'attendance_history' => [],
        'marks' => [],
        'subject_assessments' => [],
        'timetable' => [],
        'assignments' => [],
        'notices' => [],
        'stats' => [
            'overall_attendance' => 0,
            'attendance_status' => 'Good',
            'attendance_badge_class' => 'bg-success',
            'total_conducted_sessions' => 0,
            'total_present_sessions' => 0,
            'total_absent_sessions' => 0,
            'average_marks' => 0,
            'overall_grade' => 'A',
            'pending_assignments' => 0,
            'best_subject' => 'N/A',
            'weakest_subject' => 'N/A',
            'total_course_hours_all' => 0,
            'completed_course_hours_all' => 0,
            'remaining_course_hours_all' => 0,
            'course_progress_percentage' => 0,
            'is_at_risk' => false,
            'risk_level' => 'Low Risk',
            'warnings' => [],
            'recommendations' => []
        ]
    ];

    // 1. Fetch Student Profile
    $stmt = $conn->prepare("SELECT id, name, register_no, email, role, department, year FROM users WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student_res = $stmt->get_result();
    if ($student_res->num_rows === 0) {
        return $data;
    }
    $data['student'] = $student_res->fetch_assoc();
    $stmt->close();

    // 2. Fetch All Department Subjects & Planned Course Hours
    $subjects = [];
    $sub_query = "SELECT s.id, s.subject_name, s.subject_code, s.department, s.semester, COALESCE(s.total_course_hours, 75) AS total_course_hours, s.faculty_id, COALESCE(u.name, 'Department Faculty') AS faculty_name FROM subjects s LEFT JOIN users u ON s.faculty_id = u.id ORDER BY s.subject_name";
    $sub_res = $conn->query($sub_query);
    if ($sub_res) {
        while ($row = $sub_res->fetch_assoc()) {
            $subjects[$row['id']] = $row;
        }
    }

    // 3. Compute Session-Based Attendance Per Subject
    $total_present_all = 0;
    $total_conducted_all = 0;
    $total_absent_all = 0;
    $total_raw_present_all = 0;
    $total_late_all = 0;
    $total_late_penalty_cuts_all = 0;
    $total_effective_present_all = 0;
    $total_effective_absent_all = 0;
    $low_attendance_subjects = [];
    $total_hours_sum = 0;
    $completed_hours_sum = 0;

    foreach ($subjects as $sub_id => $sub) {
        // Count total conducted sessions for this subject
        $sess_stmt = $conn->prepare("
            SELECT COUNT(*) AS total_sessions 
            FROM attendance_sessions 
            WHERE subject_id = ?
        ");
        $sess_stmt->bind_param("i", $sub_id);
        $sess_stmt->execute();
        $sess_res = $sess_stmt->get_result()->fetch_assoc();
        $conducted = $sess_res ? (int)$sess_res['total_sessions'] : 0;
        $sess_stmt->close();

        // Count present/late/absent for this student
        $rec_stmt = $conn->prepare("
            SELECT 
                SUM(CASE WHEN ar.status = 'Present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN ar.status = 'Late' THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN ar.status = 'Absent' THEN 1 ELSE 0 END) AS absent_count
            FROM attendance_records ar
            JOIN attendance_sessions asess ON ar.session_id = asess.id
            WHERE asess.subject_id = ? AND ar.student_id = ?
        ");
        $rec_stmt->bind_param("ii", $sub_id, $student_id);
        $rec_stmt->execute();
        $rec_res = $rec_stmt->get_result()->fetch_assoc();
        $present = $rec_res && $rec_res['present_count'] !== null ? (int)$rec_res['present_count'] : 0;
        $late = $rec_res && $rec_res['late_count'] !== null ? (int)$rec_res['late_count'] : 0;
        $absent = $rec_res && $rec_res['absent_count'] !== null ? (int)$rec_res['absent_count'] : 0;
        $rec_stmt->close();

        // Fallback to legacy attendance table if no sessions exist
        if ($conducted === 0) {
            $leg_stmt = $conn->prepare("SELECT total_classes, attended_classes FROM attendance WHERE student_id = ? AND subject_id = ?");
            $leg_stmt->bind_param("ii", $student_id, $sub_id);
            $leg_stmt->execute();
            $leg_res = $leg_stmt->get_result()->fetch_assoc();
            if ($leg_res && (int)$leg_res['total_classes'] > 0) {
                $conducted = (int)$leg_res['total_classes'];
                $present = (int)$leg_res['attended_classes'];
                $absent = max(0, $conducted - $present);
                $late = 0;
            }
            $leg_stmt->close();
        }

        // --- Punctuality & Late Deduction Rule ---
        // Rule: For every 5 late classes, 1 class is automatically cut (deducted from attended classes)
        $raw_present = $present;
        $late_count = $late;
        $raw_absent = $absent;

        // Physical sessions attended (on-time + late)
        $physical_attended = $raw_present + $late_count;

        // Late penalty: 5 late sessions = 1 class cutted
        $late_penalty_cuts = intdiv($late_count, 5);

        // Effective attended sessions after automatic late cut
        $effective_present = max(0, $physical_attended - $late_penalty_cuts);

        // Effective absent sessions (unattended + late penalty deductions)
        $effective_absent = $conducted > 0 ? max(0, $conducted - $effective_present) : ($raw_absent + $late_penalty_cuts);

        // Attendance Percentage Formula: (Effective Present / Conducted) * 100
        $pct = $conducted > 0 ? round(($effective_present / $conducted) * 100, 1) : 100.0;

        // Status thresholds: >= 75% Eligible, < 75% Shortage (Grades reserved for marks)
        if ($pct >= 75) {
            $status = 'Eligible';
            $badge_class = 'bg-success';
            $text_class = 'text-success';
        } else {
            $status = 'Shortage';
            $badge_class = 'bg-danger';
            $text_class = 'text-danger';
            $low_attendance_subjects[] = [
                'subject' => $sub['subject_name'],
                'code' => $sub['subject_code'],
                'percentage' => $pct,
                'conducted' => $conducted,
                'present' => $effective_present,
                'late' => $late_count,
                'late_cuts' => $late_penalty_cuts
            ];
        }

        // Course Progress Tracking
        $total_hours = (int)$sub['total_course_hours'];
        $completed_hours = $conducted; // 1 session = 1 course hour
        $remaining_hours = max(0, $total_hours - $completed_hours);
        $course_progress_pct = $total_hours > 0 ? min(100, round(($completed_hours / $total_hours) * 100, 1)) : 0;

        $total_hours_sum += $total_hours;
        $completed_hours_sum += $completed_hours;
        $total_conducted_all += $conducted;
        $total_raw_present_all += $raw_present;
        $total_late_all += $late_count;
        $total_late_penalty_cuts_all += $late_penalty_cuts;
        $total_effective_present_all += $effective_present;
        $total_effective_absent_all += $effective_absent;

        $data['attendance'][] = [
            'subject_id' => $sub_id,
            'subject_name' => $sub['subject_name'],
            'subject_code' => $sub['subject_code'],
            'faculty_name' => $sub['faculty_name'] ?? 'Faculty Lead',
            'semester' => $sub['semester'],
            'total_course_hours' => $total_hours,
            'completed_hours' => $completed_hours,
            'remaining_hours' => $remaining_hours,
            'course_progress_percentage' => $course_progress_pct,
            'conducted' => $conducted,
            'present' => $effective_present,
            'raw_present' => $raw_present,
            'late' => $late_count,
            'late_penalty_cuts' => $late_penalty_cuts,
            'lates_to_next_cut' => 5 - ($late_count % 5),
            'effective_present' => $effective_present,
            'effective_absent' => $effective_absent,
            'absent' => $effective_absent,
            'raw_absent' => $raw_absent,
            'percentage' => $pct,
            'status' => $status,
            'badge_class' => $badge_class,
            'text_class' => $text_class
        ];
    }

    // Overall Attendance (incorporates 5 lates = 1 cut policy across all subjects)
    $overall_att_pct = $total_conducted_all > 0 ? round(($total_effective_present_all / $total_conducted_all) * 100, 1) : 100.0;
    $data['stats']['overall_attendance'] = $overall_att_pct;
    $data['stats']['total_conducted_sessions'] = $total_conducted_all;
    $data['stats']['total_present_sessions'] = $total_effective_present_all;
    $data['stats']['total_raw_present_sessions'] = $total_raw_present_all;
    $data['stats']['total_late_sessions'] = $total_late_all;
    $data['stats']['total_late_penalty_cuts'] = $total_late_penalty_cuts_all;
    $data['stats']['total_absent_sessions'] = $total_effective_absent_all;
    $data['stats']['total_raw_absent_sessions'] = max(0, $total_conducted_all - $total_raw_present_all - $total_late_all);

    if ($overall_att_pct >= 75) {
        $data['stats']['attendance_status'] = 'Eligible';
        $data['stats']['attendance_badge_class'] = 'bg-success';
    } else {
        $data['stats']['attendance_status'] = 'Shortage';
        $data['stats']['attendance_badge_class'] = 'bg-danger';
    }

    // Overall Course Hours
    $data['stats']['total_course_hours_all'] = $total_hours_sum;
    $data['stats']['completed_course_hours_all'] = $completed_hours_sum;
    $data['stats']['remaining_course_hours_all'] = max(0, $total_hours_sum - $completed_hours_sum);
    $data['stats']['course_progress_percentage'] = $total_hours_sum > 0 ? round(($completed_hours_sum / $total_hours_sum) * 100, 1) : 0;

    // 4. Fetch Full Detailed Session Attendance History
    $hist_stmt = $conn->prepare("
        SELECT 
            asess.id AS session_id,
            asess.session_date,
            asess.period_number,
            asess.start_time,
            asess.end_time,
            asess.topic,
            s.subject_name,
            s.subject_code,
            ar.status AS attendance_status,
            ar.marked_at
        FROM attendance_records ar
        JOIN attendance_sessions asess ON ar.session_id = asess.id
        JOIN subjects s ON asess.subject_id = s.id
        WHERE ar.student_id = ?
        ORDER BY asess.session_date DESC, asess.period_number DESC
    ");
    $hist_stmt->bind_param("i", $student_id);
    $hist_stmt->execute();
    $hist_res = $hist_stmt->get_result();
    while ($row = $hist_res->fetch_assoc()) {
        $data['attendance_history'][] = $row;
    }
    $hist_stmt->close();

    // 5. Fetch Multi-Assessment Marks
    $subject_scores = [];
    $low_marks_subjects = [];
    $total_earned_marks = 0;
    $total_max_marks = 0;

    foreach ($subjects as $sub_id => $sub) {
        $ass_stmt = $conn->prepare("
            SELECT 
                a.id AS assessment_id,
                a.assessment_name,
                a.assessment_type,
                a.max_marks,
                a.assessment_date,
                COALESCE(sm.marks, 0) AS scored_marks,
                sm.remarks
            FROM assessments a
            LEFT JOIN student_marks sm ON a.id = sm.assessment_id AND sm.student_id = ?
            WHERE a.subject_id = ?
            ORDER BY a.assessment_date ASC, a.id ASC
        ");
        $ass_stmt->bind_param("ii", $student_id, $sub_id);
        $ass_stmt->execute();
        $ass_res = $ass_stmt->get_result();

        $sub_earned = 0;
        $sub_max = 0;
        $assessments_list = [];

        while ($arow = $ass_res->fetch_assoc()) {
            $s_mark = (float)$arow['scored_marks'];
            $m_mark = (float)$arow['max_marks'];
            $pct_ass = $m_mark > 0 ? round(($s_mark / $m_mark) * 100, 1) : 0;
            $arow['percentage'] = $pct_ass;
            $assessments_list[] = $arow;

            $sub_earned += $s_mark;
            $sub_max += $m_mark;
        }
        $ass_stmt->close();

        // Fallback to legacy marks table if no assessments found
        if ($sub_max === 0) {
            $leg_m_stmt = $conn->prepare("SELECT internal1, internal2, assignment_mark FROM marks WHERE student_id = ? AND subject_id = ?");
            $leg_m_stmt->bind_param("ii", $student_id, $sub_id);
            $leg_m_stmt->execute();
            $leg_m = $leg_m_stmt->get_result()->fetch_assoc();
            if ($leg_m) {
                $i1 = (float)$leg_m['internal1'];
                $i2 = (float)$leg_m['internal2'];
                $asg = (float)$leg_m['assignment_mark'];
                $assessments_list = [
                    ['assessment_name' => 'Internal Assessment 1', 'max_marks' => 40, 'scored_marks' => $i1, 'percentage' => round(($i1/40)*100,1), 'remarks' => 'Legacy record'],
                    ['assessment_name' => 'Internal Assessment 2', 'max_marks' => 40, 'scored_marks' => $i2, 'percentage' => round(($i2/40)*100,1), 'remarks' => 'Legacy record'],
                    ['assessment_name' => 'Assignment Work', 'max_marks' => 20, 'scored_marks' => $asg, 'percentage' => round(($asg/20)*100,1), 'remarks' => 'Legacy record']
                ];
                $sub_earned = $i1 + $i2 + $asg;
                $sub_max = 100;
            }
            $leg_m_stmt->close();
        }

        $sub_pct = $sub_max > 0 ? round(($sub_earned / $sub_max) * 100, 1) : 0;
        if ($sub_pct >= 85) {
            $grade = 'A (Distinction)';
            $grade_class = 'text-success';
            $badge = 'bg-success';
        } elseif ($sub_pct >= 70) {
            $grade = 'B (Good)';
            $grade_class = 'text-primary';
            $badge = 'bg-primary';
        } elseif ($sub_pct >= 50) {
            $grade = 'C (Satisfactory)';
            $grade_class = 'text-warning';
            $badge = 'bg-warning text-dark';
        } else {
            $grade = 'D (Needs Support)';
            $grade_class = 'text-danger';
            $badge = 'bg-danger';
            $low_marks_subjects[] = [
                'subject' => $sub['subject_name'],
                'percentage' => $sub_pct
            ];
        }

        $total_earned_marks += $sub_earned;
        $total_max_marks += $sub_max;

        $mark_entry = [
            'subject_id' => $sub_id,
            'subject_name' => $sub['subject_name'],
            'subject_code' => $sub['subject_code'],
            'earned_marks' => $sub_earned,
            'max_marks' => $sub_max,
            'percentage' => $sub_pct,
            'grade' => $grade,
            'grade_class' => $grade_class,
            'badge' => $badge,
            'assessments' => $assessments_list
        ];

        $data['marks'][] = $mark_entry;
        $subject_scores[$sub['subject_name']] = $sub_pct;
    }

    $overall_academic_pct = $total_max_marks > 0 ? round(($total_earned_marks / $total_max_marks) * 100, 1) : 0;
    $data['stats']['average_marks'] = $overall_academic_pct;

    if ($overall_academic_pct >= 85) {
        $data['stats']['overall_grade'] = 'A+ (Distinction)';
    } elseif ($overall_academic_pct >= 75) {
        $data['stats']['overall_grade'] = 'A (Excellent)';
    } elseif ($overall_academic_pct >= 60) {
        $data['stats']['overall_grade'] = 'B (Good)';
    } elseif ($overall_academic_pct >= 50) {
        $data['stats']['overall_grade'] = 'C (Satisfactory)';
    } else {
        $data['stats']['overall_grade'] = 'D (Needs Support)';
    }

    if (!empty($subject_scores)) {
        arsort($subject_scores);
        $best_subj = array_key_first($subject_scores);
        $worst_subj = array_key_last($subject_scores);
        $data['stats']['best_subject'] = $best_subj . " (" . $subject_scores[$best_subj] . "%)";
        $data['stats']['weakest_subject'] = $worst_subj . " (" . $subject_scores[$worst_subj] . "%)";
    }

    // Merge marks and attendance into subject summary items
    foreach ($data['attendance'] as &$att_item) {
        $sid = $att_item['subject_id'];
        foreach ($data['marks'] as $m_item) {
            if ($m_item['subject_id'] === $sid) {
                $att_item['academic_score'] = $m_item['percentage'];
                $att_item['grade'] = $m_item['grade'];
                $att_item['badge'] = $m_item['badge'];
                $att_item['assessments'] = $m_item['assessments'];
                break;
            }
        }
    }

    // 6. Fetch Timetable
    $tt_stmt = $conn->prepare("
        SELECT t.id, t.day, t.start_time, t.end_time, t.room, s.subject_name, s.subject_code, COALESCE(u.name, 'Department Faculty') AS faculty_name 
        FROM timetable t
        JOIN subjects s ON t.subject_id = s.id
        LEFT JOIN users u ON s.faculty_id = u.id
        ORDER BY FIELD(t.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), t.start_time ASC
    ");
    $tt_stmt->execute();
    $tt_res = $tt_stmt->get_result();
    while ($row = $tt_res->fetch_assoc()) {
        $data['timetable'][] = $row;
    }
    $tt_stmt->close();

    // 7. Fetch Assignments
    // 7. Fetch Assignments and student's own submissions
    $sub_map = [];
    $sub_query = $conn->prepare("SELECT assignment_id, file_path, file_name, file_size, remarks, status, submitted_at FROM assignment_submissions WHERE student_id = ?");
    $sub_query->bind_param("i", $student_id);
    $sub_query->execute();
    $sub_res = $sub_query->get_result();
    while ($sub_row = $sub_res->fetch_assoc()) {
        $sub_map[$sub_row['assignment_id']] = $sub_row;
    }
    $sub_query->close();

    $asg_stmt = $conn->prepare("
        SELECT a.id, a.title, a.description, a.due_date, a.attachment_path, a.attachment_name, s.subject_name, s.subject_code, u.name AS faculty_name
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        JOIN users u ON a.created_by = u.id
        ORDER BY a.due_date ASC
    ");
    $asg_stmt->execute();
    $asg_res = $asg_stmt->get_result();
    $pending_count = 0;
    while ($row = $asg_res->fetch_assoc()) {
        $due = new DateTime($row['due_date']);
        $today = new DateTime();
        $interval = $today->diff($due);
        $row['is_overdue'] = ($due < $today && $interval->days > 0);
        $row['days_left'] = $due >= $today ? $interval->days : -$interval->days;

        if (isset($sub_map[$row['id']])) {
            $row['my_submission'] = $sub_map[$row['id']];
            $row['is_submitted'] = true;
        } else {
            $row['my_submission'] = null;
            $row['is_submitted'] = false;
            $pending_count++;
        }

        $data['assignments'][] = $row;
    }
    $asg_stmt->close();
    $data['stats']['pending_assignments'] = $pending_count;

    // 8. Fetch Notices
    $not_stmt = $conn->prepare("
        SELECT n.id, n.title, n.content, n.created_at, u.name AS posted_by_name, u.role AS posted_by_role
        FROM notices n
        JOIN users u ON n.posted_by = u.id
        ORDER BY n.created_at DESC
        LIMIT 10
    ");
    $not_stmt->execute();
    $not_res = $not_stmt->get_result();
    while ($row = $not_res->fetch_assoc()) {
        $data['notices'][] = $row;
    }
    $not_stmt->close();

    // 9. Rule-Based Academic Analytics & Early Warning Engine
    $warnings = [];
    $recommendations = [];
    $is_at_risk = false;

    // Rule A: Low Attendance Warning
    if (!empty($low_attendance_subjects)) {
        $is_at_risk = true;
        foreach ($low_attendance_subjects as $low_att) {
            $warnings[] = "Attendance in {$low_att['subject']} ({$low_att['percentage']}%) is below the mandatory 75% threshold.";
            $needed_sessions = max(1, ceil((0.75 * $low_att['conducted'] - $low_att['present']) / 0.25));
            $recommendations[] = "Attend the next {$needed_sessions} consecutive sessions in {$low_att['subject']} to recover attendance above 75%.";
        }
    }

    // Rule B: Overall Low Attendance
    if ($overall_att_pct < 75) {
        $is_at_risk = true;
        $warnings[] = "Your overall semester attendance ({$overall_att_pct}%) is critically below the 75% eligibility criteria.";
        $recommendations[] = "Prioritize daily class attendance and submit leave medical documents if applicable to the HOD.";
    }

    // Rule C: Low Subject Marks
    if (!empty($low_marks_subjects)) {
        $is_at_risk = true;
        foreach ($low_marks_subjects as $low_m) {
            $warnings[] = "Performance in {$low_m['subject']} ({$low_m['percentage']}%) requires academic reinforcement.";
            $recommendations[] = "Schedule faculty doubt-clearing sessions and solve previous internal assessment papers for {$low_m['subject']}.";
        }
    }

    // Rule D: Assignment Submission Alert
    if ($data['stats']['pending_assignments'] > 2) {
        $warnings[] = "You have {$data['stats']['pending_assignments']} pending course assignments approaching deadlines.";
        $recommendations[] = "Complete and submit high-priority course assignments to secure continuous internal assessment scores.";
    }

    // Rule E: Punctuality & Late Deduction Alert (5 Lates = 1 Class Cut)
    if ($total_late_penalty_cuts_all > 0) {
        $is_at_risk = true;
        $warnings[] = "Punctuality Penalty Applied: {$total_late_penalty_cuts_all} class session(s) automatically cut due to {$total_late_all} cumulative late arrivals (Rule: Every 5 late classes = 1 class cut).";
        $recommendations[] = "Arrive promptly for all course lectures to prevent further automatic class deductions from your attendance.";
    } elseif ($total_late_all > 0 && ($total_late_all % 5) >= 3) {
        $rem = 5 - ($total_late_all % 5);
        $warnings[] = "Punctuality Advisory: You have accumulated {$total_late_all} late mark(s). {$rem} more late arrival(s) will trigger an automatic class deduction.";
        $recommendations[] = "Be on time for upcoming classes to protect your exam eligibility from late penalties.";
    }

    $data['stats']['is_at_risk'] = $is_at_risk;
    $data['stats']['risk_level'] = $is_at_risk ? (count($warnings) >= 3 ? 'Critical Risk' : 'Moderate Warning') : 'Optimal Standing';
    $data['stats']['warnings'] = $warnings;
    $data['stats']['recommendations'] = $recommendations;

    return $data;
}
?>

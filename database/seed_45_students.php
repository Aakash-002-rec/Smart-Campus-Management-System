<?php
/**
 * Smart Campus - 45 Students & Session Attendance Generator
 * Seeds 45 realistic students, department attendance records, and internal marks.
 */

require_once __DIR__ . '/../backend/config/db.php';

echo "=== Seeding 45 Students & Attendance Data ===\n";

$student_names = [
    ["Aakash Raja", "23CS001", "student@campus.com", "high"],
    ["Aarav Sharma", "23CS002", "aarav.sharma@campus.edu", "high"],
    ["Aditi Rao", "23CS003", "aditi.rao@campus.edu", "high"],
    ["Aditya Nair", "23CS004", "aditya.nair@campus.edu", "medium"],
    ["Ananya Iyer", "23CS005", "ananya.iyer@campus.edu", "high"],
    ["Anirudh Kulkarni", "23CS006", "anirudh.k@campus.edu", "high"],
    ["Anushka Gupta", "23CS007", "anushka.g@campus.edu", "medium"],
    ["Arjun Menon", "23CS008", "arjun.menon@campus.edu", "low"],
    ["Bhavya Patel", "23CS009", "bhavya.p@campus.edu", "high"],
    ["Chetan Reddy", "23CS010", "chetan.reddy@campus.edu", "medium"],
    ["Deepa Krishnan", "23CS011", "deepa.k@campus.edu", "high"],
    ["Devendra Singh", "23CS012", "devendra.s@campus.edu", "high"],
    ["Divya Murthy", "23CS013", "divya.m@campus.edu", "high"],
    ["Gautham Pillai", "23CS014", "gautham.p@campus.edu", "medium"],
    ["Harini Venkatesh", "23CS015", "harini.v@campus.edu", "high"],
    ["Ishaan Malhotra", "23CS016", "ishaan.m@campus.edu", "low"],
    ["Janani Sridhar", "23CS017", "janani.s@campus.edu", "high"],
    ["Karthik Subramanian", "23CS018", "karthik.s@campus.edu", "high"],
    ["Kavya Nambiar", "23CS019", "kavya.n@campus.edu", "high"],
    ["Madhav Joshi", "23CS020", "madhav.j@campus.edu", "medium"],
    ["Meera Sundaram", "23CS021", "meera.s@campus.edu", "high"],
    ["Mithun Kumar", "23CS022", "mithun.k@campus.edu", "high"],
    ["Nandini Deshmukh", "23CS023", "nandini.d@campus.edu", "high"],
    ["Naveen Balaji", "23CS024", "naveen.b@campus.edu", "low"],
    ["Niharika Sen", "23CS025", "niharika.s@campus.edu", "high"],
    ["Pranav Hegde", "23CS026", "pranav.h@campus.edu", "high"],
    ["Pooja Bhatt", "23CS027", "pooja.b@campus.edu", "medium"],
    ["Rahul Mukund", "23CS028", "rahul.m@campus.edu", "high"],
    ["Rithika Swaminathan", "23CS029", "rithika.s@campus.edu", "high"],
    ["Rohan Chatterjee", "23CS030", "rohan.c@campus.edu", "low"],
    ["Sahana Padmanabhan", "23CS031", "sahana.p@campus.edu", "high"],
    ["Sai Pradeep", "23CS032", "sai.pradeep@campus.edu", "high"],
    ["Sanjay Natarajan", "23CS033", "sanjay.n@campus.edu", "medium"],
    ["Shreya Narayanan", "23CS034", "shreya.n@campus.edu", "high"],
    ["Siddharth Varma", "23CS035", "siddharth.v@campus.edu", "high"],
    ["Sneha Raghavan", "23CS036", "sneha.r@campus.edu", "high"],
    ["Sriram Viswanathan", "23CS037", "sriram.v@campus.edu", "high"],
    ["Swathi Ramesh", "23CS038", "swathi.r@campus.edu", "low"],
    ["Tanvi Hegde", "23CS039", "tanvi.h@campus.edu", "high"],
    ["Tarun Chawla", "23CS040", "tarun.c@campus.edu", "medium"],
    ["Varun Teja", "23CS041", "varun.t@campus.edu", "high"],
    ["Vidya Shankar", "23CS042", "vidya.s@campus.edu", "high"],
    ["Vignesh Chandran", "23CS043", "vignesh.c@campus.edu", "high"],
    ["Vikram Ranganathan", "23CS044", "vikram.r@campus.edu", "high"],
    ["Yashwant Raj", "23CS045", "yashwant.r@campus.edu", "medium"]
];

$dept = "Computer Science & Engineering";
$year = 3;
$password = getenv('SEED_PASSWORD');

if (!$password) {
    die("SEED_PASSWORD is not set.\n");
}
$student_ids = [];

// 1. Insert or Update 45 Students
foreach ($student_names as $s) {
    $name = $s[0];
    $reg_no = $s[1];
    $email = $s[2];
    $perf_tier = $s[3];

    // Check if user exists by email or reg_no
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR register_no = ?");
    $stmt->bind_param("ss", $email, $reg_no);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $id = (int)$res->fetch_assoc()['id'];
        $upd = $conn->prepare("UPDATE users SET name = ?, register_no = ?, department = ?, year = ? WHERE id = ?");
        $upd->bind_param("sssii", $name, $reg_no, $dept, $year, $id);
        $upd->execute();
        $upd->close();
    } else {
        $ins = $conn->prepare("INSERT INTO users (name, register_no, email, password, role, department, year) VALUES (?, ?, ?, ?, 'student', ?, ?)");
        $ins->bind_param("sssssi", $name, $reg_no, $email, $password, $dept, $year);
        $ins->execute();
        $id = $ins->insert_id;
        $ins->close();
    }
    $stmt->close();

    $student_ids[$id] = [
        'name' => $name,
        'reg_no' => $reg_no,
        'tier' => $perf_tier
    ];
}

echo "✔ Registered/Verified 45 Student Profiles.\n";

// 2. Fetch all Subjects and Sessions
$subjects = [];
$sub_res = $conn->query("SELECT id, subject_name, subject_code, COALESCE(total_course_hours, 75) as total_course_hours FROM subjects");
while ($r = $sub_res->fetch_assoc()) {
    $subjects[$r['id']] = $r;
}

// Fetch all attendance sessions
$sessions_res = $conn->query("SELECT id, subject_id, session_date, period_number FROM attendance_sessions ORDER BY session_date ASC, period_number ASC");
$all_sessions = [];
while ($r = $sessions_res->fetch_assoc()) {
    $all_sessions[] = $r;
}

echo "✔ Found " . count($all_sessions) . " conducted attendance sessions.\n";

// 3. Populate Attendance Records for all 45 students across all sessions
$rec_stmt = $conn->prepare("
    INSERT INTO attendance_records (session_id, student_id, status) 
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE status = VALUES(status)
");

$total_attendance_inserted = 0;

foreach ($all_sessions as $sess) {
    $sess_id = (int)$sess['id'];
    $sub_id = (int)$sess['subject_id'];
    $sub_name = $subjects[$sub_id]['subject_name'] ?? '';

    foreach ($student_ids as $stu_id => $meta) {
        $tier = $meta['tier']; // high, medium, low
        $rand = rand(1, 100);

        if ($tier === 'high') {
            // ~90 - 96% attendance
            $status = ($rand <= 92) ? 'Present' : (($rand <= 96) ? 'Late' : 'Absent');
        } elseif ($tier === 'medium') {
            // ~76 - 85% attendance
            $status = ($rand <= 80) ? 'Present' : (($rand <= 88) ? 'Late' : 'Absent');
        } else {
            // ~58 - 72% attendance (At-Risk)
            $status = ($rand <= 62) ? 'Present' : (($rand <= 72) ? 'Late' : 'Absent');
        }

        $rec_stmt->bind_param("iis", $sess_id, $stu_id, $status);
        $rec_stmt->execute();
        $total_attendance_inserted++;
    }
}
$rec_stmt->close();
echo "✔ Generated {$total_attendance_inserted} daily session attendance records for all 45 students.\n";

// 4. Fetch all Assessments and Seed Student Marks for all 45 students
$ass_res = $conn->query("SELECT id, subject_id, assessment_name, max_marks FROM assessments");
$all_assessments = [];
while ($r = $ass_res->fetch_assoc()) {
    $all_assessments[] = $r;
}

$m_stmt = $conn->prepare("
    INSERT INTO student_marks (assessment_id, student_id, marks, remarks)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE marks = VALUES(marks), remarks = VALUES(remarks)
");

$total_marks_inserted = 0;

foreach ($all_assessments as $ass) {
    $ass_id = (int)$ass['id'];
    $max_m = (float)$ass['max_marks'];

    foreach ($student_ids as $stu_id => $meta) {
        $tier = $meta['tier'];

        if ($tier === 'high') {
            $pct = rand(82, 98) / 100.0;
            $remarks = $pct >= 0.90 ? "Exceptional performance" : "Very good analytical approach";
        } elseif ($tier === 'medium') {
            $pct = rand(65, 82) / 100.0;
            $remarks = "Consistent effort, keep practicing";
        } else {
            $pct = rand(40, 64) / 100.0;
            $remarks = "Needs academic counseling and extra revision";
        }

        $scored = round($max_m * $pct, 1);
        $m_stmt->bind_param("iids", $ass_id, $stu_id, $scored, $remarks);
        $m_stmt->execute();
        $total_marks_inserted++;
    }
}
$m_stmt->close();
echo "✔ Generated {$total_marks_inserted} assessment score entries for all 45 students.\n";

echo "=== 45 Students & Attendances Successfully Seeded! ===\n";
?>

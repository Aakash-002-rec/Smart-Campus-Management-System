<?php
/**
 * Smart Campus Management System - Database Upgrade & Seed Script
 * Safely updates existing database without dropping existing tables or losing test users.
 */

require_once __DIR__ . '/../backend/config/db.php';

echo "--- Smart Campus Database Upgrade Started ---\n";

// 1. Ensure `total_course_hours` column exists in `subjects` table
$check_col = $conn->query("SHOW COLUMNS FROM subjects LIKE 'total_course_hours'");
if ($check_col && $check_col->num_rows === 0) {
    $conn->query("ALTER TABLE subjects ADD COLUMN total_course_hours INT DEFAULT 75 AFTER semester");
    echo "✔ Added total_course_hours column to subjects.\n";
} else {
    echo "✔ Column total_course_hours already exists in subjects.\n";
}

// 2. Update default realistic course hours for existing subjects
$hours_map = [
    'Web Programming' => 75,
    'Database Management Systems' => 75,
    'Computer Networks' => 72,
    'Operating Systems' => 72,
    'Software Engineering' => 68,
    'Automata Theory' => 60,
    'Artificial Intelligence' => 75,
    'Data Structures & Algorithms' => 80
];

foreach ($hours_map as $sname => $hours) {
    $stmt = $conn->prepare("UPDATE subjects SET total_course_hours = ? WHERE subject_name LIKE ?");
    $search = "%" . $sname . "%";
    $stmt->bind_param("is", $hours, $search);
    $stmt->execute();
    $stmt->close();
}
echo "✔ Updated planned course hours (60-80h) for existing subjects.\n";

// 3. Create `attendance_sessions` table
$conn->query("
CREATE TABLE IF NOT EXISTS attendance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    faculty_id INT NOT NULL,
    session_date DATE NOT NULL,
    period_number INT NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    topic VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "✔ Created/Verified attendance_sessions table.\n";

// 4. Create `attendance_records` table
$conn->query("
CREATE TABLE IF NOT EXISTS attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('Present', 'Absent', 'Late') NOT NULL DEFAULT 'Present',
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_session_student (session_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "✔ Created/Verified attendance_records table.\n";

// 5. Create `assessments` table
$conn->query("
CREATE TABLE IF NOT EXISTS assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    assessment_name VARCHAR(100) NOT NULL,
    assessment_type VARCHAR(50) NOT NULL,
    max_marks DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    assessment_date DATE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "✔ Created/Verified assessments table.\n";

// 6. Create `student_marks` table
$conn->query("
CREATE TABLE IF NOT EXISTS student_marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    student_id INT NOT NULL,
    marks DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    remarks VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assessment_student (assessment_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "✔ Created/Verified student_marks table.\n";

// 7. Seed Sample Data if sessions are empty
$sess_count_res = $conn->query("SELECT COUNT(*) as cnt FROM attendance_sessions");
$sess_count = $sess_count_res ? (int)$sess_count_res->fetch_assoc()['cnt'] : 0;

if ($sess_count === 0) {
    echo "--- Seeding initial Session Attendance & Assessment Records ---\n";
    // Find faculty id
    $fac_res = $conn->query("SELECT id FROM users WHERE role = 'faculty' LIMIT 1");
    $fac_id = $fac_res && $fac_res->num_rows > 0 ? (int)$fac_res->fetch_assoc()['id'] : 2;

    // Find all students
    $students = [];
    $stu_res = $conn->query("SELECT id FROM users WHERE role = 'student'");
    if ($stu_res) {
        while ($r = $stu_res->fetch_assoc()) {
            $students[] = (int)$r['id'];
        }
    }

    // Find all subjects
    $subjects = [];
    $sub_res = $conn->query("SELECT id, subject_name FROM subjects");
    if ($sub_res) {
        while ($r = $sub_res->fetch_assoc()) {
            $subjects[] = $r;
        }
    }

    // Topics per subject
    $topics_dict = [
        'Web Programming' => ['HTML5 Semantic Layouts', 'CSS Grid & Flexbox Masterclass', 'JavaScript ES6+ Promises', 'PHP Sessions & Security', 'REST APIs with MySQL', 'React Hooks Integration', 'Form Validation & CSRF Protection', 'Bootstrap 5 Responsive Grid'],
        'Database' => ['Relational Schema Design', 'SQL Joins & Subqueries', 'Database Indexing & B-Trees', 'ACID Transactions & Locks', 'Normalization (1NF to 3NF)', 'Stored Procedures & Triggers', 'NoSQL vs Relational Databases', 'Query Optimization Strategies'],
        'Network' => ['OSI Model & TCP/IP Stack', 'Subnetting & CIDR Addressing', 'Routing Protocols (OSPF/BGP)', 'DNS & DHCP Configuration', 'Transport Layer & TCP Handshake', 'HTTP/HTTPS & SSL/TLS', 'Network Security & Firewalls', 'Packet Analysis with Wireshark'],
        'Operating' => ['Process Scheduling Algorithms', 'Memory Management & Paging', 'Virtual Memory & Page Faults', 'Deadlock Detection & Prevention', 'File Systems & Inodes', 'Concurrency & Semaphores', 'Inter-Process Communication', 'I/O Hardware & Device Drivers'],
        'Automata' => ['Deterministic Finite Automata', 'NFA to DFA Conversion', 'Regular Expressions & Pumping Lemma', 'Context-Free Grammars', 'Pushdown Automata', 'Turing Machine Basics', 'Decidability & Halting Problem', 'Chomsky Hierarchy'],
        'Software' => ['Agile Scrum Framework', 'Software Requirement Specs (SRS)', 'UML Diagrams & System Design', 'Clean Code Principles & Refactoring', 'Unit Testing & CI/CD Pipelines', 'Design Patterns (Factory/Observer)', 'Software Risk Management', 'Code Review Best Practices']
    ];

    $times = [
        1 => ['09:00:00', '10:00:00'],
        2 => ['10:00:00', '11:00:00'],
        3 => ['11:15:00', '12:15:00'],
        4 => ['13:00:00', '14:00:00'],
        5 => ['14:00:00', '15:00:00'],
        6 => ['15:15:00', '16:15:00']
    ];

    // Create 15-20 historical sessions per subject for rich realistic data
    $today = new DateTime('2026-09-23');
    foreach ($subjects as $sub) {
        $sub_id = $sub['id'];
        $sub_name = $sub['subject_name'];
        $matched_topics = ['Introduction & Course Overview', 'Fundamental Concepts', 'Core Architectures', 'Hands-on Lab Session', 'Advanced Case Studies'];
        foreach ($topics_dict as $k => $tlist) {
            if (stripos($sub_name, $k) !== false) {
                $matched_topics = $tlist;
                break;
            }
        }

        $num_sessions = rand(16, 22);
        for ($i = 1; $i <= $num_sessions; $i++) {
            $days_ago = ($num_sessions - $i) * 2;
            $sdate = clone $today;
            $sdate->modify("-{$days_ago} days");
            // Skip weekends
            if ($sdate->format('N') >= 6) {
                $sdate->modify("-2 days");
            }
            $period = rand(1, 4);
            $st = $times[$period][0];
            $et = $times[$period][1];
            $topic = $matched_topics[($i - 1) % count($matched_topics)] . " (Part " . (($i % 3) + 1) . ")";

            $stmt = $conn->prepare("INSERT INTO attendance_sessions (subject_id, faculty_id, session_date, period_number, start_time, end_time, topic) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $sdate_str = $sdate->format('Y-m-d');
            $stmt->bind_param("iisisss", $sub_id, $fac_id, $sdate_str, $period, $st, $et, $topic);
            $stmt->execute();
            $sess_id = $stmt->insert_id;
            $stmt->close();

            // Record attendance for each student
            foreach ($students as $stu_id) {
                // Determine presence probability:
                // Automata: ~70% attendance; Web Programming: ~92% attendance; Others: ~85%
                $rand = rand(1, 100);
                $is_automata = stripos($sub_name, 'Automata') !== false;
                $is_web = stripos($sub_name, 'Web') !== false;

                if ($is_automata) {
                    $status = ($rand <= 68) ? 'Present' : (($rand <= 78) ? 'Late' : 'Absent');
                } elseif ($is_web) {
                    $status = ($rand <= 92) ? 'Present' : (($rand <= 96) ? 'Late' : 'Absent');
                } else {
                    $status = ($rand <= 86) ? 'Present' : (($rand <= 92) ? 'Late' : 'Absent');
                }

                $rec_stmt = $conn->prepare("INSERT INTO attendance_records (session_id, student_id, status) VALUES (?, ?, ?)");
                $rec_stmt->bind_param("iis", $sess_id, $stu_id, $status);
                $rec_stmt->execute();
                $rec_stmt->close();
            }
        }
    }
    echo "✔ Seeded daily session attendance records for all subjects.\n";

    // Create Assessments & Student Marks
    $assessment_types = [
        ['Internal Assessment 1', 'internal', 50.00, '2026-08-10'],
        ['Internal Assessment 2', 'internal', 50.00, '2026-09-05'],
        ['Assignment 1', 'assignment', 20.00, '2026-08-20'],
        ['Assignment 2', 'assignment', 20.00, '2026-09-15'],
        ['Quiz 1', 'quiz', 10.00, '2026-08-28'],
        ['Model Exam', 'model_exam', 50.00, '2026-09-20']
    ];

    foreach ($subjects as $sub) {
        $sub_id = $sub['id'];
        $sub_name = $sub['subject_name'];
        $is_automata = stripos($sub_name, 'Automata') !== false;
        $is_web = stripos($sub_name, 'Web') !== false;

        foreach ($assessment_types as $ass) {
            $stmt = $conn->prepare("INSERT INTO assessments (subject_id, assessment_name, assessment_type, max_marks, assessment_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issdsi", $sub_id, $ass[0], $ass[1], $ass[2], $ass[3], $fac_id);
            $stmt->execute();
            $ass_id = $stmt->insert_id;
            $stmt->close();

            foreach ($students as $stu_id) {
                $max = $ass[2];
                if ($is_web) {
                    $pct = rand(82, 98) / 100.0;
                } elseif ($is_automata) {
                    $pct = rand(55, 70) / 100.0;
                } else {
                    $pct = rand(70, 92) / 100.0;
                }
                $scored = round($max * $pct, 1);
                $remarks = $pct >= 0.85 ? 'Excellent work' : ($pct >= 0.70 ? 'Good effort' : 'Needs improvement in core concepts');

                $m_stmt = $conn->prepare("INSERT INTO student_marks (assessment_id, student_id, marks, remarks) VALUES (?, ?, ?, ?)");
                $m_stmt->bind_param("iids", $ass_id, $stu_id, $scored, $remarks);
                $m_stmt->execute();
                $m_stmt->close();
            }
        }
    }
    echo "✔ Seeded assessments and student marks.\n";
}

echo "--- Smart Campus Database Upgrade Completed Successfully! ---\n";
?>

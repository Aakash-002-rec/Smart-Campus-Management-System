<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = "Student Dashboard";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/student/get_student_data.php';

$student_id = (int)$_SESSION['user_id'];
$data = getStudentFullData($conn, $student_id);
$student = $data['student'];
$stats = $data['stats'];
$subjects = $data['attendance'];
$notices = $data['notices'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-student.php';
?>

<div class="app-main">
    <!-- Topbar -->
    <header class="app-topbar px-4 px-lg-5 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">Student Dashboard</h5>
                <span class="text-white-50 small">Welcome back, <?php echo htmlspecialchars($student['name']); ?> &bull; Semester 5 (CSE)</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Switch Theme Palette">
                    <i class="bi bi-palette2 text-info"></i>
                    <span class="small fw-semibold d-none d-sm-inline">Atmosphere</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary theme-selector-menu p-2">
                    <li class="px-2 py-1 small text-white-50 text-uppercase font-monospace" style="font-size: 0.68rem;">Select Atmosphere</li>
                    <li><div class="theme-option-item" data-theme-name="midnight" onclick="setTheme('midnight')"><span class="theme-swatch-circle swatch-midnight"></span> Midnight Indigo</div></li>
                    <li><div class="theme-option-item" data-theme-name="emerald" onclick="setTheme('emerald')"><span class="theme-swatch-circle swatch-emerald"></span> Cyber Emerald</div></li>
                    <li><div class="theme-option-item" data-theme-name="purple" onclick="setTheme('purple')"><span class="theme-swatch-circle swatch-purple"></span> Cosmic Amethyst</div></li>
                    <li><div class="theme-option-item" data-theme-name="sunset" onclick="setTheme('sunset')"><span class="theme-swatch-circle swatch-sunset"></span> Ruby Sunset</div></li>
                    <li><div class="theme-option-item" data-theme-name="sapphire" onclick="setTheme('sapphire')"><span class="theme-swatch-circle swatch-sapphire"></span> Ocean Sapphire</div></li>
                    <li><div class="theme-option-item" data-theme-name="oled" onclick="setTheme('oled')"><span class="theme-swatch-circle swatch-oled"></span> Obsidian Carbon</div></li>
                    <li><div class="theme-option-item" data-theme-name="light" onclick="setTheme('light')"><span class="theme-swatch-circle swatch-light"></span> Glacier Light</div></li>
                </ul>
            </div>
            <a href="student-analytics.php" class="btn btn-outline-primary btn-sm px-3 py-1.5 rounded-pill d-none d-sm-inline-flex align-items-center gap-1">
                <i class="bi bi-cpu"></i> Analytics
            </a>
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px;">
                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo htmlspecialchars($student['name']); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small py-2" href="student-profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item small py-2" href="student-attendance.php"><i class="bi bi-calendar-check me-2"></i>Attendance Logs</a></li>
                    <li><a class="dropdown-item small py-2" href="student-marks.php"><i class="bi bi-award me-2"></i>Marks &amp; Grades</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small py-2 text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="p-4 p-lg-5 flex-grow-1">
        <!-- Top Summary Metric Cards (Spacious & Clean Layout) -->
        <div class="row g-4 mb-4">
            <!-- 1. Overall Attendance -->
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Overall Attendance</span>
                        <div class="stat-icon-circle icon-emerald">
                            <i class="bi bi-calendar-check-fill text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <h2 class="fw-bold text-white mb-0"><?php echo $stats['overall_attendance']; ?>%</h2>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-30 rounded-pill px-2.5 py-1 small">
                            <?php echo $stats['attendance_status']; ?>
                        </span>
                    </div>
                    <div class="progress mt-3 bg-secondary bg-opacity-25" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?php echo min(100, $stats['overall_attendance']); ?>%"></div>
                    </div>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Sessions Attended</span>
                        <span class="fw-semibold text-white">
                            <?php echo $stats['total_present_sessions']; ?> / <?php echo $stats['total_conducted_sessions']; ?>
                            <?php if (!empty($stats['total_late_penalty_cuts']) && $stats['total_late_penalty_cuts'] > 0): ?>
                                <span class="badge bg-warning bg-opacity-25 text-warning ms-1" style="font-size: 0.7rem;" title="<?php echo $stats['total_late_penalty_cuts']; ?> class cutted due to <?php echo $stats['total_late_sessions']; ?> late marks">-<?php echo $stats['total_late_penalty_cuts']; ?> cut</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Academic Performance -->
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Academic Score</span>
                        <div class="stat-icon-circle icon-sapphire">
                            <i class="bi bi-award-fill text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <h2 class="fw-bold text-white mb-0"><?php echo $stats['average_marks']; ?>%</h2>
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-30 rounded-pill px-2.5 py-1 small">
                            Grade <?php echo substr($stats['overall_grade'], 0, 2); ?>
                        </span>
                    </div>
                    <div class="progress mt-3 bg-secondary bg-opacity-25" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo min(100, $stats['average_marks']); ?>%"></div>
                    </div>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Top Course</span>
                        <span class="fw-semibold text-white" title="<?php echo htmlspecialchars($stats['best_subject']); ?>"><?php echo htmlspecialchars(mb_strimwidth($stats['best_subject'], 0, 22, '...')); ?></span>
                    </div>
                </div>
            </div>

            <!-- 3. Planned Course Hours Progress -->
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Course Delivery</span>
                        <div class="stat-icon-circle icon-purple">
                            <i class="bi bi-clock-history text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <h2 class="fw-bold text-white mb-0"><?php echo $stats['completed_course_hours_all']; ?> <span class="fs-6 text-white-50 fw-normal">/ <?php echo $stats['total_course_hours_all']; ?> hrs</span></h2>
                    </div>
                    <div class="progress mt-3 bg-secondary bg-opacity-25" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: <?php echo min(100, $stats['course_progress_percentage']); ?>%"></div>
                    </div>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Curriculum Progress</span>
                        <span class="fw-semibold text-white"><?php echo $stats['course_progress_percentage']; ?>% Complete</span>
                    </div>
                </div>
            </div>

            <!-- 4. Tasks & Standing -->
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Active Standing</span>
                        <div class="stat-icon-circle <?php echo $stats['is_at_risk'] ? 'icon-sunset' : 'icon-emerald'; ?>">
                            <i class="bi bi-<?php echo $stats['is_at_risk'] ? 'exclamation-triangle-fill' : 'shield-check'; ?> text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-1">
                        <h2 class="fw-bold text-white mb-0"><?php echo $stats['pending_assignments']; ?> <span class="fs-6 text-white-50 fw-normal">Tasks</span></h2>
                        <span class="badge <?php echo $stats['is_at_risk'] ? 'bg-danger bg-opacity-25 text-danger border border-danger border-opacity-30' : 'bg-success bg-opacity-25 text-success border border-success border-opacity-30'; ?> rounded-pill px-2.5 py-1 small">
                            <?php echo $stats['risk_level']; ?>
                        </span>
                    </div>
                    <div class="progress mt-3 bg-secondary bg-opacity-25" style="height: 6px;">
                        <div class="progress-bar <?php echo $stats['is_at_risk'] ? 'bg-danger' : 'bg-success'; ?>" style="width: 100%"></div>
                    </div>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Exam Eligibility</span>
                        <span class="fw-semibold <?php echo $stats['is_at_risk'] ? 'text-danger' : 'text-success'; ?>"><?php echo $stats['is_at_risk'] ? 'Alert Triggered' : 'Exam Ready'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($stats['is_at_risk']): ?>
        <!-- At-Risk Alert Banner -->
        <div class="alert alert-danger erp-card border-danger border-start border-5 d-flex align-items-center justify-content-between p-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-octagon-fill fs-2 text-danger"></i>
                <div>
                    <h6 class="fw-bold text-danger mb-1">Academic Action Required: <?php echo $stats['risk_level']; ?></h6>
                    <span class="small text-white-50"><?php echo $stats['warnings'][0] ?? 'Low attendance detected in one or more subjects.'; ?></span>
                </div>
            </div>
            <a href="student-analytics.php" class="btn btn-danger btn-sm px-4 py-2 rounded-pill text-nowrap">View Guidance &rarr;</a>
        </div>
        <?php endif; ?>

        <!-- Subject Overview Cards (Clean & Spacious Layout) -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-white mb-0">Enrolled Courses &amp; Progress</h5>
                <span class="text-white-50 small">Subject-wise session attendance, hours completed, and continuous marks</span>
            </div>
            <a href="student-attendance.php" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5">
                <i class="bi bi-list-check me-1"></i> Attendance Logs
            </a>
        </div>

        <div class="row g-4 mb-4">
            <?php foreach ($subjects as $idx => $sub): ?>
            <div class="col-lg-6 col-xxl-4">
                <div class="erp-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Header: Code Pill & Status Badge -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 px-2.5 py-1 small">
                                <?php echo htmlspecialchars($sub['subject_code']); ?> &bull; Semester <?php echo $sub['semester']; ?>
                            </span>
                            <span class="badge <?php echo $sub['badge_class']; ?> px-3 py-1.5 rounded-pill small">
                                <?php echo $sub['status']; ?>
                            </span>
                        </div>

                        <!-- Subject Title -->
                        <h5 class="fw-bold text-white mb-3" style="font-size: 1.15rem; line-height: 1.4; min-height: 2.8rem;">
                            <?php echo htmlspecialchars($sub['subject_name']); ?>
                        </h5>

                        <!-- Dual Metrics (Attendance & Internal Marks) -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-05 h-100">
                                    <span class="text-white-50 small d-block mb-1" style="font-size: 0.75rem;">Attendance Rate</span>
                                    <h4 class="fw-bold text-white mb-1"><?php echo $sub['percentage']; ?>%</h4>
                                    <span class="text-white-50 small d-block" style="font-size: 0.75rem;">
                                        <?php echo $sub['present']; ?> of <?php echo $sub['conducted']; ?> sessions
                                        <?php if (!empty($sub['late_penalty_cuts']) && $sub['late_penalty_cuts'] > 0): ?>
                                            <span class="text-warning">(-<?php echo $sub['late_penalty_cuts']; ?> cut)</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-05 h-100">
                                    <span class="text-white-50 small d-block mb-1" style="font-size: 0.75rem;">Continuous Marks</span>
                                    <h4 class="fw-bold text-primary mb-1"><?php echo $sub['academic_score'] ?? 0; ?>%</h4>
                                    <span class="text-white-50 small d-block" style="font-size: 0.75rem;">Grade: <strong class="text-white"><?php echo $sub['grade'] ?? 'N/A'; ?></strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Course Curriculum Hours Progress -->
                        <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10 mb-3">
                            <div class="d-flex justify-content-between align-items-center small mb-1.5">
                                <span class="text-white-50"><i class="bi bi-clock me-1 text-info"></i>Curriculum Delivery:</span>
                                <span class="fw-bold text-white"><?php echo $sub['completed_hours']; ?> / <?php echo $sub['total_course_hours']; ?> hrs (<?php echo $sub['course_progress_percentage']; ?>%)</span>
                            </div>
                            <div class="progress bg-black bg-opacity-30" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: <?php echo min(100, $sub['course_progress_percentage']); ?>%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-white-50 small mt-1.5" style="font-size: 0.75rem;">
                                <span>Conducted: <?php echo $sub['completed_hours']; ?>h</span>
                                <span>Remaining: <?php echo $sub['remaining_hours']; ?>h</span>
                            </div>
                        </div>
                    </div>

                    <!-- Clean Footer with Generous Padding & Zero Overlap -->
                    <div class="pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        <div>
                            <?php if ($sub['percentage'] >= 75): ?>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill small">
                                    <i class="bi bi-shield-check me-1"></i> Exam Eligible
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25 px-3 py-1.5 rounded-pill small">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Shortage Risk
                                </span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-primary btn-sm px-4 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#subjectModal<?php echo $sub['subject_id']; ?>">
                            View Details <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Subject Detail Modal -->
            <div class="modal fade" id="subjectModal<?php echo $sub['subject_id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content erp-card border-secondary text-white">
                        <div class="modal-header border-secondary border-opacity-25 p-4">
                            <div>
                                <h5 class="modal-title fw-bold text-white"><?php echo htmlspecialchars($sub['subject_name']); ?></h5>
                                <span class="text-white-50 small"><?php echo htmlspecialchars($sub['subject_code']); ?> &bull; Semester <?php echo $sub['semester']; ?> &bull; Faculty: <?php echo htmlspecialchars($sub['faculty_name'] ?? 'Assigned Faculty'); ?></span>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Course Hours Breakdown -->
                            <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10 mb-4">
                                <h6 class="fw-bold text-info mb-2"><i class="bi bi-clock-history me-2"></i>Course Curriculum Hours Progress</h6>
                                <div class="row text-center mb-2">
                                    <div class="col-4">
                                        <span class="text-white-50 small">Planned Total</span>
                                        <h5 class="fw-bold text-white mt-1"><?php echo $sub['total_course_hours']; ?> hrs</h5>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-white-50 small">Conducted</span>
                                        <h5 class="fw-bold text-success mt-1"><?php echo $sub['completed_hours']; ?> hrs</h5>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-white-50 small">Remaining</span>
                                        <h5 class="fw-bold text-warning mt-1"><?php echo $sub['remaining_hours']; ?> hrs</h5>
                                    </div>
                                </div>
                                <div class="progress bg-black bg-opacity-40" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: <?php echo min(100, $sub['course_progress_percentage']); ?>%"></div>
                                </div>
                            </div>

                            <!-- Continuous Assessments Table -->
                            <h6 class="fw-bold text-white mb-3"><i class="bi bi-award me-2 text-primary"></i>Internal Assessments &amp; Continuous Marks</h6>
                            <div class="table-responsive mb-4">
                                <table class="table erp-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Assessment Name</th>
                                            <th>Date</th>
                                            <th>Max Marks</th>
                                            <th>Marks Scored</th>
                                            <th>Percentage</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($sub['assessments'])): ?>
                                            <?php foreach ($sub['assessments'] as $ass): ?>
                                            <tr>
                                                <td class="fw-semibold text-white"><?php echo htmlspecialchars($ass['assessment_name']); ?></td>
                                                <td class="text-white-50 small" style="white-space: nowrap;"><?php echo $ass['assessment_date'] ? date('d M Y', strtotime($ass['assessment_date'])) : 'N/A'; ?></td>
                                                <td><?php echo $ass['max_marks']; ?></td>
                                                <td class="fw-bold text-primary"><?php echo $ass['scored_marks']; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $ass['percentage'] >= 75 ? 'bg-success' : ($ass['percentage'] >= 50 ? 'bg-primary' : 'bg-danger'); ?>">
                                                        <?php echo $ass['percentage']; ?>%
                                                    </span>
                                                </td>
                                                <td class="text-white-50 small"><?php echo htmlspecialchars($ass['remarks'] ?? 'Satisfactory'); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="6" class="text-center text-muted py-3">No individual assessments recorded yet.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end gap-3">
                                <a href="student-attendance.php?subject_id=<?php echo $sub['subject_id']; ?>" class="btn btn-outline-light btn-sm px-4 py-2 rounded-pill">
                                    Full Attendance History
                                </a>
                                <a href="student-marks.php?subject_id=<?php echo $sub['subject_id']; ?>" class="btn btn-primary btn-sm px-4 py-2 rounded-pill">
                                    Full Marks Breakdown
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Recent Campus Announcements -->
        <div class="erp-card p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-megaphone me-2 text-warning"></i>Recent Campus Circulars &amp; Notices</h5>
                    <span class="text-white-50 small">Official updates from college administration</span>
                </div>
                <a href="student-notices.php" class="btn btn-link text-primary text-decoration-none small fw-semibold">View All Notices &rarr;</a>
            </div>
            <div class="row g-4">
                <?php if (!empty($notices)): ?>
                    <?php foreach (array_slice($notices, 0, 3) as $n): ?>
                    <div class="col-md-4">
                        <div class="p-4 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <span class="text-white-50 small d-block mb-2" style="font-size: 0.78rem;">
                                    <i class="bi bi-clock me-1"></i><?php echo date('M d, Y', strtotime($n['created_at'])); ?> &bull; By <?php echo htmlspecialchars($n['posted_by_name']); ?>
                                </span>
                                <h6 class="fw-bold text-white mb-2"><?php echo htmlspecialchars($n['title']); ?></h6>
                                <p class="text-white-50 small mb-0 line-clamp-2"><?php echo htmlspecialchars(substr($n['content'], 0, 110)) . '...'; ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-4">No active circulars.</div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$page_title = "Faculty Dashboard";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/faculty/get_faculty_data.php';

$data = getFacultyDashboardData($conn);
$stats = $data['stats'];
$subjects = $data['subjects'];
$recent_sessions = $data['recent_sessions'];
$at_risk = $data['at_risk_students'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-faculty.php';
?>

<div class="app-main">
    <!-- Topbar -->
    <header class="app-topbar px-4 px-lg-5 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">Faculty ERP Dashboard</h5>
                <span class="text-white-50 small">Daily session attendance, continuous evaluation, and student risk management</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <a href="faculty-attendance.php" class="btn btn-primary btn-sm px-3.5 py-1.5 rounded-pill d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-calendar-plus"></i> Mark Attendance
            </a>
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px; background: #6366f1;">
                        <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small py-2" href="faculty-students.php"><i class="bi bi-people me-2"></i>Student Roster</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small py-2 text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-4 p-lg-5 flex-grow-1">
        <!-- Faculty Metric Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Assigned Subjects</span>
                        <div class="stat-icon-circle icon-sapphire">
                            <i class="bi bi-book-fill text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-1"><?php echo $stats['total_subjects']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Department Courses</span>
                        <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill px-2">Active</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Sessions Conducted</span>
                        <div class="stat-icon-circle icon-purple">
                            <i class="bi bi-clock-history text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-info mb-1"><?php echo $stats['total_sessions_conducted']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Recorded Daily Sessions</span>
                        <span class="fw-semibold text-white">507 Planned Hrs</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Enrolled Students</span>
                        <div class="stat-icon-circle icon-emerald">
                            <i class="bi bi-people-fill text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-success mb-1"><?php echo $stats['total_students']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Active Student Profiles</span>
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2">3rd Year</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">At-Risk Alerts</span>
                        <div class="stat-icon-circle icon-sunset">
                            <i class="bi bi-shield-exclamation text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-danger mb-1"><?php echo $stats['at_risk_count']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Intervention Flagged</span>
                        <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-2">Follow Up</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Subjects & Course Progress -->
        <div class="erp-card p-4 p-lg-5 mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Assigned Subjects &amp; Course Delivery Progress</h5>
                    <span class="text-white-50 small">Planned curriculum hours vs conducted lectures</span>
                </div>
                <a href="faculty-attendance.php" class="btn btn-outline-primary btn-sm rounded-pill px-3.5 py-1.5">Start New Session</a>
            </div>

            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">Subject Name</th>
                            <th style="min-width: 110px;">Code</th>
                            <th style="min-width: 120px;">Semester</th>
                            <th style="min-width: 140px;">Planned Hours</th>
                            <th style="min-width: 140px;">Conducted</th>
                            <th style="min-width: 140px;">Remaining</th>
                            <th style="min-width: 200px;">Delivery Progress</th>
                            <th style="min-width: 130px;">Quick Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $sub): ?>
                        <tr class="<?php echo !empty($sub['is_assigned']) ? 'border-start border-3 border-success' : ''; ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-white"><?php echo htmlspecialchars($sub['subject_name']); ?></span>
                                    <?php if (!empty($sub['is_assigned'])): ?>
                                        <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">Your Lead Course</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 px-2.5 py-1"><?php echo htmlspecialchars($sub['subject_code']); ?></span></td>
                            <td>Semester <?php echo $sub['semester']; ?></td>
                            <td class="fw-semibold text-white"><?php echo $sub['total_course_hours']; ?> hrs</td>
                            <td class="fw-bold text-success"><?php echo $sub['conducted_sessions']; ?> hrs</td>
                            <td class="fw-semibold text-warning"><?php echo $sub['remaining_hours']; ?> hrs</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 bg-black bg-opacity-30" style="height: 6px; min-width: 80px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo min(100, $sub['progress_percentage']); ?>%"></div>
                                    </div>
                                    <span class="small fw-bold text-white"><?php echo $sub['progress_percentage']; ?>%</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="faculty-attendance.php?subject_id=<?php echo $sub['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1" style="font-size: 0.8rem;" title="Mark Attendance">
                                        <i class="bi bi-calendar-check me-1"></i>Mark
                                    </a>
                                    <a href="faculty-marks.php?subject_id=<?php echo $sub['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1" style="font-size: 0.8rem;" title="Enter Marks">
                                        <i class="bi bi-award me-1"></i>Marks
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Attendance Sessions -->
            <div class="col-lg-7">
                <div class="erp-card p-4 p-lg-5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-white mb-0"><i class="bi bi-clock-history me-2 text-info"></i>Recent Attendance Sessions</h5>
                            <span class="text-white-50 small">Latest recorded classroom sessions</span>
                        </div>
                        <a href="faculty-attendance.php" class="btn btn-link text-primary text-decoration-none small fw-semibold">All Logs &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table erp-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 140px;">Date &amp; Period</th>
                                    <th style="min-width: 180px;">Subject</th>
                                    <th style="min-width: 180px;">Topic</th>
                                    <th style="min-width: 140px;">Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_sessions)): ?>
                                    <?php foreach (array_slice($recent_sessions, 0, 5) as $s): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-white d-block"><?php echo date('d M Y', strtotime($s['session_date'])); ?></span>
                                            <span class="text-white-50 small">Period <?php echo $s['period_number']; ?></span>
                                        </td>
                                        <td class="fw-semibold text-white"><?php echo htmlspecialchars($s['subject_name']); ?></td>
                                        <td class="text-white-50 small"><?php echo htmlspecialchars($s['topic']); ?></td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-30 px-3 py-1.5 rounded-pill">
                                                <?php echo $s['present_count']; ?> / <?php echo $s['total_students_marked']; ?> Present
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">No sessions logged yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- At-Risk Early Intervention Students -->
            <div class="col-lg-5">
                <div class="erp-card p-4 p-lg-5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-white mb-0"><i class="bi bi-shield-exclamation me-2 text-danger"></i>At-Risk Students</h5>
                            <span class="text-white-50 small">Shortage / performance alerts</span>
                        </div>
                        <a href="faculty-students.php" class="btn btn-link text-danger text-decoration-none small fw-semibold">View Roster &rarr;</a>
                    </div>
                    <div class="list-group list-group-flush bg-transparent">
                        <?php if (!empty($at_risk)): ?>
                            <?php foreach (array_slice($at_risk, 0, 4) as $stu): ?>
                            <div class="list-group-item bg-transparent text-white px-0 py-3 border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-white d-block"><?php echo htmlspecialchars($stu['name']); ?></span>
                                    <span class="text-white-50 small"><?php echo htmlspecialchars($stu['register_no'] ?? ''); ?> &bull; Attendance: <strong class="text-white"><?php echo $stu['overall_attendance']; ?>%</strong></span>
                                </div>
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-30 rounded-pill px-3 py-1.5 small">
                                    <?php echo $stu['risk_level']; ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">No students currently flagged as at-risk.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Admin Dashboard";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/admin/get_admin_data.php';

$data = getAdminDashboardData($conn);
$stats = $data['stats'];
$students = $data['students'];
$faculty = $data['faculty'];
$subjects = $data['subjects'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-admin.php';
?>

<div class="app-main">
    <!-- Topbar -->
    <header class="app-topbar px-4 px-lg-5 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">System Administration Overview</h5>
                <span class="text-white-50 small">Campus-wide statistics, department rosters, and academic metrics</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <a href="admin-students.php" class="btn btn-danger btn-sm px-3.5 py-1.5 rounded-pill shadow-sm d-flex align-items-center gap-1.5">
                <i class="bi bi-person-plus"></i> Add Student
            </a>
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px;">
                        <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small py-2" href="admin-reports.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>Reports</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small py-2 text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-4 p-lg-5 flex-grow-1">
        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Total Students</span>
                        <div class="stat-icon-circle icon-sapphire">
                            <i class="bi bi-people-fill text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-1"><?php echo $stats['total_students']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Enrolled in Courses</span>
                        <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill px-2"><?php echo $stats['total_students']; ?> Active</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Faculty Members</span>
                        <div class="stat-icon-circle icon-purple">
                            <i class="bi bi-person-badge-fill text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-1"><?php echo $stats['total_faculty']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Department Faculty</span>
                        <span class="badge bg-info bg-opacity-25 text-info rounded-pill px-2">Teaching Staff</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Curriculum Subjects</span>
                        <div class="stat-icon-circle icon-emerald">
                            <i class="bi bi-book-half text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-1"><?php echo $stats['total_subjects']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Planned Course Delivery</span>
                        <span class="fw-semibold text-white">507 Total Hours</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">At-Risk Interventions</span>
                        <div class="stat-icon-circle icon-sunset">
                            <i class="bi bi-shield-exclamation text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-danger mb-1"><?php echo $stats['campus_at_risk_count']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Alerts Detected</span>
                        <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-2">Follow Up</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Students -->
            <div class="col-lg-6">
                <div class="erp-card p-4 p-lg-5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-white mb-0"><i class="bi bi-people me-2 text-primary"></i>Student Directory Preview</h5>
                            <span class="text-white-50 small">Enrolled engineering students</span>
                        </div>
                        <a href="admin-students.php" class="btn btn-link text-primary text-decoration-none small fw-semibold">Manage Students &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table erp-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 120px;">Register No</th>
                                    <th style="min-width: 180px;">Student Name</th>
                                    <th style="min-width: 130px;">Attendance</th>
                                    <th style="min-width: 120px;">Standing</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($students, 0, 5) as $stu): ?>
                                <tr>
                                    <td class="fw-bold text-white"><?php echo htmlspecialchars($stu['register_no'] ?? '21CS101'); ?></td>
                                    <td>
                                        <span class="fw-semibold text-white d-block"><?php echo htmlspecialchars($stu['name']); ?></span>
                                        <span class="text-white-50 small"><?php echo htmlspecialchars($stu['department'] ?? 'CSE'); ?></span>
                                    </td>
                                    <td class="fw-bold <?php echo $stu['overall_attendance'] >= 75 ? 'text-success' : 'text-danger'; ?>"><?php echo $stu['overall_attendance']; ?>%</td>
                                    <td>
                                        <span class="badge <?php echo $stu['is_at_risk'] ? 'bg-danger bg-opacity-25 text-danger border border-danger border-opacity-30' : 'bg-success bg-opacity-25 text-success border border-success border-opacity-30'; ?> rounded-pill px-3 py-1">
                                            <?php echo $stu['risk_level']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Faculty Directory -->
            <div class="col-lg-6">
                <div class="erp-card p-4 p-lg-5 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-white mb-0"><i class="bi bi-person-badge me-2 text-info"></i>Faculty Directory Preview</h5>
                            <span class="text-white-50 small">Department academic instructors</span>
                        </div>
                        <a href="admin-faculty.php" class="btn btn-link text-info text-decoration-none small fw-semibold">Manage Faculty &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table erp-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 160px;">Faculty Name</th>
                                    <th style="min-width: 200px;">Institutional Email</th>
                                    <th style="min-width: 160px;">Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($faculty, 0, 5) as $fac): ?>
                                <tr>
                                    <td class="fw-bold text-white"><?php echo htmlspecialchars($fac['name']); ?></td>
                                    <td class="text-white-50 small"><?php echo htmlspecialchars($fac['email']); ?></td>
                                    <td><span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 px-2.5 py-1"><?php echo htmlspecialchars($fac['department'] ?? 'Computer Science'); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

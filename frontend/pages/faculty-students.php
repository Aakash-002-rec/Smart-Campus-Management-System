<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$page_title = "Student Roster & Risk Directory";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/faculty/get_faculty_data.php';

$data = getFacultyDashboardData($conn);
$students = $data['students'];

$total_students = count($students);
$at_risk_count = count(array_filter($students, function($s) { return $s['is_at_risk']; }));
$good_standing_count = $total_students - $at_risk_count;
$avg_attendance = $total_students > 0 ? round(array_sum(array_column($students, 'overall_attendance')) / $total_students, 1) : 0;

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-faculty.php';
?>

<div class="app-main">
    <header class="app-topbar px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">Student Roster &amp; Early Intervention</h5>
                <span class="text-white-50 small">Track attendance compliance (&ge;75%), continuous marks, and at-risk students</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-2 rounded-pill d-none d-sm-inline-block">
                <i class="bi bi-people-fill me-1"></i> <?php echo $total_students; ?> Enrolled Students
            </span>
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px; background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small" href="faculty-dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item small" href="faculty-attendance.php"><i class="bi bi-calendar-check me-2"></i>Attendance Sheet</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-4 flex-grow-1">
        <!-- Top Metrics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small fw-medium">Total Enrolled</span>
                        <div class="p-2 rounded-3 bg-primary bg-opacity-20 text-primary">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-0"><?php echo $total_students; ?></h2>
                    <span class="text-white-50 small mt-2 d-block">Department of CSE (Year 3)</span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small fw-medium">Good Standing</span>
                        <div class="p-2 rounded-3 bg-success bg-opacity-20 text-success">
                            <i class="bi bi-shield-check fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-success mb-0"><?php echo $good_standing_count; ?></h2>
                    <span class="text-white-50 small mt-2 d-block">Attendance &ge; 75% &bull; Eligible</span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small fw-medium">At-Risk Students</span>
                        <div class="p-2 rounded-3 bg-danger bg-opacity-20 text-danger">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-danger mb-0"><?php echo $at_risk_count; ?></h2>
                    <span class="text-white-50 small mt-2 d-block">Shortage / Low Academic Score</span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small fw-medium">Class Attendance Mean</span>
                        <div class="p-2 rounded-3 bg-info bg-opacity-20 text-info">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-0"><?php echo $avg_attendance; ?>%</h2>
                    <span class="text-white-50 small mt-2 d-block">Across all conducted sessions</span>
                </div>
            </div>
        </div>

        <!-- Student Directory Table Card -->
        <div class="erp-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Enrolled Student Directory (<?php echo count($students); ?> Students)</h5>
                    <span class="text-white-50 small">Live attendance computations and early intervention alerts</span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active filter-pill" onclick="filterRisk('all', this)">All (<?php echo $total_students; ?>)</button>
                        <button type="button" class="btn btn-outline-success filter-pill" onclick="filterRisk('good', this)">Good Standing (<?php echo $good_standing_count; ?>)</button>
                        <button type="button" class="btn btn-outline-danger filter-pill" onclick="filterRisk('risk', this)">At-Risk (<?php echo $at_risk_count; ?>)</button>
                    </div>
                    <input type="text" id="rosterSearch" class="form-control form-control-sm bg-dark text-white border-secondary rounded-pill px-3" placeholder="Search by name or reg no..." style="min-width: 220px;" onkeyup="searchRoster()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0" id="rosterTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Department</th>
                            <th>Overall Attendance</th>
                            <th>Academic Score</th>
                            <th>Standing</th>
                            <th>Guidance / Warnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $stu): ?>
                        <?php 
                            $initials = strtoupper(substr($stu['name'], 0, 1));
                            $is_risk = $stu['is_at_risk'];
                        ?>
                        <tr class="roster-row" data-risk="<?php echo $is_risk ? 'risk' : 'good'; ?>">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; background: <?php echo $is_risk ? 'linear-gradient(135deg, #ef4444, #b91c1c)' : 'linear-gradient(135deg, #3b82f6, #6366f1)'; ?>; font-size: 0.9rem;">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div>
                                        <span class="fw-semibold text-white d-block"><?php echo htmlspecialchars($stu['name']); ?></span>
                                        <span class="text-white-50 small"><?php echo htmlspecialchars($stu['email']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-white"><?php echo htmlspecialchars($stu['register_no'] ?? '23CS001'); ?></td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 text-white-50"><?php echo htmlspecialchars($stu['department'] ?? 'CSE'); ?> &bull; Year <?php echo $stu['year'] ?? 3; ?></span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold <?php echo $stu['overall_attendance'] >= 75 ? 'text-success' : 'text-danger'; ?>"><?php echo $stu['overall_attendance']; ?>%</span>
                                    <span class="badge <?php echo $stu['overall_attendance'] >= 75 ? 'bg-success' : 'bg-danger'; ?> rounded-pill small"><?php echo $stu['attendance_status']; ?></span>
                                </div>
                                <div class="progress mt-1 bg-black bg-opacity-30" style="height: 4px; width: 90px;">
                                    <div class="progress-bar <?php echo $stu['overall_attendance'] >= 75 ? 'bg-success' : 'bg-danger'; ?>" style="width: <?php echo min(100, $stu['overall_attendance']); ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-primary"><?php echo $stu['average_marks']; ?>%</span>
                                <span class="text-white-50 small d-block">Grade: <?php echo $stu['overall_grade']; ?></span>
                            </td>
                            <td>
                                <span class="badge <?php echo $is_risk ? 'bg-danger' : 'bg-success'; ?> px-3 py-1 rounded-pill">
                                    <?php echo $stu['risk_level']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($is_risk && !empty($stu['warnings'])): ?>
                                    <span class="small text-danger d-block text-truncate" style="max-width: 260px;" title="<?php echo htmlspecialchars($stu['warnings'][0]); ?>">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i><?php echo htmlspecialchars($stu['warnings'][0]); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="small text-success"><i class="bi bi-check-circle-fill me-1"></i>Good standing &bull; Exam Eligible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
let currentFilter = 'all';

function filterRisk(type, btn) {
    currentFilter = type;
    document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilters();
}

function searchRoster() {
    applyFilters();
}

function applyFilters() {
    const input = document.getElementById('rosterSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.roster-row');

    rows.forEach(row => {
        const riskType = row.getAttribute('data-risk');
        const text = row.textContent.toLowerCase();
        
        const matchesFilter = (currentFilter === 'all') || (riskType === currentFilter);
        const matchesSearch = !input || text.includes(input);

        row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


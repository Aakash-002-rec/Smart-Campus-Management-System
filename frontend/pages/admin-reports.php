<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Academic Audit Reports";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/admin/get_admin_data.php';

$data = getAdminDashboardData($conn);
$students = $data['students'];
$subjects = $data['subjects'];

$total_audited = count($students);
$eligible_count = count(array_filter($students, function($s) { return $s['overall_attendance'] >= 75; }));
$shortage_count = $total_audited - $eligible_count;
$avg_perf = $total_audited > 0 ? round(array_sum(array_column($students, 'average_marks')) / $total_audited, 1) : 0;

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-admin.php';
?>

<div class="app-main">
    <header class="app-topbar px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">Consolidated Academic Reports &amp; Audit</h5>
                <span class="text-white-50 small">Comprehensive institutional audit of attendance compliance and examination metrics</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-light btn-sm px-3 rounded-pill" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print / Export Report
            </button>
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px;">
                        <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small" href="admin-dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-4 flex-grow-1">
        <!-- Top Institutional Audit Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small fw-medium">Audited Students</span>
                        <div class="p-2 rounded-3 bg-primary bg-opacity-20 text-primary">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-0"><?php echo $total_audited; ?></h2>
                    <span class="text-white-50 small mt-2 d-block">Department of CSE &bull; Year 3</span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small fw-medium">End-Sem Exam Eligible</span>
                        <div class="p-2 rounded-3 bg-success bg-opacity-20 text-success">
                            <i class="bi bi-patch-check-fill fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-success mb-0"><?php echo $eligible_count; ?></h2>
                    <span class="text-white-50 small mt-2 d-block">Attendance &ge; 75% threshold</span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small fw-medium">Attendance Shortage</span>
                        <div class="p-2 rounded-3 bg-danger bg-opacity-20 text-danger">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-danger mb-0"><?php echo $shortage_count; ?></h2>
                    <span class="text-white-50 small mt-2 d-block">Requires condonation / intervention</span>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small fw-medium">Batch Marks Mean</span>
                        <div class="p-2 rounded-3 bg-info bg-opacity-20 text-info">
                            <i class="bi bi-award-fill fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-white mb-0"><?php echo $avg_perf; ?>%</h2>
                    <span class="text-white-50 small mt-2 d-block">Continuous internal assessments</span>
                </div>
            </div>
        </div>

        <div class="erp-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <div>
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-file-earmark-bar-graph-fill me-2 text-primary"></i>Student Performance Audit Table (<?php echo $total_audited; ?> Records)</h5>
                    <span class="text-white-50 small">Institutional record of student attendance compliance (&ge; 75%) and continuous assessment results</span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active audit-filter" onclick="filterAudit('all', this)">All (<?php echo $total_audited; ?>)</button>
                        <button type="button" class="btn btn-outline-success audit-filter" onclick="filterAudit('eligible', this)">Eligible (<?php echo $eligible_count; ?>)</button>
                        <button type="button" class="btn btn-outline-danger audit-filter" onclick="filterAudit('shortage', this)">Shortage (<?php echo $shortage_count; ?>)</button>
                    </div>
                    <input type="text" id="reportSearch" class="form-control form-control-sm bg-dark text-white border-secondary rounded-pill px-3" placeholder="Search report..." style="min-width: 200px;" onkeyup="searchReport()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0" id="reportTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Department</th>
                            <th>Overall Attendance</th>
                            <th>Academic Score</th>
                            <th>Exam Eligibility</th>
                            <th>Risk Assessment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $stu): ?>
                        <?php 
                            $initials = strtoupper(substr($stu['name'], 0, 1));
                            $is_eligible = $stu['overall_attendance'] >= 75;
                        ?>
                        <tr class="report-row" data-eligibility="<?php echo $is_eligible ? 'eligible' : 'shortage'; ?>">
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; background: <?php echo $is_eligible ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'; ?>; font-size: 0.85rem;">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div>
                                        <span class="fw-semibold text-white d-block"><?php echo htmlspecialchars($stu['name']); ?></span>
                                        <span class="text-white-50 small"><?php echo htmlspecialchars($stu['email']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-white"><?php echo htmlspecialchars($stu['register_no'] ?? '23CS001'); ?></td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-white-50"><?php echo htmlspecialchars($stu['department'] ?? 'CSE'); ?></span></td>
                            <td>
                                <span class="fw-bold <?php echo $is_eligible ? 'text-success' : 'text-danger'; ?>"><?php echo $stu['overall_attendance']; ?>%</span>
                                <div class="progress mt-1 bg-black bg-opacity-30" style="height: 4px; width: 80px;">
                                    <div class="progress-bar <?php echo $is_eligible ? 'bg-success' : 'bg-danger'; ?>" style="width: <?php echo min(100, $stu['overall_attendance']); ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-primary"><?php echo $stu['average_marks']; ?>%</span>
                                <span class="text-white-50 small d-block">Grade: <?php echo $stu['overall_grade']; ?></span>
                            </td>
                            <td>
                                <?php if ($is_eligible): ?>
                                    <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i>Eligible</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-exclamation-triangle me-1"></i>Shortage</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo $stu['is_at_risk'] ? 'bg-danger' : 'bg-success'; ?> rounded-pill">
                                    <?php echo $stu['risk_level']; ?>
                                </span>
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
let currentAuditFilter = 'all';

function filterAudit(type, btn) {
    currentAuditFilter = type;
    document.querySelectorAll('.audit-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyReportFilters();
}

function searchReport() {
    applyReportFilters();
}

function applyReportFilters() {
    const input = document.getElementById('reportSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.report-row');

    rows.forEach(row => {
        const elType = row.getAttribute('data-eligibility');
        const text = row.textContent.toLowerCase();
        
        const matchesFilter = (currentAuditFilter === 'all') || (elType === currentAuditFilter);
        const matchesSearch = !input || text.includes(input);

        row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


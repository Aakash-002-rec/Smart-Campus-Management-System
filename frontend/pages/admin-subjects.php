<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Subjects";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/admin/get_admin_data.php';

$admin_data = getAdminDashboardData($conn);
$subjects = $admin_data['subjects'];
$faculty_list = $admin_data['faculty'];

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
                <h5 class="fw-bold mb-0 text-white">Curriculum Courses &amp; Faculty Assignments</h5>
                <span class="text-white-50 small">Configure 7 department courses, faculty leads, credit hours (60-80h), and curriculum progress</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-success btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                <i class="bi bi-plus-circle me-1"></i> Add New Subject
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
        <div class="erp-card p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-book-half me-2 text-success"></i>Academic Subjects &amp; Faculty Allocations (<?php echo count($subjects); ?>)</h5>
                <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 px-3 py-1.5 rounded-pill">
                    Semester 5 &bull; 7 Core Subjects
                </span>
            </div>
            
            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Code</th>
                            <th>Assigned Faculty Lead</th>
                            <th>Semester</th>
                            <th>Planned Hours</th>
                            <th>Conducted</th>
                            <th>Remaining</th>
                            <th>Delivery Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $s): ?>
                        <tr>
                            <td class="fw-bold text-white"><?php echo htmlspecialchars($s['subject_name']); ?></td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 px-2 py-1"><?php echo htmlspecialchars($s['subject_code']); ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 26px; height: 26px; background: #6366f1;">
                                        <i class="bi bi-person-fill" style="font-size: 0.75rem;"></i>
                                    </div>
                                    <span class="fw-semibold text-info"><?php echo htmlspecialchars($s['faculty_name']); ?></span>
                                </div>
                            </td>
                            <td><span class="badge bg-primary bg-opacity-20 text-primary">Sem <?php echo $s['semester']; ?></span></td>
                            <td class="fw-bold text-white"><?php echo $s['total_course_hours']; ?> hrs</td>
                            <td class="text-success fw-bold"><?php echo $s['conducted_sessions']; ?> hrs</td>
                            <td class="text-warning fw-bold"><?php echo $s['remaining_hours']; ?> hrs</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 bg-black bg-opacity-30" style="height: 6px; width: 80px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo min(100, $s['progress_pct']); ?>%"></div>
                                    </div>
                                    <span class="small fw-semibold text-white"><?php echo $s['progress_pct']; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Subject Modal -->
        <div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content erp-card border-secondary text-white">
                    <div class="modal-header border-secondary border-opacity-25">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-book-plus me-2 text-success"></i>Add Curriculum Subject</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addSubjectForm">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Subject / Course Name</label>
                                <input type="text" name="subject_name" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. Distributed Systems Architecture" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Subject Code</label>
                                <input type="text" name="subject_code" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. CS508" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Assigned Faculty Lead</label>
                                <select name="faculty_id" class="form-select bg-dark text-white border-secondary rounded-3">
                                    <option value="">-- Select Faculty Lead --</option>
                                    <?php foreach ($faculty_list as $fac): ?>
                                    <option value="<?php echo $fac['id']; ?>"><?php echo htmlspecialchars($fac['name']); ?> (<?php echo htmlspecialchars($fac['department'] ?? 'CSE'); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Department</label>
                                <input type="text" name="department" class="form-control bg-dark text-white border-secondary rounded-3" value="Computer Science & Engineering" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small text-white-50">Semester</label>
                                    <select name="semester" class="form-select bg-dark text-white border-secondary rounded-3">
                                        <option value="1">Semester 1</option>
                                        <option value="2">Semester 2</option>
                                        <option value="3">Semester 3</option>
                                        <option value="4">Semester 4</option>
                                        <option value="5" selected>Semester 5</option>
                                        <option value="6">Semester 6</option>
                                        <option value="7">Semester 7</option>
                                        <option value="8">Semester 8</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-white-50">Planned Hours (60-80h)</label>
                                    <input type="number" name="total_course_hours" class="form-control bg-dark text-white border-secondary rounded-3" value="75" min="30" max="150" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary border-opacity-25">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="addSubjectBtn" class="btn btn-success btn-sm rounded-pill px-4">Save Subject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('addSubjectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('addSubjectBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    const formData = new FormData(this);
    try {
        const res = await fetch('../../backend/admin/add_subject.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.status === 'success') {
            showToast('Course added to curriculum successfully!', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast(result.message || 'Error adding subject.', 'danger');
            btn.disabled = false;
            btn.innerHTML = 'Save Subject';
        }
    } catch (err) {
        showToast('Network error.', 'danger');
        btn.disabled = false;
        btn.innerHTML = 'Save Subject';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Students";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/admin/get_admin_data.php';

$data = getAdminDashboardData($conn);
$students = $data['students'];

$at_risk_count = 0;
$good_count = 0;
foreach ($students as $s) {
    if (!empty($s['is_at_risk'])) $at_risk_count++;
    else $good_count++;
}

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
                <h5 class="fw-bold mb-0 text-white">Student Enrollment &amp; Directory</h5>
                <span class="text-white-50 small">Manage all <?php echo count($students); ?> enrolled students, credentials, and academic statuses</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-danger btn-sm px-3 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                <i class="bi bi-person-plus-fill me-1"></i> Register Student
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
        <!-- Quick Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="erp-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small">Total Enrolled</span>
                        <h3 class="fw-bold text-white mb-0 mt-1"><?php echo count($students); ?></h3>
                    </div>
                    <div class="p-3 rounded-3 bg-primary bg-opacity-20 text-primary">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="erp-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small">Good Standing</span>
                        <h3 class="fw-bold text-success mb-0 mt-1"><?php echo $good_count; ?></h3>
                    </div>
                    <div class="p-3 rounded-3 bg-success bg-opacity-20 text-success">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="erp-card p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small">At-Risk Interventions</span>
                        <h3 class="fw-bold text-danger mb-0 mt-1"><?php echo $at_risk_count; ?></h3>
                    </div>
                    <div class="p-3 rounded-3 bg-danger bg-opacity-20 text-danger">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Roster Table -->
        <div class="erp-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 filter-pill active" onclick="filterByStanding('all')">
                        All (<?php echo count($students); ?>)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 filter-pill" onclick="filterByStanding('good')">
                        Good Standing (<?php echo $good_count; ?>)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 filter-pill" onclick="filterByStanding('risk')">
                        At-Risk (<?php echo $at_risk_count; ?>)
                    </button>
                </div>
                <input type="text" id="adminStudentSearch" class="form-control form-control-sm bg-dark text-white border-secondary rounded-pill px-3" placeholder="Search by name, reg no, or email..." style="max-width: 300px;" onkeyup="searchStudents()">
            </div>

            <div class="table-responsive" style="max-height: 650px; overflow-y: auto;">
                <table class="table erp-table align-middle mb-0" id="studentTable">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Department</th>
                            <th>Attendance</th>
                            <th>Academic Score</th>
                            <th>Exam Eligibility</th>
                            <th>Standing</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $avatar_colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'];
                        foreach ($students as $idx => $stu): 
                            $c = $avatar_colors[$idx % count($avatar_colors)];
                            $initials = strtoupper(substr($stu['name'], 0, 1));
                        ?>
                        <tr class="student-row" id="student-row-<?php echo $stu['id']; ?>" data-risk="<?php echo !empty($stu['is_at_risk']) ? 'risk' : 'good'; ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 36px; height: 36px; background: <?php echo $c; ?>; font-size: 0.9rem;">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div>
                                        <span class="fw-semibold text-white d-block"><?php echo htmlspecialchars($stu['name']); ?></span>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="text-white-50 small" style="font-size: 0.75rem;"><?php echo htmlspecialchars($stu['email']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-white fw-bold"><?php echo htmlspecialchars($stu['register_no'] ?? '23CS' . str_pad($idx+1, 3, '0', STR_PAD_LEFT)); ?></span></td>
                            <td><span class="text-white-50 small"><?php echo htmlspecialchars($stu['department'] ?? 'CSE'); ?> &bull; Y<?php echo $stu['year'] ?? 3; ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 bg-black bg-opacity-30" style="height: 5px; width: 60px;">
                                        <div class="progress-bar <?php echo $stu['overall_attendance'] >= 75 ? 'bg-success' : 'bg-danger'; ?>" style="width: <?php echo min(100, $stu['overall_attendance']); ?>%"></div>
                                    </div>
                                    <span class="fw-bold small <?php echo $stu['overall_attendance'] >= 75 ? 'text-success' : 'text-danger'; ?>"><?php echo $stu['overall_attendance']; ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-primary"><?php echo $stu['average_marks']; ?>%</span>
                            </td>
                            <td>
                                <?php if ($stu['overall_attendance'] >= 75): ?>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-2 py-1 small">
                                        <i class="bi bi-check-circle me-1"></i> Eligible
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30 rounded-pill px-2 py-1 small">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Shortage
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo !empty($stu['is_at_risk']) ? 'bg-danger' : 'bg-success'; ?> rounded-pill px-3 py-1">
                                    <?php echo $stu['risk_level']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-outline-danger btn-sm rounded-pill px-2 py-1" onclick="deleteUser(<?php echo $stu['id']; ?>, '<?php echo htmlspecialchars(addslashes($stu['name'])); ?>', '<?php echo htmlspecialchars(addslashes($stu['register_no'] ?? '')); ?>', '<?php echo htmlspecialchars(addslashes($stu['department'] ?? 'CSE')); ?>')" title="Remove Student">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Student Modal -->
        <div class="modal fade" id="addStudentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content erp-card border-secondary text-white">
                    <div class="modal-header border-secondary border-opacity-25">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-person-plus me-2 text-danger"></i>Register New Student</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addStudentForm">
                        <div class="modal-body p-4">
                            <input type="hidden" name="role" value="student">
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Full Name</label>
                                <input type="text" name="name" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. Sanya Mehta" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Register / Roll Number</label>
                                <input type="text" name="register_no" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. 23CS046" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Email Address</label>
                                <input type="email" name="email" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="student@campus.edu" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Password</label>
                                <input type="password" name="password" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="Enter password" required>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-8">
                                    <label class="form-label small text-white-50">Department</label>
                                    <input type="text" name="department" class="form-control bg-dark text-white border-secondary rounded-3" value="Computer Science & Engineering" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small text-white-50">Year</label>
                                    <select name="year" class="form-select bg-dark text-white border-secondary rounded-3">
                                        <option value="1">1st Year</option>
                                        <option value="2">2nd Year</option>
                                        <option value="3" selected>3rd Year</option>
                                        <option value="4">4th Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary border-opacity-25">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="addStuBtn" class="btn btn-danger btn-sm rounded-pill px-4">Register Student</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
let currentStandingFilter = 'all';

function filterByStanding(type) {
    currentStandingFilter = type;
    document.querySelectorAll('.filter-pill').forEach(el => {
        el.classList.remove('active', 'btn-primary', 'btn-success', 'btn-danger');
        el.classList.add('btn-outline-secondary');
    });

    event.currentTarget.classList.remove('btn-outline-secondary');
    if (type === 'all') event.currentTarget.classList.add('active', 'btn-primary');
    else if (type === 'good') event.currentTarget.classList.add('active', 'btn-success');
    else event.currentTarget.classList.add('active', 'btn-danger');

    applyFilters();
}

function searchStudents() {
    applyFilters();
}

function applyFilters() {
    const search = document.getElementById('adminStudentSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.student-row');

    rows.forEach(r => {
        const text = r.textContent.toLowerCase();
        const risk = r.getAttribute('data-risk');

        const matchSearch = text.includes(search);
        const matchStanding = (currentStandingFilter === 'all') || (currentStandingFilter === risk);

        r.style.display = (matchSearch && matchStanding) ? '' : 'none';
    });
}

document.getElementById('addStudentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('addStuBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    const formData = new FormData(this);
    try {
        const res = await fetch('../../backend/admin/add_user.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.status === 'success') {
            showToast('Student registered successfully!', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast(result.message || 'Error registering student.', 'danger');
            btn.disabled = false;
            btn.innerHTML = 'Register Student';
        }
    } catch (err) {
        showToast('Network error.', 'danger');
        btn.disabled = false;
        btn.innerHTML = 'Register Student';
    }
});

async function deleteUser(id, name, regNo = '', dept = '') {
    const metaInfo = (regNo ? `${regNo} • ` : '') + (dept || 'CSE') + ' • Year 3';
    
    // Call the ultra-interactive confirmation box
    const confirmed = await showInteractiveConfirm({
        title: 'Remove Student',
        subtitle: `Are you sure you want to remove this student from the campus database?`,
        name: name,
        role: 'Enrolled Student',
        meta: metaInfo,
        impact1: 'Student academic records, attendance history, and exam marks will be permanently removed.',
        impact2: 'Credentials will be immediately revoked and cannot be recovered.',
        confirmText: 'Remove Student',
        cancelText: 'Keep Student'
    });

    if (!confirmed) return;

    const row = document.getElementById(`student-row-${id}`);
    const formData = new FormData();
    formData.append('user_id', id);

    try {
        const res = await fetch('../../backend/admin/delete_user.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.status === 'success') {
            showToast(`${name} has been removed successfully.`, 'success');
            
            // Smoothly dissolve the deleted row with tactile animation
            if (row) {
                row.classList.add('student-row-deleting');
                setTimeout(() => {
                    row.remove();
                    // Update counters in stats card if present
                    document.querySelectorAll('h2.fw-bold').forEach(el => {
                        const val = parseInt(el.textContent.trim());
                        if (!isNaN(val) && val > 0) {
                            el.textContent = val - 1;
                        }
                    });
                }, 520);
            } else {
                setTimeout(() => window.location.reload(), 800);
            }
        } else {
            showToast(result.message || 'Error deleting user.', 'danger');
        }
    } catch (e) {
        showToast('Network communication error.', 'danger');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Faculty";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/admin/get_admin_data.php';

$data = getAdminDashboardData($conn);
$faculty = $data['faculty'];

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
                <h5 class="fw-bold mb-0 text-white">Faculty Directory &amp; Course Allocations</h5>
                <span class="text-white-50 small">Manage 7 faculty leads, credentials, departments, and course responsibilities</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-info text-dark fw-bold btn-sm px-3 rounded-pill" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                <i class="bi bi-person-badge-fill me-1"></i> Register Faculty
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
                <h5 class="fw-bold text-white mb-0"><i class="bi bi-person-workspace me-2 text-info"></i>Faculty Members &amp; Assigned Subjects (<?php echo count($faculty); ?>)</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Faculty Name</th>
                            <th>Email Address</th>
                            <th>Department</th>
                            <th>Assigned Core Subject</th>
                            <th>Role Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faculty as $fac): ?>
                        <tr id="faculty-row-<?php echo $fac['id']; ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6, #6366f1);">
                                        <?php echo strtoupper(substr($fac['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-white d-block"><?php echo htmlspecialchars($fac['name']); ?></span>
                                        <span class="text-white-50 small">ID: #<?php echo $fac['id']; ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-white-50 small font-monospace"><?php echo htmlspecialchars($fac['email']); ?></td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-white-50"><?php echo htmlspecialchars($fac['department'] ?? 'Computer Science'); ?></span></td>
                            <td>
                                <?php if (!empty($fac['assigned_subjects'])): ?>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill">
                                        <i class="bi bi-journal-bookmark me-1"></i><?php echo htmlspecialchars($fac['assigned_subjects']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-20 text-white-50">No Course Assigned</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">Faculty Lead</span></td>
                            <td>
                                <button class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1" onclick="deleteUser(<?php echo $fac['id']; ?>, '<?php echo htmlspecialchars(addslashes($fac['name'])); ?>', '<?php echo htmlspecialchars(addslashes($fac['department'] ?? 'Computer Science')); ?>')" title="Remove Faculty">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Faculty Modal -->
        <div class="modal fade" id="addFacultyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content erp-card border-secondary text-white">
                    <div class="modal-header border-secondary border-opacity-25">
                        <h5 class="modal-title fw-bold text-white"><i class="bi bi-person-plus me-2 text-info"></i>Register Faculty Member</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addFacultyForm">
                        <div class="modal-body p-4">
                            <input type="hidden" name="role" value="faculty">
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Faculty Name</label>
                                <input type="text" name="name" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. Dr. Priya Sundaram" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Faculty Email</label>
                                <input type="email" name="email" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="faculty@campus.edu" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Password</label>
                                <input type="password" name="password" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="Enter password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-white-50">Department</label>
                                <input type="text" name="department" class="form-control bg-dark text-white border-secondary rounded-3" value="Computer Science & Engineering" required>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary border-opacity-25">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="addFacBtn" class="btn btn-info text-dark fw-bold btn-sm rounded-pill px-4">Register Faculty</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('addFacultyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('addFacBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    const formData = new FormData(this);
    try {
        const res = await fetch('../../backend/admin/add_user.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.status === 'success') {
            showToast('Faculty member registered successfully!', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast(result.message || 'Error registering faculty.', 'danger');
            btn.disabled = false;
            btn.innerHTML = 'Register Faculty';
        }
    } catch (err) {
        showToast('Network error.', 'danger');
        btn.disabled = false;
        btn.innerHTML = 'Register Faculty';
    }
});

async function deleteUser(id, name, dept = 'Computer Science') {
    const confirmed = await showInteractiveConfirm({
        title: 'Remove Faculty Member',
        subtitle: `Are you sure you want to remove this faculty lead from the directory?`,
        name: name,
        role: 'Faculty Lead',
        meta: `${dept} • ID: #${id}`,
        impact1: 'Assigned courses will be unassigned and pending marks/evaluations removed.',
        impact2: 'Faculty portal credentials will be permanently deactivated.',
        confirmText: 'Remove Faculty',
        cancelText: 'Keep Faculty'
    });

    if (!confirmed) return;

    const row = document.getElementById(`faculty-row-${id}`);
    const formData = new FormData();
    formData.append('user_id', id);

    try {
        const res = await fetch('../../backend/admin/delete_user.php', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.status === 'success') {
            showToast(`${name} removed successfully.`, 'success');
            if (row) {
                row.classList.add('faculty-row-deleting');
                setTimeout(() => {
                    row.remove();
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

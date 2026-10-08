<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Assignments";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/faculty/get_faculty_data.php';

$data = getFacultyDashboardData($conn);
$subjects = $data['subjects'];

// Fetch assignments created by faculty with submission counts and attachments
$asg_res = $conn->query("
    SELECT a.id, a.title, a.description, a.due_date, s.subject_name, s.subject_code, a.created_at,
           a.attachment_path, a.attachment_name,
           (SELECT COUNT(*) FROM assignment_submissions sub WHERE sub.assignment_id = a.id) AS submission_count
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    ORDER BY a.due_date DESC
");
$assignments = [];
if ($asg_res) {
    while ($row = $asg_res->fetch_assoc()) {
        $assignments[] = $row;
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-faculty.php';
?>

<style>
.file-upload-box {
    border: 2px dashed rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.03);
    padding: 18px;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
}
.file-upload-box:hover {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.08);
}
.file-upload-box input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.selected-file-pill {
    display: none;
    align-items: center;
    justify-content: space-between;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.4);
    border-radius: 50px;
    padding: 8px 16px;
    margin-top: 10px;
}
</style>

<div class="app-main">
    <header class="app-topbar px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">Course Assignments &amp; Projects</h5>
                <span class="text-white-50 small">Publish tasks, set deadlines, and manage student submissions</span>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle bg-indigo text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px; background: #6366f1;">
                    <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                </div>
                <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                <li><a class="dropdown-item small" href="faculty-dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li><hr class="dropdown-divider border-secondary"></li>
                <li><a class="dropdown-item small text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </header>

    <main class="p-4 flex-grow-1">
        <div class="row g-4">
            <!-- Create Assignment Form -->
            <div class="col-lg-5">
                <div class="erp-card p-4">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-journal-plus me-2 text-primary"></i>Create New Assignment</h5>
                    <p class="text-white-50 small mb-3 pb-3 border-bottom border-secondary border-opacity-25">Publish new homework or project tasks for your enrolled courses</p>

                    <form id="createAsgForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small fw-semibold">Course / Subject</label>
                            <select name="subject_id" class="form-select bg-dark text-white border-secondary rounded-pill px-3.5" required>
                                <?php foreach ($subjects as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['subject_name']); ?> (<?php echo htmlspecialchars($s['subject_code']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small fw-semibold">Assignment Title</label>
                            <input type="text" name="title" class="form-control bg-dark text-white border-secondary rounded-pill px-3.5" placeholder="e.g. Unit 3 - Relational Schema Normalization" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small fw-semibold">Submission Due Date</label>
                            <input type="date" name="due_date" class="form-control bg-dark text-white border-secondary rounded-pill px-3.5" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white-50 small fw-semibold">Description &amp; Instructions</label>
                            <textarea name="description" rows="3" class="form-control bg-dark text-white border-secondary rounded-4 px-3.5 py-3" placeholder="Provide problem statement, guidelines, and submission format..." required></textarea>
                        </div>

                        <!-- 50MB File/Photo Upload Box -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label text-white-50 small fw-semibold mb-0">Attach Problem Sheet / Photos</label>
                                <span class="badge bg-secondary bg-opacity-50 text-white-50 small rounded-pill">Limit: 50 MB</span>
                            </div>
                            
                            <div class="file-upload-box" id="fileUploadBox">
                                <input type="file" name="attachment" id="attachmentInput" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.webp,.gif,.zip,.rar" onchange="handleFileSelect(this)">
                                <i class="bi bi-cloud-arrow-up text-primary fs-3 d-block mb-1"></i>
                                <span class="text-white small fw-semibold d-block">Click or drag file/photo here</span>
                                <span class="text-white-50" style="font-size: 0.75rem;">PDF, Images (JPG, PNG, WEBP), Word Documents up to 50 MB</span>
                            </div>

                            <!-- Selected File Chip Display -->
                            <div class="selected-file-pill" id="selectedFilePill">
                                <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                    <i class="bi bi-file-earmark-check text-success fs-5"></i>
                                    <div class="text-truncate">
                                        <span class="text-white small fw-semibold d-block text-truncate" id="selectedFileName">filename.pdf</span>
                                        <span class="text-white-50" style="font-size: 0.72rem;" id="selectedFileSize">0 MB</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearSelectedFile()" title="Remove File">
                                    <i class="bi bi-x-circle fs-5"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="createAsgBtn" class="btn btn-primary w-100 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Post Assignment
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Assignments List -->
            <div class="col-lg-7">
                <div class="erp-card p-4">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-list-task me-2 text-info"></i>Active Course Assignments</h5>
                    <p class="text-white-50 small mb-3">All assignments published for students and their submission records</p>

                    <div class="table-responsive">
                        <table class="table erp-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Subject &amp; Title</th>
                                    <th>Attachment</th>
                                    <th>Due Date</th>
                                    <th class="text-center">Submissions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($assignments)): ?>
                                    <?php foreach ($assignments as $a): ?>
                                    <?php 
                                        $due = new DateTime($a['due_date']);
                                        $today = new DateTime();
                                        $is_past = ($due < $today);
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-25 text-white-50 mb-1"><?php echo htmlspecialchars($a['subject_code']); ?></span>
                                            <span class="fw-bold text-white d-block"><?php echo htmlspecialchars($a['title']); ?></span>
                                            <span class="text-white-50 small"><?php echo htmlspecialchars(substr($a['description'], 0, 50)) . (strlen($a['description']) > 50 ? '...' : ''); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($a['attachment_path'])): ?>
                                                <a href="../../<?php echo htmlspecialchars($a['attachment_path']); ?>" target="_blank" download class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1 small" title="<?php echo htmlspecialchars($a['attachment_name']); ?>">
                                                    <i class="bi bi-paperclip me-1"></i>
                                                    <span style="max-width: 90px; display: inline-block; overflow: hidden; text-overflow: ellipsis; vertical-align: bottom;">
                                                        <?php echo htmlspecialchars($a['attachment_name'] ?: 'File'); ?>
                                                    </span>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-white-50 small">&mdash;</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-white-50 small d-block"><?php echo date('d M Y', strtotime($a['due_date'])); ?></span>
                                            <?php if ($is_past): ?>
                                                <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Closed</span>
                                            <?php else: ?>
                                                <span class="badge bg-success rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="viewSubmissions(<?php echo $a['id']; ?>, '<?php echo htmlspecialchars(addslashes($a['title'])); ?>')">
                                                <i class="bi bi-people me-1"></i>
                                                <span>Submissions (<?php echo (int)$a['submission_count']; ?>)</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">No assignments posted yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Faculty View Submissions Modal -->
<div class="modal fade" id="submissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content erp-card border-secondary text-white shadow-lg">
            <div class="modal-header border-secondary border-opacity-25">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalAsgTitle">Student Submissions</h5>
                    <span class="text-white-50 small" id="modalSubCount">0 Submissions received</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="modalLoadingSpinner" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <span class="d-block text-white-50 small mt-2">Loading student submission documents...</span>
                </div>
                <div class="table-responsive" id="submissionsTableWrapper" style="display: none;">
                    <table class="table erp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Submitted Document / File</th>
                                <th>Size</th>
                                <th>Date &amp; Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="submissionsTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle 50 MB client-side check & preview
function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;

    const maxBytes = 50 * 1024 * 1024; // 50 MB
    if (file.size > maxBytes) {
        showToast('File size (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB) exceeds the 50 MB maximum limit!', 'danger');
        input.value = '';
        document.getElementById('selectedFilePill').style.display = 'none';
        return;
    }

    const sizeStr = file.size > 1048576 
        ? (file.size / 1048576).toFixed(2) + ' MB' 
        : (file.size / 1024).toFixed(1) + ' KB';

    document.getElementById('selectedFileName').textContent = file.name;
    document.getElementById('selectedFileSize').textContent = sizeStr + ' • Ready to upload';
    document.getElementById('selectedFilePill').style.display = 'flex';
}

function clearSelectedFile() {
    document.getElementById('attachmentInput').value = '';
    document.getElementById('selectedFilePill').style.display = 'none';
}

// Post assignment with file attachment
document.getElementById('createAsgForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('createAsgBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Posting Assignment & Uploading...';

    const formData = new FormData(this);

    try {
        const response = await fetch('../../backend/faculty/create_assignment.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            showToast(result.message || 'Assignment posted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            showToast(result.message || 'Failed to create assignment.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Post Assignment';
        }
    } catch (err) {
        showToast('Network error while uploading assignment.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Post Assignment';
    }
});

// View Submissions Modal Loader
async function viewSubmissions(assignmentId, title) {
    const modalEl = document.getElementById('submissionsModal');
    const modal = new bootstrap.Modal(modalEl);
    document.getElementById('modalAsgTitle').textContent = 'Submissions: ' + title;
    document.getElementById('modalLoadingSpinner').style.display = 'block';
    document.getElementById('submissionsTableWrapper').style.display = 'none';
    modal.show();

    try {
        const res = await fetch('../../backend/faculty/get_submissions.php?assignment_id=' + assignmentId);
        const data = await res.json();

        document.getElementById('modalLoadingSpinner').style.display = 'none';
        document.getElementById('submissionsTableWrapper').style.display = 'block';

        if (data.status === 'success') {
            document.getElementById('modalSubCount').textContent = data.total_submissions + ' Submissions received';
            const tbody = document.getElementById('submissionsTableBody');
            tbody.innerHTML = '';

            if (data.submissions.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No student submissions received for this assignment yet.</td></tr>';
            } else {
                data.submissions.forEach(sub => {
                    const tr = document.createElement('tr');
                    const statusBadge = sub.status === 'Late' 
                        ? '<span class="badge bg-danger rounded-pill px-2 py-1">Late</span>'
                        : '<span class="badge bg-success rounded-pill px-2 py-1">Submitted</span>';

                    tr.innerHTML = `
                        <td>
                            <span class="fw-bold text-white d-block">${escapeHtml(sub.student_name)}</span>
                            <span class="text-white-50 small">${escapeHtml(sub.register_no)} • ${escapeHtml(sub.department || 'CSE')}</span>
                        </td>
                        <td>
                            <a href="../../${escapeHtml(sub.file_path)}" target="_blank" download class="btn btn-sm btn-outline-success rounded-pill px-3">
                                <i class="bi bi-download me-1"></i> ${escapeHtml(sub.file_name)}
                            </a>
                        </td>
                        <td class="text-white-50 small">${sub.size_formatted}</td>
                        <td class="text-white-50 small">${sub.formatted_date}</td>
                        <td>${statusBadge}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        } else {
            document.getElementById('submissionsTableBody').innerHTML = `<tr><td colspan="5" class="text-danger text-center py-3">${data.message || 'Error loading submissions'}</td></tr>`;
        }
    } catch (e) {
        document.getElementById('modalLoadingSpinner').style.display = 'none';
        document.getElementById('submissionsTableWrapper').style.display = 'block';
        document.getElementById('submissionsTableBody').innerHTML = '<tr><td colspan="5" class="text-danger text-center py-3">Server connection error.</td></tr>';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function(m) {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[m];
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

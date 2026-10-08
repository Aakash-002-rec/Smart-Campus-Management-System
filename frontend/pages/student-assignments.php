<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = "Course Assignments";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/student/get_student_data.php';

$student_id = (int)$_SESSION['user_id'];
$data = getStudentFullData($conn, $student_id);
$assignments = $data['assignments'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-student.php';
?>

<style>
.submission-dropzone {
    border: 2px dashed rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.03);
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
}
.submission-dropzone:hover {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.08);
}
.submission-dropzone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.file-selected-box {
    display: none;
    align-items: center;
    justify-content: space-between;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.4);
    border-radius: 50px;
    padding: 10px 18px;
    margin-top: 12px;
}
.faculty-attachment-box {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 12px 16px;
    transition: all 0.25s ease;
}
.faculty-attachment-box:hover {
    border-color: rgba(99, 102, 241, 0.5);
    background: rgba(99, 102, 241, 0.08);
}
.submitted-card-badge {
    background: rgba(16, 185, 129, 0.12);
    border: 1px solid rgba(16, 185, 129, 0.35);
    border-radius: 12px;
    padding: 12px 16px;
}
</style>

<div class="app-main">
    <!-- Topbar -->
    <header class="app-topbar px-4 px-lg-5 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">Course Assignments &amp; Projects</h5>
                <span class="text-white-50 small">Download instructions, view reference materials, and upload your submissions</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-3.5 py-2 rounded-pill d-none d-md-inline-block fw-semibold">
                Pending: <?php echo $data['stats']['pending_assignments']; ?> of <?php echo count($assignments); ?>
            </span>
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px;">
                        <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small py-2" href="student-dashboard.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item small py-2" href="student-profile.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small py-2 text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-4 p-lg-5 flex-grow-1">
        <div class="row g-4">
            <?php if (!empty($assignments)): ?>
                <?php foreach ($assignments as $asg): ?>
                <?php 
                    $is_submitted = !empty($asg['is_submitted']);
                    $my_sub = $asg['my_submission'] ?? null;
                ?>
                <div class="col-lg-6">
                    <div class="erp-card p-4 p-lg-5 h-100 d-flex flex-column justify-content-between position-relative border-start border-4 <?php echo $is_submitted ? 'border-success' : ($asg['is_overdue'] ? 'border-danger' : 'border-warning'); ?>">
                        <div>
                            <!-- Header Tags -->
                            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                                <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 px-2.5 py-1">
                                    <?php echo htmlspecialchars($asg['subject_name']); ?> (<?php echo htmlspecialchars($asg['subject_code']); ?>)
                                </span>

                                <div class="d-flex gap-2">
                                    <?php if ($is_submitted): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="bi bi-check2-circle me-1"></i>Submitted
                                        </span>
                                    <?php elseif ($asg['is_overdue']): ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-1.5">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Past Due Date
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="bi bi-hourglass-split me-1"></i><?php echo $asg['days_left']; ?> Days Left
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h5 class="fw-bold text-white mb-2" style="font-size: 1.25rem;"><?php echo htmlspecialchars($asg['title']); ?></h5>
                            <p class="text-white-50 small mb-4" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($asg['description'])); ?></p>

                            <!-- Faculty Reference Material / Photo / PDF Attachment -->
                            <?php if (!empty($asg['attachment_path'])): ?>
                            <div class="faculty-attachment-box mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-25 text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="bi bi-file-earmark-arrow-down fs-5"></i>
                                    </div>
                                    <div>
                                        <span class="d-block text-white fw-semibold small text-truncate" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($asg['attachment_name'] ?: 'Reference Material'); ?>
                                        </span>
                                        <span class="text-white-50" style="font-size: 0.72rem;">Faculty Reference File / Problem Sheet</span>
                                    </div>
                                </div>
                                <a href="../../<?php echo htmlspecialchars($asg['attachment_path']); ?>" target="_blank" download class="btn btn-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-download me-1"></i> View / Download
                                </a>
                            </div>
                            <?php endif; ?>

                            <!-- Student's Existing Submission Status -->
                            <?php if ($is_submitted && $my_sub): ?>
                            <div class="submitted-card-badge mb-4">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-patch-check-fill text-success fs-5"></i>
                                        <div>
                                            <span class="d-block text-white fw-semibold small"><?php echo htmlspecialchars($my_sub['file_name']); ?></span>
                                            <span class="text-white-50" style="font-size: 0.72rem;">Submitted on <?php echo date('d M Y, h:i A', strtotime($my_sub['submitted_at'])); ?></span>
                                        </div>
                                    </div>
                                    <a href="../../<?php echo htmlspecialchars($my_sub['file_path']); ?>" target="_blank" download class="btn btn-outline-success btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i> View My File
                                    </a>
                                </div>
                                <?php if (!empty($my_sub['remarks'])): ?>
                                <p class="text-white-50 small mb-0 fst-italic">Note: <?php echo htmlspecialchars($my_sub['remarks']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Footer / Action Button -->
                        <div class="pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="text-white-50 small">
                                <div><i class="bi bi-person-fill text-warning me-1"></i>Faculty: <strong class="text-white"><?php echo htmlspecialchars($asg['faculty_name']); ?></strong></div>
                                <div><i class="bi bi-calendar-check text-info me-1"></i>Due: <strong class="text-white"><?php echo date('d M Y', strtotime($asg['due_date'])); ?></strong></div>
                            </div>

                            <div>
                                <button type="button" class="btn <?php echo $is_submitted ? 'btn-outline-warning' : 'btn-success'; ?> btn-sm rounded-pill px-3.5 py-2 shadow-sm" onclick="openUploadModal(<?php echo $asg['id']; ?>, '<?php echo htmlspecialchars(addslashes($asg['title'])); ?>', <?php echo $is_submitted ? 'true' : 'false'; ?>)">
                                    <i class="bi <?php echo $is_submitted ? 'bi-arrow-repeat' : 'bi-upload'; ?> me-1"></i>
                                    <span><?php echo $is_submitted ? 'Resubmit / Update File' : 'Upload Submission (PDF/Photos)'; ?></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="erp-card p-5 text-center text-muted">
                        <i class="bi bi-journal-check fs-1 d-block mb-2 text-white-50"></i>
                        <h6 class="fw-bold text-white">No pending assignments at this time!</h6>
                        <span class="small text-white-50">All course coursework is currently up to date.</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Student Upload Submission Modal (Limit: 50 MB) -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content erp-card border-secondary text-white shadow-lg">
            <div class="modal-header border-secondary border-opacity-25">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="uploadModalTitle">Upload Assignment Submission</h5>
                    <span class="text-white-50 small" id="uploadModalSubtitle">Upload your PDF or photo documents (Max limit: 50 MB)</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="studentUploadForm" enctype="multipart/form-data">
                <input type="hidden" name="assignment_id" id="uploadAssignmentId">
                
                <div class="modal-body p-4">
                    <!-- 50 MB File Dropzone -->
                    <div class="mb-3">
                        <label class="form-label text-white-50 small fw-semibold">Choose Document / PDF / Photo</label>
                        <div class="submission-dropzone" id="dropzoneBox">
                            <input type="file" name="submission_file" id="submissionFileInput" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp,.zip" required onchange="handleStudentFile(this)">
                            <i class="bi bi-file-earmark-arrow-up text-success fs-2 d-block mb-1"></i>
                            <span class="text-white small fw-bold d-block">Click or Drop PDF / Photos Here</span>
                            <span class="text-white-50" style="font-size: 0.75rem;">Supported formats: PDF, Images (JPG, PNG), Word Docs • Max: 50 MB</span>
                        </div>

                        <!-- Selected File Preview -->
                        <div class="file-selected-box" id="studentFilePill">
                            <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                <i class="bi bi-file-earmark-check-fill text-success fs-5"></i>
                                <div class="text-truncate">
                                    <span class="text-white small fw-semibold d-block text-truncate" id="studentFileName">document.pdf</span>
                                    <span class="text-white-50" style="font-size: 0.72rem;" id="studentFileSize">0 MB</span>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="clearStudentFile()" title="Cancel">
                                <i class="bi bi-x-circle fs-5"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-white-50 small fw-semibold">Submission Remarks (Optional)</label>
                        <textarea name="remarks" rows="2" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="Add any notes or explanations for the faculty..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-dark border border-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitUploadBtn" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload &amp; Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let uploadModalInstance = null;

function openUploadModal(assignmentId, title, isResubmit) {
    document.getElementById('uploadAssignmentId').value = assignmentId;
    document.getElementById('uploadModalTitle').textContent = isResubmit ? 'Resubmit Assignment' : 'Upload Assignment Submission';
    document.getElementById('uploadModalSubtitle').textContent = title + ' (50 MB Limit)';
    clearStudentFile();
    
    if (!uploadModalInstance) {
        uploadModalInstance = new bootstrap.Modal(document.getElementById('uploadModal'));
    }
    uploadModalInstance.show();
}

function handleStudentFile(input) {
    const file = input.files[0];
    if (!file) return;

    const maxBytes = 50 * 1024 * 1024; // 50 MB
    if (file.size > maxBytes) {
        showToast('File size (' + (file.size / (1024 * 1024)).toFixed(1) + ' MB) exceeds the 50 MB limit! Please choose a file under 50 MB.', 'danger');
        input.value = '';
        document.getElementById('studentFilePill').style.display = 'none';
        return;
    }

    const sizeStr = file.size > 1048576 
        ? (file.size / 1048576).toFixed(2) + ' MB' 
        : (file.size / 1024).toFixed(1) + ' KB';

    document.getElementById('studentFileName').textContent = file.name;
    document.getElementById('studentFileSize').textContent = sizeStr + ' • Ready to submit';
    document.getElementById('studentFilePill').style.display = 'flex';
}

function clearStudentFile() {
    document.getElementById('submissionFileInput').value = '';
    document.getElementById('studentFilePill').style.display = 'none';
}

// Student Upload Submission Form Handler
document.getElementById('studentUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitUploadBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading File (50 MB limit)...';

    const formData = new FormData(this);

    try {
        const response = await fetch('../../backend/student/submit_assignment.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            showToast(result.message || 'Assignment submitted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            showToast(result.message || 'Failed to submit assignment.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Upload & Submit';
        }
    } catch (err) {
        showToast('Network error during file upload.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Upload & Submit';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

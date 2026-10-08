<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$page_title = "Post Notices";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/faculty/get_faculty_data.php';

// Fetch notices
$not_res = $conn->query("
    SELECT n.id, n.title, n.content, n.created_at, u.name AS posted_by_name
    FROM notices n
    JOIN users u ON n.posted_by = u.id
    ORDER BY n.created_at DESC
");
$notices = [];
if ($not_res) {
    while ($row = $not_res->fetch_assoc()) {
        $notices[] = $row;
    }
}

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
                <h5 class="fw-bold mb-0 text-white">Department Notices &amp; Circulars</h5>
                <span class="text-white-50 small">Issue academic announcements and exam schedules for students</span>
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

    <main class="p-4 flex-grow-1">
        <div class="row g-4">
            <!-- Notice Post Form -->
            <div class="col-lg-5">
                <div class="erp-card p-4">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-broadcast-pin me-2 text-warning"></i>Broadcast New Notice</h5>
                    <p class="text-white-50 small mb-3 pb-3 border-bottom border-secondary border-opacity-25">Publish important circulars directly to student dashboards</p>

                    <form id="createNoticeForm">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small fw-semibold">Notice Title</label>
                            <input type="text" name="title" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="e.g. Schedule for Internal Assessment II" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-white-50 small fw-semibold">Notice Content &amp; Details</label>
                            <textarea name="content" rows="5" class="form-control bg-dark text-white border-secondary rounded-3" placeholder="Write full notice description..." required></textarea>
                        </div>

                        <button type="submit" id="postNoticeBtn" class="btn btn-warning text-dark fw-bold w-100 py-2 rounded-pill shadow-sm">
                            <i class="bi bi-send-fill me-1"></i> Broadcast Circular
                        </button>
                    </form>
                </div>
            </div>

            <!-- Existing Notices List -->
            <div class="col-lg-7">
                <div class="erp-card p-4">
                    <h5 class="fw-bold text-white mb-1"><i class="bi bi-megaphone me-2 text-info"></i>Published Circulars</h5>
                    <p class="text-white-50 small mb-3">Recent announcements broadcast to the portal</p>

                    <div class="d-flex flex-column gap-3">
                        <?php if (!empty($notices)): ?>
                            <?php foreach ($notices as $n): ?>
                            <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-white-50 small" style="font-size: 0.72rem;">
                                        <i class="bi bi-clock me-1"></i><?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?> &bull; By <?php echo htmlspecialchars($n['posted_by_name']); ?>
                                    </span>
                                </div>
                                <h6 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($n['title']); ?></h6>
                                <p class="text-white-50 small mb-0"><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">No circulars broadcast yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('createNoticeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('postNoticeBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Posting...';

    const formData = new FormData(this);

    try {
        const response = await fetch('../../backend/faculty/post_notice.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            showToast('Notice broadcast successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            showToast(result.message || 'Failed to broadcast notice.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Broadcast Circular';
        }
    } catch (err) {
        showToast('Network error.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Broadcast Circular';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

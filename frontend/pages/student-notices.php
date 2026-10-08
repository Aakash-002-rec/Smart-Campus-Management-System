<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = "Campus Notices";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/student/get_student_data.php';

$student_id = (int)$_SESSION['user_id'];
$data = getStudentFullData($conn, $student_id);
$notices = $data['notices'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-student.php';
?>

<div class="app-main">
    <!-- Topbar -->
    <header class="app-topbar px-4 px-lg-5 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">Campus Circulars &amp; Notices</h5>
                <span class="text-white-50 small">Official administrative and academic announcements</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Switch Theme Palette">
                    <i class="bi bi-palette2 text-info"></i>
                    <span class="small fw-semibold d-none d-sm-inline">Atmosphere</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary theme-selector-menu p-2">
                    <li class="px-2 py-1 small text-white-50 text-uppercase font-monospace" style="font-size: 0.68rem;">Select Atmosphere</li>
                    <li><div class="theme-option-item" data-theme-name="midnight" onclick="setTheme('midnight')"><span class="theme-swatch-circle swatch-midnight"></span> Midnight Indigo</div></li>
                    <li><div class="theme-option-item" data-theme-name="emerald" onclick="setTheme('emerald')"><span class="theme-swatch-circle swatch-emerald"></span> Cyber Emerald</div></li>
                    <li><div class="theme-option-item" data-theme-name="purple" onclick="setTheme('purple')"><span class="theme-swatch-circle swatch-purple"></span> Cosmic Amethyst</div></li>
                    <li><div class="theme-option-item" data-theme-name="sunset" onclick="setTheme('sunset')"><span class="theme-swatch-circle swatch-sunset"></span> Ruby Sunset</div></li>
                    <li><div class="theme-option-item" data-theme-name="sapphire" onclick="setTheme('sapphire')"><span class="theme-swatch-circle swatch-sapphire"></span> Ocean Sapphire</div></li>
                    <li><div class="theme-option-item" data-theme-name="oled" onclick="setTheme('oled')"><span class="theme-swatch-circle swatch-oled"></span> Obsidian Carbon</div></li>
                    <li><div class="theme-option-item" data-theme-name="light" onclick="setTheme('light')"><span class="theme-swatch-circle swatch-light"></span> Glacier Light</div></li>
                </ul>
            </div>
            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-3.5 py-2 rounded-pill d-none d-md-inline-block fw-semibold">
                <?php echo count($notices); ?> Announcements
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
            <?php if (!empty($notices)): ?>
                <?php foreach ($notices as $n): ?>
                <div class="col-lg-6">
                    <div class="erp-card p-4 p-lg-5 h-100 position-relative border-start border-4 border-warning d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-secondary bg-opacity-25 text-white-50 small border border-white border-opacity-10 px-2.5 py-1">
                                    <i class="bi bi-clock me-1"></i><?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?>
                                </span>
                                <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 rounded-pill px-3 py-1">
                                    <?php echo ucfirst(htmlspecialchars($n['posted_by_role'])); ?> Notice
                                </span>
                            </div>
                            <h5 class="fw-bold text-white mb-2" style="font-size: 1.2rem;"><?php echo htmlspecialchars($n['title']); ?></h5>
                            <p class="text-white-50 small mb-4" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
                        </div>
                        
                        <div class="pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center text-white-50 small">
                            <span><i class="bi bi-person-fill text-warning me-1"></i>Issued by: <strong class="text-white"><?php echo htmlspecialchars($n['posted_by_name']); ?></strong></span>
                            <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill"><i class="bi bi-shield-check me-1"></i>Official</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="erp-card p-5 text-center text-muted">
                        <i class="bi bi-megaphone fs-1 d-block mb-2 text-white-50"></i>
                        <h6 class="fw-bold text-white">No active circulars at this time.</h6>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

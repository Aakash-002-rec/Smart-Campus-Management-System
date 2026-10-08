<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = "Smart Analytics";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/student/get_student_data.php';

$student_id = (int)$_SESSION['user_id'];
$data = getStudentFullData($conn, $student_id);
$stats = $data['stats'];
$subjects = $data['attendance'];

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
                <h5 class="fw-bold mb-0 text-white">Rule-Based Academic Analytics</h5>
                <span class="text-white-50 small">Continuous academic telemetry and early intervention alerts</span>
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
            <a href="../../react-dashboard/index.html" target="_blank" class="btn btn-primary btn-sm px-3.5 py-1.5 rounded-pill d-none d-sm-inline-flex align-items-center gap-2">
                <i class="bi bi-cpu-fill"></i> React Visualizer
            </a>
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
        <!-- Status Banner -->
        <div class="erp-card p-4 p-lg-5 mb-4 border-start border-5 <?php echo $stats['is_at_risk'] ? 'border-danger' : 'border-success'; ?>">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon-circle <?php echo $stats['is_at_risk'] ? 'icon-sunset' : 'icon-emerald'; ?>" style="width: 56px; height: 56px; font-size: 1.8rem;">
                        <i class="bi bi-<?php echo $stats['is_at_risk'] ? 'exclamation-octagon-fill' : 'shield-check'; ?> text-white"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-1">Academic Status: <span class="<?php echo $stats['is_at_risk'] ? 'text-danger' : 'text-success'; ?>"><?php echo $stats['risk_level']; ?></span></h4>
                        <p class="text-white-50 small mb-0">
                            Based on session attendance threshold (&ge; 75%) and continuous internal assessments (&ge; 50%).
                        </p>
                    </div>
                </div>
                <span class="badge <?php echo $stats['is_at_risk'] ? 'bg-danger bg-opacity-25 text-danger border border-danger border-opacity-30' : 'bg-success bg-opacity-25 text-success border border-success border-opacity-30'; ?> px-4 py-2.5 rounded-pill fs-6 fw-semibold">
                    <?php echo $stats['is_at_risk'] ? 'Intervention Recommended' : 'Optimal Good Standing'; ?>
                </span>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- 1. Risk Indicators & Warnings -->
            <div class="col-lg-6">
                <div class="erp-card p-4 p-lg-5 h-100">
                    <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-slash-fill text-danger"></i> Detected Risk Indicators
                    </h5>
                    <?php if (!empty($stats['warnings'])): ?>
                        <ul class="list-group list-group-flush bg-transparent">
                            <?php foreach ($stats['warnings'] as $w): ?>
                            <li class="list-group-item bg-transparent text-white px-0 py-3 border-secondary border-opacity-25 d-flex align-items-start gap-3">
                                <i class="bi bi-exclamation-triangle-fill text-danger mt-1"></i>
                                <span class="small" style="line-height: 1.5;"><?php echo htmlspecialchars($w); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-4 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-20 text-center text-success">
                            <i class="bi bi-check-circle-fill fs-2 d-block mb-2"></i>
                            <h6 class="fw-bold mb-1">No Academic Risk Detected</h6>
                            <span class="small text-white-50">Attendance across all courses is above 75% and assessment scores are in good standing.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. Action Guidance Recommendations -->
            <div class="col-lg-6">
                <div class="erp-card p-4 p-lg-5 h-100">
                    <h5 class="fw-bold text-white mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-lightbulb-fill text-warning"></i> Action Guidance &amp; Recovery Plan
                    </h5>
                    <?php if (!empty($stats['recommendations'])): ?>
                        <ul class="list-group list-group-flush bg-transparent">
                            <?php foreach ($stats['recommendations'] as $r): ?>
                            <li class="list-group-item bg-transparent text-white px-0 py-3 border-secondary border-opacity-25 d-flex align-items-start gap-3">
                                <i class="bi bi-arrow-right-circle-fill text-warning mt-1"></i>
                                <span class="small" style="line-height: 1.5;"><?php echo htmlspecialchars($r); ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-4 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10 text-center text-white-50">
                            <i class="bi bi-stars fs-2 d-block mb-2 text-warning"></i>
                            <h6 class="fw-bold text-white mb-1">Keep Up the Excellent Work</h6>
                            <span class="small">Maintain consistent daily attendance and prepare for final semester practicals.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Course Performance Matrix -->
        <div class="erp-card p-4 p-lg-5">
            <div class="mb-4">
                <h5 class="fw-bold text-white mb-1"><i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>Comprehensive Subject Evaluation Matrix</h5>
                <p class="text-white-50 small mb-0">Holistic view of course hours completed, attendance percentage, and internal academic grade</p>
            </div>
            
            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 220px;">Subject Name</th>
                            <th style="min-width: 180px;">Curriculum Delivery</th>
                            <th style="min-width: 180px;">Attendance</th>
                            <th style="min-width: 140px;">Continuous Score</th>
                            <th style="min-width: 100px;">Grade</th>
                            <th style="min-width: 160px;">Evaluation Summary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $s): ?>
                        <tr>
                            <td class="fw-bold text-white">
                                <?php echo htmlspecialchars($s['subject_name']); ?>
                                <span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 ms-1"><?php echo htmlspecialchars($s['subject_code']); ?></span>
                            </td>
                            <td>
                                <div class="small fw-semibold text-white"><?php echo $s['completed_hours']; ?> / <?php echo $s['total_course_hours']; ?> hrs</div>
                                <div class="progress bg-black bg-opacity-30 mt-1.5" style="height: 6px; width: 110px;">
                                    <div class="progress-bar bg-info" style="width: <?php echo min(100, $s['course_progress_percentage']); ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold <?php echo $s['percentage'] >= 75 ? 'text-success' : 'text-danger'; ?>"><?php echo $s['percentage']; ?>%</span>
                                <span class="text-white-50 small d-block"><?php echo $s['present']; ?> of <?php echo $s['conducted']; ?> sessions</span>
                            </td>
                            <td class="fw-bold text-primary"><?php echo $s['academic_score'] ?? 0; ?>%</td>
                            <td><span class="badge <?php echo $s['badge'] ?? 'bg-primary'; ?> px-2.5 py-1"><?php echo $s['grade'] ?? 'A'; ?></span></td>
                            <td>
                                <?php if ($s['percentage'] < 75 && ($s['academic_score'] ?? 0) < 50): ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-30 px-3 py-1.5 rounded-pill">High Priority Concern</span>
                                <?php elseif ($s['percentage'] < 75): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-30 px-3 py-1.5 rounded-pill">Attendance Shortage</span>
                                <?php elseif (($s['academic_score'] ?? 0) < 50): ?>
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-30 px-3 py-1.5 rounded-pill">Academic Focus</span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-30 px-3 py-1.5 rounded-pill">Good Standing</span>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

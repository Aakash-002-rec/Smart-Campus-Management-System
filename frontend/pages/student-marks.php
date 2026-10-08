<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = "Student Marks";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/student/get_student_data.php';

$student_id = (int)$_SESSION['user_id'];
$data = getStudentFullData($conn, $student_id);
$stats = $data['stats'];
$marks_data = $data['marks'];

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
                <h5 class="fw-bold mb-0 text-white">Academic Marks &amp; Grades</h5>
                <span class="text-white-50 small">Continuous internal assessments, assignments, and test breakdown</span>
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
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-30 px-3.5 py-2 rounded-pill d-none d-md-inline-block fw-semibold">
                Overall: <?php echo $stats['average_marks']; ?>% &bull; <?php echo $stats['overall_grade']; ?>
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
        <!-- Academic Summary Metric Cards (Spacious, No Truncation) -->
        <div class="row g-4 mb-4">
            <!-- 1. Overall Academic Score -->
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Overall Academic Score</span>
                        <div class="stat-icon-circle icon-sapphire">
                            <i class="bi bi-mortarboard-fill text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold text-white mb-0"><?php echo $stats['average_marks']; ?>%</h2>
                        <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-30 rounded-pill px-2.5 py-1 small">
                            <?php echo $stats['overall_grade']; ?>
                        </span>
                    </div>
                    <div class="progress mt-2 bg-secondary bg-opacity-25" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo min(100, $stats['average_marks']); ?>%"></div>
                    </div>
                    <span class="text-white-50 small mt-2 d-block" style="font-size: 0.8rem;">Cumulative Grade Point Average</span>
                </div>
            </div>

            <!-- 2. Highest Scored Course -->
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Highest Scored Course</span>
                        <div class="stat-icon-circle icon-emerald">
                            <i class="bi bi-trophy-fill text-white"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold text-success mb-1" style="line-height: 1.35; min-height: 2.2rem;">
                        <?php echo htmlspecialchars($stats['best_subject']); ?>
                    </h6>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Academic Strength</span>
                        <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2">Top Course</span>
                    </div>
                </div>
            </div>

            <!-- 3. Needs Academic Attention -->
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Target Focus Course</span>
                        <div class="stat-icon-circle icon-amber">
                            <i class="bi bi-flag-fill text-white"></i>
                        </div>
                    </div>
                    <h6 class="fw-bold text-warning mb-1" style="line-height: 1.35; min-height: 2.2rem;">
                        <?php echo htmlspecialchars($stats['weakest_subject']); ?>
                    </h6>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Target for Improvement</span>
                        <span class="badge bg-warning bg-opacity-25 text-warning rounded-pill px-2">Actionable</span>
                    </div>
                </div>
            </div>

            <!-- 4. Evaluation Status -->
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Evaluation Status</span>
                        <div class="stat-icon-circle icon-purple">
                            <i class="bi bi-check2-all text-white"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-info mb-1">Continuous</h3>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>System Assessment</span>
                        <span class="fw-semibold text-white">5 / 5 Subjects Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Marks Overview Cards & Detailed Tables -->
        <?php foreach ($marks_data as $sub): ?>
        <div class="erp-card p-4 p-lg-5 mb-4">
            <!-- Subject Header Banner -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                <div>
                    <span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 px-2.5 py-1 small mb-1">
                        <?php echo htmlspecialchars($sub['subject_code']); ?>
                    </span>
                    <h4 class="fw-bold text-white mb-0"><?php echo htmlspecialchars($sub['subject_name']); ?></h4>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <span class="text-white-50 small d-block">Overall Subject Aggregate</span>
                        <span class="fw-bold text-white fs-5"><?php echo $sub['earned_marks']; ?> / <?php echo $sub['max_marks']; ?> (<?php echo $sub['percentage']; ?>%)</span>
                    </div>
                    <span class="badge <?php echo $sub['badge']; ?> px-3.5 py-2 rounded-pill fs-6">
                        <?php echo $sub['grade']; ?>
                    </span>
                </div>
            </div>

            <!-- Spacious Table -->
            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 180px;">Assessment Name</th>
                            <th style="min-width: 140px;">Evaluation Type</th>
                            <th style="min-width: 140px;">Conducted Date</th>
                            <th style="min-width: 100px;">Max Marks</th>
                            <th style="min-width: 110px;">Marks Scored</th>
                            <th style="min-width: 180px;">Performance %</th>
                            <th style="min-width: 200px;">Remarks / Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sub['assessments'])): ?>
                            <?php foreach ($sub['assessments'] as $ass): ?>
                            <tr>
                                <td class="fw-bold text-white"><?php echo htmlspecialchars($ass['assessment_name']); ?></td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-30 text-white border border-white border-opacity-10 px-2.5 py-1">
                                        <?php echo ucfirst(htmlspecialchars($ass['assessment_type'] ?? 'Internal')); ?>
                                    </span>
                                </td>
                                <td class="text-white-50 small" style="white-space: nowrap;">
                                    <?php echo $ass['assessment_date'] ? date('d M Y', strtotime($ass['assessment_date'])) : 'N/A'; ?>
                                </td>
                                <td class="fw-semibold text-white-50"><?php echo number_format($ass['max_marks'], 2); ?></td>
                                <td class="fw-bold text-primary fs-6"><?php echo number_format($ass['scored_marks'], 2); ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1 bg-black bg-opacity-30" style="height: 6px; min-width: 80px;">
                                            <div class="progress-bar <?php echo $ass['percentage'] >= 75 ? 'bg-success' : ($ass['percentage'] >= 50 ? 'bg-primary' : 'bg-danger'); ?>" style="width: <?php echo min(100, $ass['percentage']); ?>%"></div>
                                        </div>
                                        <span class="small fw-semibold text-white"><?php echo $ass['percentage']; ?>%</span>
                                    </div>
                                </td>
                                <td class="text-white-50 small"><?php echo htmlspecialchars($ass['remarks'] ?? 'Satisfactory'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No assessment records available for this subject.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

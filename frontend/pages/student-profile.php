<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = "My Profile";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/student/get_student_data.php';

$student_id = (int)$_SESSION['user_id'];
$data = getStudentFullData($conn, $student_id);
$student = $data['student'];
$stats = $data['stats'];

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
                <h5 class="fw-bold mb-0 text-white">Student Academic Profile</h5>
                <span class="text-white-50 small">Enrolled credentials, department records, and contact details</span>
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
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px;">
                        <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small py-2" href="student-dashboard.php"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small py-2 text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-4 p-lg-5 flex-grow-1">
        <div class="row g-4">
            <!-- Left Profile Card -->
            <div class="col-lg-4">
                <div class="erp-card p-4 p-lg-5 text-center h-100">
                    <div class="rounded-circle bg-primary text-white mx-auto d-flex align-items-center justify-content-center shadow mb-3" style="width: 96px; height: 96px; font-size: 2.4rem; font-weight: 700;">
                        <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                    </div>
                    <h4 class="fw-bold text-white mb-1"><?php echo htmlspecialchars($student['name']); ?></h4>
                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-30 px-3.5 py-1.5 rounded-pill small mb-3">
                        Reg No: <?php echo htmlspecialchars($student['register_no'] ?? '21CS101'); ?>
                    </span>
                    
                    <div class="text-white-50 small mb-4">
                        <i class="bi bi-mortarboard me-1 text-info"></i><?php echo htmlspecialchars($student['department'] ?? 'Computer Science'); ?> &bull; Year <?php echo $student['year'] ?? '3'; ?>
                    </div>

                    <div class="p-3.5 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10 text-start small">
                        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                            <span class="text-white-50">Overall Attendance:</span>
                            <strong class="text-white"><?php echo $stats['overall_attendance']; ?>%</strong>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom border-secondary border-opacity-25">
                            <span class="text-white-50">Academic Grade:</span>
                            <strong class="text-primary"><?php echo $stats['overall_grade']; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-white-50">Standing:</span>
                            <strong class="<?php echo $stats['is_at_risk'] ? 'text-danger' : 'text-success'; ?>"><?php echo $stats['risk_level']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Profile Info -->
            <div class="col-lg-8">
                <div class="erp-card p-4 p-lg-5 h-100">
                    <h5 class="fw-bold text-white mb-4"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Registration Information</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small text-white-50 fw-semibold mb-1">Full Name</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary p-3" value="<?php echo htmlspecialchars($student['name']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-white-50 fw-semibold mb-1">Register / Roll Number</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary p-3" value="<?php echo htmlspecialchars($student['register_no'] ?? '21CS101'); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-white-50 fw-semibold mb-1">Institutional Email</label>
                            <input type="email" class="form-control bg-dark text-white border-secondary p-3" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-white-50 fw-semibold mb-1">Department</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary p-3" value="<?php echo htmlspecialchars($student['department'] ?? 'Computer Science & Engineering'); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-white-50 fw-semibold mb-1">Academic Year &amp; Semester</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary p-3" value="Year <?php echo $student['year'] ?? '3'; ?> (Semester 5)" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-white-50 fw-semibold mb-1">Program Type</label>
                            <input type="text" class="form-control bg-dark text-white border-secondary p-3" value="B.Tech Regular Full-Time" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

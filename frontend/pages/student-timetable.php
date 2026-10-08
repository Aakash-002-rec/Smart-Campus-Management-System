<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = "Class Timetable";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/student/get_student_data.php';

$student_id = (int)$_SESSION['user_id'];
$data = getStudentFullData($conn, $student_id);
$timetable = $data['timetable'];

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$today_name = date('l');
$active_day = in_array($today_name, $days) ? $today_name : 'Monday';

// Group timetable by day
$tt_by_day = [];
foreach ($days as $d) {
    $tt_by_day[$d] = [];
}
foreach ($timetable as $t) {
    if (isset($tt_by_day[$t['day']])) {
        $tt_by_day[$t['day']][] = $t;
    }
}

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
                <h5 class="fw-bold mb-0 text-white">Weekly Class Schedule</h5>
                <span class="text-white-50 small">Department lecture periods, room allocations, and timings</span>
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
                Today: <?php echo date('l, d M Y'); ?>
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
        <!-- Day Navigation Tabs -->
        <div class="erp-card p-3 mb-4">
            <ul class="nav nav-pills nav-fill gap-2" id="timetableTabs" role="tablist">
                <?php foreach ($days as $idx => $day): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-2.5 rounded-pill fw-semibold text-white <?php echo $day === $active_day ? 'active bg-primary shadow-sm' : ''; ?>" 
                            id="<?php echo strtolower($day); ?>-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#<?php echo strtolower($day); ?>" 
                            type="button" 
                            role="tab">
                        <i class="bi bi-calendar-event me-2"></i><?php echo $day; ?>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Timetable Tab Content -->
        <div class="tab-content" id="timetableTabContent">
            <?php foreach ($days as $day): ?>
            <div class="tab-pane fade <?php echo $day === $active_day ? 'show active' : ''; ?>" id="<?php echo strtolower($day); ?>" role="tabpanel">
                <div class="row g-4">
                    <?php if (!empty($tt_by_day[$day])): ?>
                        <?php foreach ($tt_by_day[$day] as $period_idx => $slot): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="erp-card p-4 h-100 position-relative border-start border-4 border-primary d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 px-2.5 py-1 small">
                                            Period <?php echo $period_idx + 1; ?>
                                        </span>
                                        <span class="text-white-50 small fw-bold">
                                            <i class="bi bi-clock me-1 text-info"></i><?php echo substr($slot['start_time'], 0, 5); ?> - <?php echo substr($slot['end_time'], 0, 5); ?>
                                        </span>
                                    </div>
                                    <h5 class="fw-bold text-white mb-1" style="font-size: 1.15rem;"><?php echo htmlspecialchars($slot['subject_name']); ?></h5>
                                    <span class="badge bg-secondary bg-opacity-25 text-white-50 mb-3 border border-white border-opacity-10"><?php echo htmlspecialchars($slot['subject_code']); ?></span>
                                </div>
                                
                                <div class="p-3 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10 text-white-50 small">
                                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                                        <span><i class="bi bi-person-badge-fill text-info me-1.5"></i>Faculty: <strong class="text-white"><?php echo htmlspecialchars($slot['faculty_name'] ?? 'Faculty Lead'); ?></strong></span>
                                        <span><i class="bi bi-building text-info me-1"></i>Academic Block</span>
                                    </div>
                                    <div><i class="bi bi-geo-alt-fill text-danger me-1"></i>Room / Lab: <strong class="text-white"><?php echo htmlspecialchars($slot['room']); ?></strong></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="erp-card p-5 text-center text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-white-50"></i>
                                <h6 class="fw-bold text-white">No scheduled lectures for <?php echo $day; ?></h6>
                                <span class="small text-white-50">Enjoy your self-study / lab workshop hours.</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

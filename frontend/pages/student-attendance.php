<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$page_title = "Student Attendance";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/student/get_student_data.php';

$student_id = (int)$_SESSION['user_id'];
$data = getStudentFullData($conn, $student_id);
$stats = $data['stats'];
$subjects = $data['attendance'];
$history = $data['attendance_history'];

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
                <h5 class="fw-bold mb-0 text-white">Daily Session Attendance</h5>
                <span class="text-white-50 small">Subject-wise percentages and session-by-session history logs</span>
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
            <span class="badge <?php echo $stats['attendance_badge_class']; ?> px-3.5 py-2 rounded-pill d-none d-md-inline-block fw-semibold">
                Overall: <?php echo $stats['overall_attendance']; ?>% &bull; <?php echo $stats['attendance_status']; ?>
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
        <!-- Attendance Performance Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Overall Attendance</span>
                        <div class="stat-icon-circle icon-emerald">
                            <i class="bi bi-calendar-check-fill text-white"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h2 class="fw-bold text-white mb-0"><?php echo $stats['overall_attendance']; ?>%</h2>
                        <span class="badge <?php echo $stats['attendance_badge_class']; ?> rounded-pill px-2.5 py-1 small"><?php echo $stats['attendance_status']; ?></span>
                    </div>
                    <div class="progress mt-2 bg-secondary bg-opacity-25" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?php echo min(100, $stats['overall_attendance']); ?>%"></div>
                    </div>
                    <span class="text-white-50 small mt-2 d-block" style="font-size: 0.8rem;">University Threshold: &ge; 75%</span>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Total Conducted</span>
                        <div class="stat-icon-circle icon-sapphire">
                            <i class="bi bi-layers-fill text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-info mb-1"><?php echo $stats['total_conducted_sessions']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>All Courses</span>
                        <span class="fw-semibold text-white">5 Active Subjects</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Attended Sessions</span>
                        <div class="stat-icon-circle icon-emerald">
                            <i class="bi bi-person-check-fill text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-success mb-1"><?php echo $stats['total_present_sessions']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Effective Attended</span>
                        <?php if ($stats['total_late_penalty_cuts'] > 0): ?>
                            <span class="badge bg-warning bg-opacity-25 text-warning rounded-pill px-2">-<?php echo $stats['total_late_penalty_cuts']; ?> cut (<?php echo $stats['total_late_sessions']; ?> Lates)</span>
                        <?php else: ?>
                            <span class="badge bg-success bg-opacity-25 text-success rounded-pill px-2">Verified</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="erp-card p-4 h-100 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">Missed Sessions</span>
                        <div class="stat-icon-circle icon-sunset">
                            <i class="bi bi-person-x-fill text-white"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold text-danger mb-1"><?php echo $stats['total_absent_sessions']; ?></h2>
                    <div class="text-white-50 small mt-2 d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                        <span>Absent + Penalty</span>
                        <span class="badge bg-danger bg-opacity-25 text-danger rounded-pill px-2"><?php echo round(($stats['total_absent_sessions'] / max(1, $stats['total_conducted_sessions'])) * 100, 1); ?>% Missed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Late Attendance Policy Alert Banner -->
        <div class="alert erp-card border-warning border-start border-4 p-3.5 mb-4" role="alert" style="background: rgba(245, 158, 11, 0.08);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-20 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-white mb-0.5">Automated Late Attendance Rule: 5 Late Classes = 1 Class Cut</h6>
                        <span class="small text-white-50">Every 5 'Late' records in a subject automatically deducts 1 attended class from your cumulative total and converts it to missed attendance.</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark border border-secondary text-warning px-3 py-2 rounded-pill small">
                        Total Late: <strong><?php echo $stats['total_late_sessions']; ?></strong>
                    </span>
                    <span class="badge <?php echo $stats['total_late_penalty_cuts'] > 0 ? 'bg-danger text-white' : 'bg-secondary bg-opacity-50 text-white-50'; ?> px-3 py-2 rounded-pill small">
                        Penalty Cuts: <strong>-<?php echo $stats['total_late_penalty_cuts']; ?> Class<?php echo $stats['total_late_penalty_cuts'] == 1 ? '' : 'es'; ?></strong>
                    </span>
                </div>
            </div>
        </div>

        <?php if ($stats['overall_attendance'] < 75): ?>
        <div class="alert alert-warning erp-card border-warning border-start border-5 p-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-2 text-warning"></i>
                <div>
                    <h6 class="fw-bold text-warning mb-1">Attendance Shortage Warning</h6>
                    <span class="small text-white-50">Your overall attendance is below the university 75% minimum requirement. Please maintain regular attendance to remain eligible for examinations.</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Subject-Wise Attendance Breakdown Table -->
        <div class="erp-card p-4 p-lg-5 mb-4">
            <div class="mb-4">
                <h5 class="fw-bold text-white mb-1"><i class="bi bi-table me-2 text-primary"></i>Subject-Wise Attendance Breakdown</h5>
                <p class="text-white-50 small mb-0">Dynamic calculation formula: <code>[Present + Late - floor(Late / 5)] / Conducted &times; 100</code></p>
            </div>
            
            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="min-width: 190px;">Subject Name</th>
                            <th style="min-width: 90px;">Code</th>
                            <th style="min-width: 140px;">Faculty Lead</th>
                            <th style="min-width: 100px;">Conducted</th>
                            <th style="min-width: 95px;">On-Time</th>
                            <th style="min-width: 130px;">Late (5 = -1)</th>
                            <th style="min-width: 85px;">Absent</th>
                            <th style="min-width: 110px;">Effective Attended</th>
                            <th style="min-width: 170px;">Attendance %</th>
                            <th style="min-width: 140px;">Exam Eligibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjects as $sub): ?>
                        <tr>
                            <td class="fw-bold text-white"><?php echo htmlspecialchars($sub['subject_name']); ?></td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 px-2.5 py-1"><?php echo htmlspecialchars($sub['subject_code']); ?></span></td>
                            <td class="text-white-50 small"><i class="bi bi-person text-info me-1"></i><?php echo htmlspecialchars($sub['faculty_name'] ?? 'Faculty'); ?></td>
                            <td class="fw-semibold text-white-50"><?php echo $sub['conducted']; ?></td>
                            <td class="text-success fw-bold"><?php echo $sub['raw_present']; ?></td>
                            <td>
                                <span class="text-warning fw-bold"><?php echo $sub['late']; ?></span>
                                <?php if ($sub['late_penalty_cuts'] > 0): ?>
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5 small ms-1" title="<?php echo $sub['late_penalty_cuts']; ?> class cutted due to <?php echo $sub['late']; ?> late classes">-<?php echo $sub['late_penalty_cuts']; ?> cut</span>
                                <?php elseif ($sub['late'] > 0): ?>
                                    <span class="text-white-50 small ms-1" style="font-size: 0.72rem;">(<?php echo $sub['lates_to_next_cut']; ?> to cut)</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-danger fw-bold"><?php echo $sub['absent']; ?></td>
                            <td class="fw-bold text-white"><?php echo $sub['effective_present']; ?> / <?php echo $sub['conducted']; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 bg-black bg-opacity-30" style="height: 6px; min-width: 70px;">
                                        <div class="progress-bar <?php echo $sub['percentage'] >= 75 ? 'bg-success' : 'bg-danger'; ?>" style="width: <?php echo min(100, $sub['percentage']); ?>%"></div>
                                    </div>
                                    <span class="fw-bold small text-white"><?php echo $sub['percentage']; ?>%</span>
                                </div>
                            </td>
                            <td>
                                <?php if ($sub['percentage'] >= 75): ?>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill small"><i class="bi bi-shield-check me-1"></i>Eligible (&ge;75%)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25 px-3 py-1.5 rounded-pill small"><i class="bi bi-exclamation-triangle me-1"></i>Shortage (&lt;75%)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detailed Daily Session History Logs with Filters -->
        <div class="erp-card p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-clock-history me-2 text-info"></i>Session-by-Session Attendance Logs</h5>
                    <span class="text-white-50 small">Every class session recorded by faculty</span>
                </div>
                <div class="d-flex gap-2">
                    <select id="filterSubject" class="form-select form-select-sm bg-dark text-white border-secondary rounded-pill px-3 py-1.5" onchange="filterHistory()">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo htmlspecialchars($s['subject_name']); ?>"><?php echo htmlspecialchars($s['subject_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filterStatus" class="form-select form-select-sm bg-dark text-white border-secondary rounded-pill px-3 py-1.5" onchange="filterHistory()">
                        <option value="">All Statuses</option>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                <table class="table erp-table mb-0" id="historyTable">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Date</th>
                            <th style="min-width: 180px;">Period &amp; Time</th>
                            <th style="min-width: 220px;">Subject</th>
                            <th style="min-width: 260px;">Lecture Topic</th>
                            <th style="min-width: 130px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($history)): ?>
                            <?php foreach ($history as $h): ?>
                            <tr class="history-row" data-subject="<?php echo htmlspecialchars($h['subject_name']); ?>" data-status="<?php echo $h['attendance_status']; ?>">
                                <td class="fw-semibold text-white" style="white-space: nowrap;"><?php echo date('d M Y', strtotime($h['session_date'])); ?></td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 text-white border border-white border-opacity-10 px-2.5 py-1">Period <?php echo $h['period_number']; ?></span>
                                    <span class="text-white-50 small ms-2"><?php echo substr($h['start_time'], 0, 5); ?> - <?php echo substr($h['end_time'], 0, 5); ?></span>
                                </td>
                                <td class="fw-semibold text-white"><?php echo htmlspecialchars($h['subject_name']); ?></td>
                                <td class="text-white-50"><?php echo htmlspecialchars($h['topic']); ?></td>
                                <td>
                                    <?php if ($h['attendance_status'] === 'Present'): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-30 px-3 py-1 rounded-pill"><i class="bi bi-check me-1"></i>Present</span>
                                    <?php elseif ($h['attendance_status'] === 'Late'): ?>
                                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-30 px-3 py-1 rounded-pill"><i class="bi bi-clock me-1"></i>Late</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-30 px-3 py-1 rounded-pill"><i class="bi bi-x me-1"></i>Absent</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No session records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
function filterHistory() {
    const subFilter = document.getElementById('filterSubject').value.toLowerCase();
    const stFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('.history-row');

    rows.forEach(row => {
        const sub = row.getAttribute('data-subject').toLowerCase();
        const st = row.getAttribute('data-status');
        const matchSub = !subFilter || sub.includes(subFilter);
        const matchSt = !stFilter || st === stFilter;
        row.style.display = (matchSub && matchSt) ? '' : 'none';
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

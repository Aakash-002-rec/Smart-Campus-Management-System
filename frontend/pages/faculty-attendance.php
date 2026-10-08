<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$page_title = "Faculty Attendance";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/faculty/get_faculty_data.php';

$data = getFacultyDashboardData($conn);
$subjects = $data['subjects'];
$students = $data['students'];
$recent_sessions = $data['recent_sessions'];

$selected_sub_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : ($subjects[0]['id'] ?? 1);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar-faculty.php';
?>

<style>
/* ==============================================
   INTERACTIVE OVAL ATTENDANCE WIDGET STYLES
   ============================================== */
.attendance-oval-widget {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(18, 10, 16, 0.72);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 9999px;
    padding: 3px 4px;
    gap: 3px;
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.45), 0 2px 10px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    position: relative;
    user-select: none;
    width: 100%;
    max-width: 320px;
    transition: all 0.25s ease;
}

.attendance-oval-widget:hover {
    border-color: rgba(255, 255, 255, 0.25);
    box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.45), 0 4px 14px rgba(0, 0, 0, 0.3);
}

.oval-option {
    position: relative;
    flex: 1 1 0;
    margin: 0;
    cursor: pointer;
    display: flex;
}

.oval-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
    width: 0;
    height: 0;
    margin: 0;
}

.oval-pill {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 6px 12px;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.2px;
    color: rgba(255, 255, 255, 0.65);
    background: transparent;
    border: 1px solid transparent;
    transition: all 0.24s cubic-bezier(0.34, 1.56, 0.64, 1);
    text-align: center;
    white-space: nowrap;
}

.oval-pill i {
    font-size: 0.85rem;
    transition: transform 0.24s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Hover effects when unselected */
.oval-present:hover input:not(:checked) + .oval-pill {
    background: rgba(16, 185, 129, 0.16);
    color: #34d399;
    transform: translateY(-1px) scale(1.03);
    border-color: rgba(16, 185, 129, 0.3);
}
.oval-late:hover input:not(:checked) + .oval-pill {
    background: rgba(245, 158, 11, 0.16);
    color: #fbbf24;
    transform: translateY(-1px) scale(1.03);
    border-color: rgba(245, 158, 11, 0.3);
}
.oval-absent:hover input:not(:checked) + .oval-pill {
    background: rgba(239, 68, 68, 0.16);
    color: #f87171;
    transform: translateY(-1px) scale(1.03);
    border-color: rgba(239, 68, 68, 0.3);
}

/* Tactile feedback on click */
.oval-option:active .oval-pill {
    transform: scale(0.92) !important;
}

/* Active / Selected States */
.oval-present input:checked + .oval-pill {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff !important;
    font-weight: 700;
    border-color: rgba(255, 255, 255, 0.35);
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.4);
    transform: scale(1.02);
}
.oval-present input:checked + .oval-pill i {
    transform: scale(1.15);
    animation: pillPop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.oval-late input:checked + .oval-pill {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff !important;
    font-weight: 700;
    border-color: rgba(255, 255, 255, 0.35);
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.4);
    transform: scale(1.02);
}
.oval-late input:checked + .oval-pill i {
    transform: scale(1.15);
    animation: pillPop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.oval-absent input:checked + .oval-pill {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff !important;
    font-weight: 700;
    border-color: rgba(255, 255, 255, 0.35);
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.55), inset 0 1px 1px rgba(255, 255, 255, 0.4);
    transform: scale(1.02);
}
.oval-absent input:checked + .oval-pill i {
    transform: scale(1.15);
    animation: pillPop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes pillPop {
    0% { transform: scale(0.7); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1.15); }
}

/* Light Mode Overrides */
[data-theme="sandal"] .attendance-oval-widget,
[data-theme="light"] .attendance-oval-widget {
    background: rgba(238, 230, 216, 0.85);
    border-color: rgba(180, 120, 40, 0.25);
    box-shadow: inset 0 2px 4px rgba(120, 95, 60, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
}
[data-theme="sandal"] .oval-pill,
[data-theme="light"] .oval-pill {
    color: #4a3c2c;
}

.counter-bump {
    animation: counterBump 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes counterBump {
    0% { transform: scale(1); }
    50% { transform: scale(1.08); }
    100% { transform: scale(1); }
}

/* Oval Form Inputs & Selects - Normal Compact Size */
.form-control,
.form-select,
input[type="text"],
input[type="date"],
select {
    border-radius: 9999px !important;
    padding: 0.375rem 1rem !important;
    font-size: 0.875rem !important;
    transition: border-color 0.2s ease, box-shadow 0.2s ease !important;
}

.form-control:focus,
.form-select:focus,
input:focus,
select:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.22) !important;
    outline: none;
}
</style>

<div class="app-main">
    <header class="app-topbar px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-dark d-lg-none p-2 rounded-3 border border-secondary" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-white">Daily Session Attendance Management</h5>
                <span class="text-white-50 small">Record period-wise attendance and maintain session lecture logs</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary px-3 py-2 rounded-pill d-none d-sm-inline-block">
                Date: <?php echo date('d M Y'); ?>
            </span>
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle bg-indigo text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px; background: #6366f1;">
                        <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small" href="faculty-dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item small" href="faculty-students.php"><i class="bi bi-people me-2"></i>Student Roster</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-4 flex-grow-1">
        <!-- Attendance Entry Form Card -->
        <div class="erp-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-secondary border-opacity-25 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-calendar-check me-2 text-primary"></i>Conduct New Lecture Session</h5>
                    <span class="text-white-50 small">Fill session parameters and mark enrolled student attendance</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="markAll('Present')">
                        <i class="bi bi-check-all me-1"></i> Mark All Present
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="markAll('Absent')">
                        <i class="bi bi-x-circle me-1"></i> Mark All Absent
                    </button>
                </div>
            </div>

            <form id="attendanceForm">
                <!-- Session Metadata Controls -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-white-50 small fw-semibold">Subject / Course</label>
                        <select name="subject_id" id="subjectSelect" class="form-select bg-dark text-white border-secondary rounded-pill px-3.5" required>
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $s['id'] == $selected_sub_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['subject_name']); ?> (<?php echo htmlspecialchars($s['subject_code']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-white-50 small fw-semibold">Session Date</label>
                        <input type="date" name="session_date" class="form-control bg-dark text-white border-secondary rounded-pill px-3.5" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-white-50 small fw-semibold">Period Number</label>
                        <select name="period_number" class="form-select bg-dark text-white border-secondary rounded-pill px-3.5">
                            <option value="1">Period 1 (09:00 - 10:00)</option>
                            <option value="2">Period 2 (10:00 - 11:00)</option>
                            <option value="3">Period 3 (11:15 - 12:15)</option>
                            <option value="4">Period 4 (13:00 - 14:00)</option>
                            <option value="5">Period 5 (14:00 - 15:00)</option>
                            <option value="6">Period 6 (15:15 - 16:15)</option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <label class="form-label text-white-50 small fw-semibold">Lecture Topic / Learning Objective</label>
                        <input type="text" name="topic" class="form-control bg-dark text-white border-secondary rounded-pill px-3.5" placeholder="e.g. PHP Sessions & Authentication Logic" required>
                    </div>
                </div>

                <!-- Hidden start and end time fields -->
                <input type="hidden" name="start_time" value="09:00:00">
                <input type="hidden" name="end_time" value="10:00:00">

                <!-- Enrolled Students Roster -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold text-white mb-0"><i class="bi bi-people me-2 text-info"></i>Student Attendance Sheet (<?php echo count($students); ?> Enrolled)</h6>
                        <span class="text-white-50 small">Toggle status per student &bull; <span class="text-warning fw-semibold"><i class="bi bi-info-circle me-1"></i>Punctuality Rule: Every 5 Lates automatically cuts 1 class</span></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="badge bg-dark border border-secondary px-3 py-2 rounded-pill small" id="liveAttendanceCounter">
                            <span class="text-success fw-bold" id="cntPresent">Present: <?php echo count($students); ?></span> &bull; 
                            <span class="text-warning fw-bold" id="cntLate">Late: 0</span> &bull; 
                            <span class="text-danger fw-bold" id="cntAbsent">Absent: 0</span>
                        </div>
                        <input type="text" id="sheetSearch" class="form-control form-control-sm bg-dark text-white border-secondary rounded-pill px-3" placeholder="Filter sheet..." style="width: 180px;" onkeyup="filterAttendanceSheet()">
                    </div>
                </div>

                <div class="table-responsive mb-4" style="max-height: 520px; overflow-y: auto;">
                    <table class="table erp-table align-middle mb-0" id="sheetTable">
                        <thead class="sticky-top bg-dark">
                            <tr>
                                <th style="width: 140px;">Register No</th>
                                <th>Student Name</th>
                                <th>Cumulative Att %</th>
                                <th class="text-center" style="width: 320px;">Mark Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $stu): ?>
                            <?php $initials = strtoupper(substr($stu['name'], 0, 1)); ?>
                            <tr class="sheet-row">
                                <td class="fw-bold text-white"><?php echo htmlspecialchars($stu['register_no'] ?? '23CS001'); ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 30px; height: 30px; background: linear-gradient(135deg, #3b82f6, #6366f1);">
                                            <?php echo $initials; ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-white d-block"><?php echo htmlspecialchars($stu['name']); ?></span>
                                            <span class="text-white-50 small"><?php echo htmlspecialchars($stu['department'] ?? 'CSE'); ?> &bull; Year <?php echo $stu['year'] ?? 3; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $stu['overall_attendance'] >= 75 ? 'bg-success bg-opacity-25 text-success border-success' : 'bg-danger bg-opacity-25 text-danger border-danger'; ?> border px-2.5 py-1 rounded-pill small">
                                        <?php echo $stu['overall_attendance']; ?>% &bull; <?php echo $stu['overall_attendance'] >= 75 ? 'Eligible' : 'Shortage'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <div class="attendance-oval-widget">
                                            <label class="oval-option oval-present" for="pres_<?php echo $stu['id']; ?>" title="Mark Present">
                                                <input type="radio" class="status-radio" name="attendance[<?php echo $stu['id']; ?>]" id="pres_<?php echo $stu['id']; ?>" value="Present" checked onchange="updateLiveCounters()">
                                                <span class="oval-pill">
                                                    <i class="bi bi-check-circle-fill"></i>
                                                    <span>Present</span>
                                                </span>
                                            </label>

                                            <label class="oval-option oval-late" for="late_<?php echo $stu['id']; ?>" title="Mark Late">
                                                <input type="radio" class="status-radio" name="attendance[<?php echo $stu['id']; ?>]" id="late_<?php echo $stu['id']; ?>" value="Late" onchange="updateLiveCounters()">
                                                <span class="oval-pill">
                                                    <i class="bi bi-clock-fill"></i>
                                                    <span>Late</span>
                                                </span>
                                            </label>

                                            <label class="oval-option oval-absent" for="abs_<?php echo $stu['id']; ?>" title="Mark Absent">
                                                <input type="radio" class="status-radio" name="attendance[<?php echo $stu['id']; ?>]" id="abs_<?php echo $stu['id']; ?>" value="Absent" onchange="updateLiveCounters()">
                                                <span class="oval-pill">
                                                    <i class="bi bi-x-circle-fill"></i>
                                                    <span>Absent</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" id="saveAttendanceBtn" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-save me-1"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>

        <!-- Session Attendance History Logs -->
        <div class="erp-card p-4">
            <h5 class="fw-bold text-white mb-1"><i class="bi bi-clock-history me-2 text-info"></i>Past Conducted Sessions History</h5>
            <p class="text-white-50 small mb-3">All daily sessions marked by faculty with student participation summaries</p>

            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Period &amp; Time</th>
                            <th>Subject</th>
                            <th>Lecture Topic</th>
                            <th>Present</th>
                            <th>Late</th>
                            <th>Absent</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_sessions)): ?>
                            <?php foreach ($recent_sessions as $s): ?>
                            <?php 
                                $total_m = (int)$s['total_students_marked'];
                                $pres_m = (int)$s['present_count'];
                                $pct_m = $total_m > 0 ? round(($pres_m / $total_m) * 100, 1) : 100.0;
                            ?>
                            <tr>
                                <td class="fw-semibold text-white"><?php echo date('d-m-Y', strtotime($s['session_date'])); ?></td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 text-white">P<?php echo $s['period_number']; ?></span>
                                    <span class="text-white-50 small ms-1"><?php echo substr($s['start_time'], 0, 5); ?> - <?php echo substr($s['end_time'], 0, 5); ?></span>
                                </td>
                                <td class="fw-semibold text-white"><?php echo htmlspecialchars($s['subject_name']); ?></td>
                                <td class="text-white-50"><?php echo htmlspecialchars($s['topic']); ?></td>
                                <td class="text-success fw-bold"><?php echo $s['present_count']; ?></td>
                                <td class="text-warning fw-semibold"><?php echo $s['late_count']; ?></td>
                                <td class="text-danger fw-semibold"><?php echo $s['absent_count']; ?></td>
                                <td>
                                    <span class="badge <?php echo $pct_m >= 75 ? 'bg-success' : 'bg-danger'; ?> rounded-pill">
                                        <?php echo $pct_m; ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No previous session records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
function updateLiveCounters() {
    const radios = document.querySelectorAll('.status-radio:checked');
    let p = 0, l = 0, a = 0;
    radios.forEach(r => {
        if (r.value === 'Present') p++;
        else if (r.value === 'Late') l++;
        else if (r.value === 'Absent') a++;
    });
    
    const cntBadge = document.getElementById('liveAttendanceCounter');
    if (cntBadge) {
        cntBadge.classList.remove('counter-bump');
        void cntBadge.offsetWidth; // trigger reflow
        cntBadge.classList.add('counter-bump');
    }

    document.getElementById('cntPresent').textContent = 'Present: ' + p;
    document.getElementById('cntLate').textContent = 'Late: ' + l;
    document.getElementById('cntAbsent').textContent = 'Absent: ' + a;
}

function markAll(status) {
    const radios = document.querySelectorAll('.status-radio');
    radios.forEach(radio => {
        if (radio.value === status) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    updateLiveCounters();
}

function filterAttendanceSheet() {
    const query = document.getElementById('sheetSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.sheet-row');
    rows.forEach(r => {
        const txt = r.textContent.toLowerCase();
        r.style.display = (!query || txt.includes(query)) ? '' : 'none';
    });
}

document.getElementById('attendanceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveAttendanceBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    const formData = new FormData(this);

    try {
        const response = await fetch('../../backend/faculty/save_session_attendance.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            showToast('Attendance saved successfully! (' + result.summary.present + ' Present, ' + result.summary.absent + ' Absent)', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            showToast(result.message || 'Error saving attendance.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Save Attendance';
        }
    } catch (err) {
        showToast('Network/server connection error.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Save Attendance';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

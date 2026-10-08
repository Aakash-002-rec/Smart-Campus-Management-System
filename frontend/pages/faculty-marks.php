<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login.php");
    exit();
}

$page_title = "Faculty Marks Entry";
require_once __DIR__ . '/../../backend/config/db.php';
require_once __DIR__ . '/../../backend/faculty/get_faculty_data.php';

$data = getFacultyDashboardData($conn);
$subjects = $data['subjects'];
$students = $data['students'];
$assessments = $data['assessments'];

$selected_sub_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : ($subjects[0]['id'] ?? 1);

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
                <h5 class="fw-bold mb-0 text-white">Continuous Assessment &amp; Smart Grading Engine</h5>
                <span class="text-white-50 small">Real-time automatic grading, intelligent remarks generator, and class score telemetry</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary px-3 py-2 rounded-pill d-none d-sm-inline-block">
                Auto-Grading Active
            </span>
            <div class="dropdown">
                <button class="btn btn-dark border border-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-2 dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px; background: #6366f1;">
                        <?php echo strtoupper(substr($current_user_name, 0, 1)); ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline"><?php echo $current_user_name; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow border-secondary">
                    <li><a class="dropdown-item small py-2" href="faculty-dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                    <li><a class="dropdown-item small py-2" href="faculty-students.php"><i class="bi bi-people me-2"></i>Student Roster</a></li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li><a class="dropdown-item small py-2 text-danger fw-semibold" href="../../backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-4 flex-grow-1">
        <!-- Mark Entry Form Card -->
        <div class="erp-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-secondary border-opacity-25 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-white mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Enter Assessment Scores &amp; Real-Time Grading</h5>
                    <span class="text-white-50 small">Type student score — the system instantly computes percentage, assigns <strong>Excellent / Good / Satisfactory / Warning</strong> rating, and generates remarks.</span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="batchFillMarks(50)">
                        <i class="bi bi-star-fill me-1"></i> Fill 50 (Excellent)
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="batchFillMarks(40)">
                        <i class="bi bi-hand-thumbs-up me-1"></i> Fill 40 (Good)
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3" onclick="batchFillMarks(32)">
                        <i class="bi bi-check me-1"></i> Fill 32 (Satisfactory)
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="batchFillMarks('')">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                </div>
            </div>

            <form id="marksForm">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label text-white-50 small fw-semibold">Subject / Course</label>
                        <select name="subject_id" id="markSubjectSelect" class="form-select bg-dark text-white border-secondary rounded-3" required>
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo $s['id'] == $selected_sub_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['subject_name']); ?> (<?php echo htmlspecialchars($s['subject_code']); ?>) <?php echo !empty($s['is_assigned']) ? '★ Lead' : ''; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-white-50 small fw-semibold">Assessment Title</label>
                        <select name="assessment_name" id="assessmentNameSelect" class="form-select bg-dark text-white border-secondary rounded-3" required>
                            <option value="Internal Assessment 1">Internal Assessment 1</option>
                            <option value="Internal Assessment 2">Internal Assessment 2</option>
                            <option value="Internal Assessment 3">Internal Assessment 3</option>
                            <option value="Course Assignment 1">Course Assignment 1</option>
                            <option value="Course Assignment 2">Course Assignment 2</option>
                            <option value="Lab Practical / Quiz 1">Lab Practical / Quiz 1</option>
                            <option value="Model Examination">Model Examination</option>
                            <option value="Capstone Course Project">Capstone Course Project</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-white-50 small fw-semibold">Assessment Type</label>
                        <select name="assessment_type" class="form-select bg-dark text-white border-secondary rounded-3">
                            <option value="internal">Internal Test</option>
                            <option value="assignment">Assignment</option>
                            <option value="quiz">Quiz</option>
                            <option value="lab">Lab / Practical</option>
                            <option value="model_exam">Model Exam</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-white-50 small fw-semibold">Maximum Marks</label>
                        <input type="number" step="0.5" name="max_marks" id="maxMarksInput" class="form-control bg-dark text-white border-secondary rounded-3" value="50" min="1" max="100" required onchange="updateMaxLimit(this.value)">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-white-50 small fw-semibold">Assessment Date</label>
                        <input type="date" name="assessment_date" class="form-control bg-dark text-white border-secondary rounded-3" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <!-- Live Score Telemetry Header Bar -->
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2 p-3 rounded-3 bg-dark bg-opacity-60 border border-secondary border-opacity-30">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-white-50 small fw-semibold"><i class="bi bi-speedometer2 text-info me-1"></i>Live Batch Telemetry:</span>
                        <span class="badge bg-secondary bg-opacity-40 text-white" id="telemetryCount"><?php echo count($students); ?> Students</span>
                        <span class="badge bg-primary bg-opacity-25 text-primary fw-bold" id="telemetryAvg">Class Avg: -- / 50</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap" id="telemetryBadges">
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-30 px-2.5 py-1" id="cntExcellent">🌟 Excellent: 0</span>
                        <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-30 px-2.5 py-1" id="cntGood">👍 Good: 0</span>
                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-30 px-2.5 py-1" id="cntSatisfactory">👌 Satisfactory: 0</span>
                        <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-30 px-2.5 py-1" id="cntRisk">❌ Needs Support: 0</span>
                    </div>
                </div>

                <!-- Student Score Input Table -->
                <div class="table-responsive mb-4" style="max-height: 540px; overflow-y: auto;">
                    <table class="table erp-table align-middle mb-0">
                        <thead class="sticky-top bg-dark">
                            <tr>
                                <th style="width: 130px;">Register No</th>
                                <th style="min-width: 180px;">Student Name</th>
                                <th style="width: 190px;">Score (<span class="max-mark-label">Max 50</span>)</th>
                                <th style="min-width: 200px;">Real-Time Rating</th>
                                <th style="min-width: 260px;">Faculty Remarks (Auto-Generated)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $stu): ?>
                            <?php $sid = $stu['id']; ?>
                            <tr class="score-row" id="row_<?php echo $sid; ?>">
                                <td class="fw-bold text-white"><?php echo htmlspecialchars($stu['register_no'] ?? '23CS001'); ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center small fw-bold" style="width: 28px; height: 28px; background: linear-gradient(135deg, #3b82f6, #6366f1);">
                                            <?php echo strtoupper(substr($stu['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-white d-block"><?php echo htmlspecialchars($stu['name']); ?></span>
                                            <span class="text-white-50 small"><?php echo htmlspecialchars($stu['department'] ?? 'CSE'); ?> &bull; Yr <?php echo $stu['year'] ?? 3; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" step="0.5" min="0" max="50" 
                                               name="marks[<?php echo $sid; ?>]" 
                                               id="mark_input_<?php echo $sid; ?>" 
                                               class="form-control form-control-sm bg-dark text-white border-secondary score-input fw-bold" 
                                               placeholder="Enter mark" 
                                               oninput="evaluateStudentScore(<?php echo $sid; ?>, this.value)" 
                                               required>
                                        <span class="input-group-text bg-secondary bg-opacity-25 text-white-50 border-secondary small max-mark-display">/ 50</span>
                                    </div>
                                </td>
                                <td>
                                    <div id="grade_badge_<?php echo $sid; ?>">
                                        <span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 px-2.5 py-1">
                                            Awaiting Input
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="text" 
                                               name="remarks[<?php echo $sid; ?>]" 
                                               id="remark_input_<?php echo $sid; ?>" 
                                               class="form-control bg-dark text-white border-secondary" 
                                               placeholder="Auto-evaluates on score entry">
                                        <button class="btn btn-outline-secondary" type="button" onclick="quickRemarkPrompt(<?php echo $sid; ?>)" title="Quick remarks">
                                            <i class="bi bi-magic text-warning"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" id="saveMarksBtn" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Save Assessment Marks
                    </button>
                </div>
            </form>
        </div>

        <!-- Recorded Assessments Summary -->
        <div class="erp-card p-4">
            <h5 class="fw-bold text-white mb-1"><i class="bi bi-journal-check me-2 text-info"></i>Recorded Assessments &amp; Class Averages</h5>
            <p class="text-white-50 small mb-3">All continuous assessments entered for department courses</p>

            <div class="table-responsive">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Assessment Name</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Max Marks</th>
                            <th>Records Entered</th>
                            <th>Class Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($assessments)): ?>
                            <?php foreach ($assessments as $ass): ?>
                            <tr>
                                <td class="fw-bold text-white"><?php echo htmlspecialchars($ass['assessment_name']); ?></td>
                                <td>
                                    <span class="fw-semibold text-white"><?php echo htmlspecialchars($ass['subject_name']); ?></span>
                                    <span class="badge bg-secondary bg-opacity-25 text-white-50 ms-1"><?php echo htmlspecialchars($ass['subject_code']); ?></span>
                                </td>
                                <td class="text-white-50 small"><?php echo $ass['assessment_date'] ? date('d M Y', strtotime($ass['assessment_date'])) : 'N/A'; ?></td>
                                <td class="fw-semibold text-white"><?php echo $ass['max_marks']; ?></td>
                                <td><span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50"><?php echo $ass['marks_entered_count']; ?> Students</span></td>
                                <td class="fw-bold text-success"><?php echo $ass['class_average_mark']; ?> / <?php echo $ass['max_marks']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No assessments logged yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
let currentMaxMarks = 50;

function updateMaxLimit(val) {
    currentMaxMarks = parseFloat(val) || 50;
    document.querySelectorAll('.max-mark-label').forEach(el => el.textContent = 'Max ' + currentMaxMarks);
    document.querySelectorAll('.max-mark-display').forEach(el => el.textContent = '/ ' + currentMaxMarks);
    document.querySelectorAll('.score-input').forEach(input => {
        input.max = currentMaxMarks;
        if (input.value) {
            const sid = input.id.replace('mark_input_', '');
            evaluateStudentScore(sid, input.value);
        }
    });
}

function evaluateStudentScore(studentId, scoreVal) {
    const badgeContainer = document.getElementById('grade_badge_' + studentId);
    const remarkInput = document.getElementById('remark_input_' + studentId);

    if (scoreVal === '' || scoreVal === null || isNaN(scoreVal)) {
        badgeContainer.innerHTML = `<span class="badge bg-secondary bg-opacity-25 text-white-50 border border-white border-opacity-10 px-2.5 py-1">Awaiting Input</span>`;
        if (remarkInput) remarkInput.value = '';
        recalculateTelemetry();
        return;
    }

    const score = parseFloat(scoreVal);
    const maxMarks = currentMaxMarks;
    const percentage = maxMarks > 0 ? (score / maxMarks) * 100 : 0;
    const pctFixed = percentage.toFixed(1);

    let badgeHtml = '';
    let autoRemark = '';

    if (percentage >= 90) {
        badgeHtml = `
            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-1.5 rounded-pill fw-bold">
                <i class="bi bi-star-fill text-warning me-1"></i> Excellent (A+) &bull; ${pctFixed}%
            </span>`;
        autoRemark = score === maxMarks ? `Outstanding concept mastery (100%)` : `Excellent problem solving & analytical depth (${pctFixed}%)`;
    } else if (percentage >= 75) {
        badgeHtml = `
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-3 py-1.5 rounded-pill fw-bold">
                <i class="bi bi-hand-thumbs-up-fill me-1"></i> Good (A) &bull; ${pctFixed}%
            </span>`;
        autoRemark = `Good comprehension, neat structure & clear execution (${pctFixed}%)`;
    } else if (percentage >= 60) {
        badgeHtml = `
            <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-1.5 rounded-pill fw-semibold">
                <i class="bi bi-check-circle-fill me-1"></i> Satisfactory (B) &bull; ${pctFixed}%
            </span>`;
        autoRemark = `Satisfactory effort, reinforce core theoretical formulas (${pctFixed}%)`;
    } else if (percentage >= 50) {
        badgeHtml = `
            <span class="badge bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 px-3 py-1.5 rounded-pill fw-semibold">
                <i class="bi bi-exclamation-circle-fill me-1"></i> Average (C) &bull; ${pctFixed}%
            </span>`;
        autoRemark = `Average score, recommended to attend tutorial revision (${pctFixed}%)`;
    } else {
        badgeHtml = `
            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-3 py-1.5 rounded-pill fw-bold">
                <i class="bi bi-shield-slash-fill me-1"></i> Needs Support (D) &bull; ${pctFixed}%
            </span>`;
        autoRemark = `Below required standard (${pctFixed}%), scheduled for faculty remedial sessions`;
    }

    badgeContainer.innerHTML = badgeHtml;

    // Only update remark if empty or user hasn't typed a completely custom one
    if (remarkInput && (!remarkInput.dataset.manual || remarkInput.value === '')) {
        remarkInput.value = autoRemark;
    }

    recalculateTelemetry();
}

function quickRemarkPrompt(studentId) {
    const remarkInput = document.getElementById('remark_input_' + studentId);
    const scoreInput = document.getElementById('mark_input_' + studentId);
    if (!scoreInput.value) {
        showToast('Please enter a score first to generate remarks.', 'warning');
        return;
    }
    const currentScore = parseFloat(scoreInput.value);
    const pct = ((currentScore / currentMaxMarks) * 100).toFixed(0);
    const remarkOptions = [
        `Outstanding concept mastery (${pct}%)`,
        `Good analytical depth & systematic steps (${pct}%)`,
        `Consistent performance, keep it up (${pct}%)`,
        `Satisfactory, revise unit 2 & 3 questions (${pct}%)`,
        `Needs academic counseling & extra practice (${pct}%)`
    ];
    const picked = prompt("Select or write custom remarks:\n\n1. " + remarkOptions[0] + "\n2. " + remarkOptions[1] + "\n3. " + remarkOptions[2] + "\n4. " + remarkOptions[3] + "\n5. " + remarkOptions[4], remarkInput.value || remarkOptions[0]);
    if (picked !== null) {
        remarkInput.value = picked;
        remarkInput.dataset.manual = "true";
    }
}

function batchFillMarks(val) {
    const inputs = document.querySelectorAll('.score-input');
    inputs.forEach(inp => {
        inp.value = val;
        const sid = inp.id.replace('mark_input_', '');
        evaluateStudentScore(sid, val);
    });
    if (val !== '') {
        showToast(`Auto-graded all students with ${val} marks!`, 'success');
    }
}

function recalculateTelemetry() {
    const inputs = document.querySelectorAll('.score-input');
    let totalScore = 0;
    let count = 0;
    let exc = 0, gd = 0, sat = 0, rsk = 0;

    inputs.forEach(inp => {
        if (inp.value !== '' && !isNaN(inp.value)) {
            const score = parseFloat(inp.value);
            totalScore += score;
            count++;
            const pct = (score / currentMaxMarks) * 100;
            if (pct >= 90) exc++;
            else if (pct >= 75) gd++;
            else if (pct >= 60) sat++;
            else rsk++;
        }
    });

    const avg = count > 0 ? (totalScore / count).toFixed(1) : '--';
    document.getElementById('telemetryAvg').textContent = `Class Avg: ${avg} / ${currentMaxMarks}`;
    document.getElementById('cntExcellent').textContent = `🌟 Excellent: ${exc}`;
    document.getElementById('cntGood').textContent = `👍 Good: ${gd}`;
    document.getElementById('cntSatisfactory').textContent = `👌 Satisfactory: ${sat}`;
    document.getElementById('cntRisk').textContent = `❌ Needs Support: ${rsk}`;
}

document.getElementById('marksForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveMarksBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving Marks...';

    const formData = new FormData(this);

    try {
        const response = await fetch('../../backend/faculty/save_student_marks.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            showToast('Marks and automatic evaluations saved successfully! (' + result.records_saved + ' records updated)', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1200);
        } else {
            showToast(result.message || 'Error saving marks.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Assessment Marks';
        }
    } catch (err) {
        showToast('Network/server connection error.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Save Assessment Marks';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!DOCTYPE html>
<html lang="en" data-theme="sandal">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Smart Campus Management Portal</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: radial-gradient(circle at 10% 10%, rgba(238, 220, 192, 0.70) 0%, transparent 55%),
                        radial-gradient(circle at 90% 85%, rgba(226, 201, 165, 0.55) 0%, transparent 55%),
                        #f8f3e8;
            min-height: 100vh;
            color: #1c150c;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .login-box {
            background: #ffffff;
            border: 1px solid #dfd3bd;
            border-radius: 24px;
            box-shadow: 0 20px 45px -10px rgba(120, 95, 60, 0.18), 0 0 0 1px rgba(180, 140, 95, 0.12);
            backdrop-filter: blur(20px);
            overflow: hidden;
            max-width: 480px;
            width: 100%;
        }
        .login-header-glow {
            background: linear-gradient(180deg, rgba(180, 120, 40, 0.12) 0%, transparent 100%);
            padding: 32px 32px 20px 32px;
            text-align: center;
            border-bottom: 1px solid #ebdcc8;
        }
        .role-pill {
            cursor: pointer;
            padding: 9px 12px;
            border-radius: 20px;
            border: 1px solid #dfd3bd;
            background: #f5eedf;
            color: #5c4b38;
            font-size: 0.84rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .role-pill:hover {
            background: #ebdcc8;
            color: #1c150c;
        }
        .role-pill.active {
            background: linear-gradient(135deg, #b47828 0%, #8c581a 100%);
            border-color: #8c581a;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(180, 120, 40, 0.35);
        }
        .form-control-custom {
            background: #fdfbf7 !important;
            border: 1px solid #d8cab2 !important;
            color: #1c150c !important;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .form-control-custom:focus {
            background: #ffffff !important;
            border-color: #b47828 !important;
            box-shadow: 0 0 0 3px rgba(180, 120, 40, 0.22) !important;
            color: #1c150c !important;
        }
        .form-control-custom::placeholder {
            color: #9a8870 !important;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #b47828 0%, #8c581a 100%);
            color: #ffffff;
            font-weight: 600;
            border-radius: 12px;
            padding: 12px;
            border: none;
            box-shadow: 0 10px 20px -5px rgba(180, 120, 40, 0.4);
            transition: all 0.2s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 26px -5px rgba(180, 120, 40, 0.55);
            color: #ffffff;
        }
        .faculty-quick-item {
            cursor: pointer;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 0.84rem;
            color: #2c2014;
            transition: all 0.15s ease;
        }
        .faculty-quick-item:hover {
            background: #f3ebd8;
            color: #b47828;
        }
    </style>
</head>
<body>

<div class="login-box">
    <!-- Header -->
    <div class="login-header-glow">
        <div class="d-inline-flex p-3 rounded-circle mb-2 shadow-sm" style="background: #fbf3e5; color: #b47828; border: 1px solid #dfd3bd;">
            <i class="bi bi-mortarboard-fill fs-2"></i>
        </div>
        <h4 class="fw-bold mb-1" style="color: #1c150c;">Smart Campus ERP</h4>
        <p class="small mb-0" style="color: #786650;">College Academic Management &amp; Analytics Portal</p>
    </div>

    <!-- Body Form -->
    <div class="p-4 p-sm-5">
        <!-- Dynamic Alert Message Box -->
        <div id="alertBox" class="alert d-none py-2 px-3 small mb-3 border-0 rounded-3 text-center" role="alert"></div>

        <!-- Quick Role Selector Tabs -->
        <div class="mb-3">
            <span class="small d-block mb-2 font-monospace fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px; color: #786650;">SIGN IN AS:</span>
            <div class="d-flex justify-content-between gap-2">
                <button type="button" class="role-pill flex-grow-1 text-center active" id="btnRoleStudent" onclick="selectRole('student', 'aakashcampus@gmail.com', 'Aakash@001')">
                    <i class="bi bi-person me-1"></i> Student
                </button>
                <button type="button" class="role-pill flex-grow-1 text-center" id="btnRoleFaculty" onclick="selectRole('faculty', 'aruncampus@gmail.com', 'Faculty@FAC001')">
                    <i class="bi bi-person-workspace me-1"></i> Faculty
                </button>
                <button type="button" class="role-pill flex-grow-1 text-center" id="btnRoleAdmin" onclick="selectRole('admin', 'admincampus@gmail.com', 'Admin@Campus2026')">
                    <i class="bi bi-shield-lock me-1"></i> Admin
                </button>
            </div>
        </div>

        <!-- Faculty Fast Selector Dropdown (Visible ONLY when Faculty tab is chosen) -->
        <div id="facultyDropdownContainer" class="dropdown mb-3 d-none">
            <button class="btn w-100 text-start d-flex justify-content-between align-items-center py-2 px-3 rounded-3 small dropdown-toggle" type="button" data-bs-toggle="dropdown" style="background: #fdfbf7; border: 1px solid #d8cab2; color: #1c150c;">
                <span id="facultySelectedLabel"><i class="bi bi-person-badge me-2" style="color: #b47828;"></i>Faculty: Dr. Arun Kumar (Web)</span>
            </button>
            <ul class="dropdown-menu w-100 shadow-lg p-2" style="max-height: 280px; overflow-y: auto; background: #ffffff; border: 1px solid #dfd3bd;">
                <li class="px-2 py-1 small text-uppercase font-monospace fw-bold" style="font-size: 0.68rem; color: #8c581a;">Select 1 of 7 Faculty Leads</li>
                <li><div class="faculty-quick-item" onclick="pickFaculty('aruncampus@gmail.com', 'Faculty@FAC001', 'Dr. Arun Kumar (Web Programming)')"><strong>1. Dr. Arun Kumar</strong> &bull; Web Programming</div></li>
                <li><div class="faculty-quick-item" onclick="pickFaculty('priyacampus@gmail.com', 'Faculty@FAC049', 'Dr. Priya Sharma (DBMS)')"><strong>2. Dr. Priya Sharma</strong> &bull; Database Systems</div></li>
                <li><div class="faculty-quick-item" onclick="pickFaculty('rajeshcampus@gmail.com', 'Faculty@FAC050', 'Prof. Rajesh Iyer (Networks)')"><strong>3. Prof. Rajesh Iyer</strong> &bull; Computer Networks</div></li>
                <li><div class="faculty-quick-item" onclick="pickFaculty('meenakshicampus@gmail.com', 'Faculty@FAC051', 'Dr. Meenakshi Sundaram (Automata)')"><strong>4. Dr. Meenakshi Sundaram</strong> &bull; Automata Theory</div></li>
                <li><div class="faculty-quick-item" onclick="pickFaculty('sureshcampus@gmail.com', 'Faculty@FAC052', 'Dr. Suresh Balaji (AI & ML)')"><strong>5. Dr. Suresh Balaji</strong> &bull; AI &amp; Machine Learning</div></li>
                <li><div class="faculty-quick-item" onclick="pickFaculty('ananyacampus@gmail.com', 'Faculty@FAC053', 'Prof. Ananya Sengupta (Cloud)')"><strong>6. Prof. Ananya Sengupta</strong> &bull; Cloud &amp; DevOps</div></li>
                <li><div class="faculty-quick-item" onclick="pickFaculty('vikramadityacampus@gmail.com', 'Faculty@FAC054', 'Dr. Vikramaditya Rao (Security)')"><strong>7. Dr. Vikramaditya Rao</strong> &bull; Cyber Security</div></li>
            </ul>
        </div>

        <!-- Form -->
        <form id="loginForm" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold" style="color: #4a3c2c;">Institutional Email or Roll No</label>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-custom ps-3" id="email" name="email" value="aakashcampus@gmail.com" placeholder="namecampus@gmail.com or 23CS001" required>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label small fw-semibold" style="color: #4a3c2c;">Password</label>
                    <span class="small fw-semibold" style="font-size: 0.78rem; cursor: pointer; color: #b47828;" onclick="document.getElementById('password').value='Aakash@001'">Sample: Aakash@001</span>
                </div>
                <div class="input-group">
                    <input type="password" class="form-control form-control-custom" id="password" name="password" value="Aakash@001" placeholder="Enter password" required>
                    <button class="btn" type="button" id="togglePassword" style="background: #ede3ce; border-color: #d8cab2; color: #4a3c2c;">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" id="loginBtn" class="btn btn-gradient w-100">
                <span id="btnText"><i class="bi bi-box-arrow-in-right me-2"></i>Access Portal</span>
                <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </form>

        <div class="mt-3 text-center">
            <button type="button" class="btn btn-link btn-sm text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#credentialsModal" style="color: #8c581a; font-size: 0.8rem;">
                <i class="bi bi-person-lines-fill me-1"></i> View All 64 Member Credentials (Email &amp; Passwords)
            </button>
        </div>

        <div class="mt-3 pt-3 border-top text-center" style="border-color: #ebdcc8 !important;">
            <span class="small" style="font-size: 0.75rem; color: #786650;">
                <i class="bi bi-shield-check text-success me-1"></i>Secure 256-Bit Encrypted Portal &bull; 64 Enrolled Members
            </span>
        </div>
    </div>
</div>

<!-- Modal: Member Credentials Directory -->
<div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="background: #fdfbf7; border: 1px solid #d8cab2; border-radius: 16px;">
            <div class="modal-header border-bottom" style="border-color: #dfd3bd !important;">
                <div>
                    <h6 class="modal-title fw-bold" id="credentialsModalLabel" style="color: #1c150c;">
                        <i class="bi bi-key-fill text-warning me-2"></i>Member Credentials Directory (64 Accounts)
                    </h6>
                    <span class="small text-muted" style="font-size: 0.75rem;">All members updated to <strong>xxxcampus@gmail.com</strong> with unique secure passwords</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group input-group-sm mb-3">
                    <span class="input-group-text" style="background: #f5eedf; border-color: #d8cab2;"><i class="bi bi-search"></i></span>
                    <input type="text" id="credSearch" class="form-control" placeholder="Search by name, roll no, or email..." style="background: #ffffff; border-color: #d8cab2;" onkeyup="filterCredentials()">
                </div>
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table table-sm align-middle table-hover" id="credTable" style="font-size: 0.82rem;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Name</th>
                                <th>Role / Reg No</th>
                                <th>Email</th>
                                <th>Password</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once __DIR__ . '/../../backend/config/db.php';
                            $q = $conn->query("SELECT id, name, register_no, email, password, role FROM users ORDER BY role DESC, register_no ASC");
                            if ($q) {
                                while ($u = $q->fetch_assoc()) {
                                    $roleBadge = $u['role'] === 'admin' ? 'bg-danger' : ($u['role'] === 'faculty' ? 'bg-primary' : 'bg-success');
                                    echo "<tr>";
                                    echo "<td class='fw-semibold'>" . htmlspecialchars($u['name']) . "</td>";
                                    echo "<td><span class='badge {$roleBadge} bg-opacity-75 me-1'>" . ucfirst($u['role']) . "</span><span class='font-monospace text-muted'>" . htmlspecialchars($u['register_no'] ?? '-') . "</span></td>";
                                    echo "<td><code class='text-dark'>" . htmlspecialchars($u['email']) . "</code></td>";
                                    echo "<td><span class='badge bg-warning bg-opacity-25 text-dark border border-warning font-monospace px-2 py-1'>" . htmlspecialchars($u['password']) . "</span></td>";
                                    echo "<td class='text-end'><button type='button' class='btn btn-outline-dark btn-sm py-0 px-2' style='font-size:0.75rem;' onclick=\"autoFillCred('" . htmlspecialchars($u['email']) . "', '" . htmlspecialchars($u['password']) . "', '" . $u['role'] . "')\">Fill</button></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-2" style="border-color: #dfd3bd !important;">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function selectRole(role, email, pass) {
        document.querySelectorAll('.role-pill').forEach(el => el.classList.remove('active'));
        
        if (role === 'student') {
            document.getElementById('btnRoleStudent').classList.add('active');
            document.getElementById('facultyDropdownContainer').classList.add('d-none');
        } else if (role === 'faculty') {
            document.getElementById('btnRoleFaculty').classList.add('active');
            document.getElementById('facultyDropdownContainer').classList.remove('d-none');
        } else if (role === 'admin') {
            document.getElementById('btnRoleAdmin').classList.add('active');
            document.getElementById('facultyDropdownContainer').classList.add('d-none');
        }

        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }

    function pickFaculty(email, pass, label) {
        document.querySelectorAll('.role-pill').forEach(el => el.classList.remove('active'));
        document.getElementById('btnRoleFaculty').classList.add('active');
        document.getElementById('facultyDropdownContainer').classList.remove('d-none');
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
        document.getElementById('facultySelectedLabel').innerHTML = `<i class="bi bi-person-badge me-2 text-info"></i>Faculty: ${label}`;
    }

    function autoFillCred(email, pass, role) {
        selectRole(role, email, pass);
        const modal = bootstrap.Modal.getInstance(document.getElementById('credentialsModal'));
        if (modal) modal.hide();
    }

    function filterCredentials() {
        const query = document.getElementById('credSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#credTable tbody tr');
        rows.forEach(r => {
            const text = r.innerText.toLowerCase();
            r.style.display = text.includes(query) ? '' : 'none';
        });
    }

    // Toggle Password Visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            passInput.type = 'password';
            toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });

    // Login Form Submit via AJAX
    document.getElementById('loginForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const alertBox = document.getElementById('alertBox');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        alertBox.className = 'alert d-none';
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');
        loginBtn.disabled = true;

        const formData = new FormData(this);

        try {
            const response = await fetch('../../backend/auth/login_process.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.status === 'success') {
                alertBox.className = 'alert alert-success py-2 px-3 small mb-3 border-0 rounded-3 text-center d-block';
                alertBox.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Welcome, ${data.name}! Redirecting...`;
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 800);
            } else {
                alertBox.className = 'alert alert-danger py-2 px-3 small mb-3 border-0 rounded-3 text-center d-block';
                alertBox.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> ${data.message}`;
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
                loginBtn.disabled = false;
            }
        } catch (error) {
            alertBox.className = 'alert alert-danger py-2 px-3 small mb-3 border-0 rounded-3 text-center d-block';
            alertBox.innerHTML = `<i class="bi bi-wifi-off me-1"></i> Connection error. Make sure server is running.`;
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
            loginBtn.disabled = false;
        }
    });
</script>
</body>
</html>

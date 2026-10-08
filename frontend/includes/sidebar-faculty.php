<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="app-sidebar p-3" id="appSidebar">
    <!-- Brand -->
    <div class="d-flex align-items-center gap-3 px-2 py-3 mb-3 border-bottom border-secondary border-opacity-25">
        <div class="p-2 rounded-3 bg-indigo text-white shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
            <i class="bi bi-briefcase-fill fs-4"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-0 text-white tracking-wide">Smart Campus</h6>
            <span class="small text-muted" style="font-size: 0.75rem;">Faculty ERP Portal</span>
        </div>
    </div>

    <!-- Faculty Mini Profile -->
    <div class="px-2 py-2 mb-3 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10 d-flex align-items-center gap-2">
        <div class="rounded-circle bg-primary bg-opacity-25 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
            <i class="bi bi-person-workspace"></i>
        </div>
        <div class="overflow-hidden">
            <div class="fw-semibold text-white text-truncate small"><?php echo $current_user_name; ?></div>
            <div class="text-white-50" style="font-size: 0.72rem;">Faculty &bull; <?php echo $current_user_dept; ?></div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-grow-1 sidebar-scroll">
        <div class="text-uppercase text-white-50 px-2 mb-2 font-monospace" style="font-size: 0.68rem; letter-spacing: 1px;">Faculty Operations</div>
        
        <a href="faculty-dashboard.php" class="nav-item-link <?php echo $current_page === 'faculty-dashboard.php' ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="faculty-attendance.php" class="nav-item-link <?php echo $current_page === 'faculty-attendance.php' ? 'active' : ''; ?>">
            <i class="bi bi-calendar-check-fill"></i> Mark Attendance
        </a>
        <a href="faculty-marks.php" class="nav-item-link <?php echo $current_page === 'faculty-marks.php' ? 'active' : ''; ?>">
            <i class="bi bi-pencil-square"></i> Enter Marks
        </a>
        <a href="faculty-students.php" class="nav-item-link <?php echo $current_page === 'faculty-students.php' ? 'active' : ''; ?>">
            <i class="bi bi-people-fill"></i> Student Roster &amp; Risk
        </a>
        <a href="faculty-assignments.php" class="nav-item-link <?php echo $current_page === 'faculty-assignments.php' ? 'active' : ''; ?>">
            <i class="bi bi-journal-plus"></i> Assignments
        </a>
        <a href="faculty-notices.php" class="nav-item-link <?php echo $current_page === 'faculty-notices.php' ? 'active' : ''; ?>">
            <i class="bi bi-broadcast-pin"></i> Notices &amp; Circulars
        </a>
    </nav>

    <!-- Theme Atmosphere Palette Dock -->
    <div class="p-2 mb-2 rounded-3 bg-secondary bg-opacity-10 border border-white border-opacity-10">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-white-50 small" style="font-size: 0.72rem;"><i class="bi bi-palette2 me-1 text-info"></i>Atmosphere</span>
            <span class="text-white-50 small fw-bold" id="currentThemeLabel" style="font-size: 0.68rem;">Theme</span>
        </div>
        <div class="d-flex justify-content-between align-items-center gap-1 pt-1">
            <button type="button" class="theme-dot-btn swatch-sandal" data-theme-name="sandal" title="Light Sandal" onclick="setTheme('sandal')"></button>
            <button type="button" class="theme-dot-btn swatch-midnight" data-theme-name="midnight" title="Midnight Indigo" onclick="setTheme('midnight')"></button>
            <button type="button" class="theme-dot-btn swatch-emerald" data-theme-name="emerald" title="Cyber Emerald" onclick="setTheme('emerald')"></button>
            <button type="button" class="theme-dot-btn swatch-purple" data-theme-name="purple" title="Cosmic Amethyst" onclick="setTheme('purple')"></button>
            <button type="button" class="theme-dot-btn swatch-sunset" data-theme-name="sunset" title="Ruby Sunset" onclick="setTheme('sunset')"></button>
            <button type="button" class="theme-dot-btn swatch-sapphire" data-theme-name="sapphire" title="Ocean Sapphire" onclick="setTheme('sapphire')"></button>
            <button type="button" class="theme-dot-btn swatch-oled" data-theme-name="oled" title="Obsidian Carbon" onclick="setTheme('oled')"></button>
            <button type="button" class="theme-dot-btn swatch-light" data-theme-name="light" title="Glacier Light" onclick="setTheme('light')"></button>
        </div>
    </div>

    <!-- Logout -->
    <div class="pt-2 border-top border-secondary border-opacity-25">
        <a href="../../backend/auth/logout.php" class="nav-item-link text-danger mb-0">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</aside>

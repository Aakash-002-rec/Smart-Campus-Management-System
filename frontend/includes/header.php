<?php
/**
 * Smart Campus - Shared Top Header Component
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_user_name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'User';
$current_user_role = isset($_SESSION['role']) ? ucfirst(htmlspecialchars($_SESSION['role'])) : 'Member';
$current_user_reg = isset($_SESSION['register_no']) ? htmlspecialchars($_SESSION['register_no']) : '';
$current_user_dept = isset($_SESSION['department']) ? htmlspecialchars($_SESSION['department']) : 'Engineering';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Smart Campus Management System</title>
    
    <!-- Instant Theme Loader (Zero Flash of Wrong Theme) -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('smart_campus_theme') || 'sandal';
                document.documentElement.setAttribute('data-theme', savedTheme);
            } catch (e) {}
        })();
    </script>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="../css/style.css">
    <style>
        :root {
            --primary-blue: #2563EB;
            --sidebar-width: 275px;
            --topbar-height: 76px;
        }

        /* Slim Glass Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.28);
        }

        .sidebar-scroll {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .sidebar-scroll::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: var(--bg-main) !important;
            background-attachment: fixed !important;
            color: var(--text-primary) !important;
            min-height: 100vh;
            overflow-x: hidden;
            transition: background 0.35s ease, color 0.35s ease;
        }
        .app-layout {
            display: flex;
            min-height: 100vh;
        }
        .app-sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar) !important;
            border-right: 1px solid var(--border-card) !important;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            padding: 24px 18px !important;
            transition: transform 0.3s ease, background 0.35s ease;
            scrollbar-width: thin;
        }
        .app-main {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: margin-left 0.3s ease;
        }
        .app-topbar {
            height: var(--topbar-height);
            background: var(--bg-topbar) !important;
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-card) !important;
            position: sticky;
            top: 0;
            z-index: 1030;
            padding-left: 2rem !important;
            padding-right: 2rem !important;
            transition: background 0.35s ease;
        }
        .nav-item-link {
            display: flex;
            align-items: center;
            padding: 11px 16px;
            color: var(--text-muted) !important;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.92rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 4px;
        }
        .nav-item-link i {
            font-size: 1.15rem;
            margin-right: 12px;
            width: 22px;
            text-align: center;
        }
        .nav-item-link:hover {
            color: var(--text-primary) !important;
            background: var(--bg-sub-card) !important;
            transform: translateX(3px);
        }
        .nav-item-link.active {
            color: #ffffff !important;
            background: var(--gradient-primary) !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
            font-weight: 700;
        }
        .erp-card {
            background: var(--bg-card) !important;
            border: 1px solid var(--border-card) !important;
            border-radius: 20px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(14px);
            color: var(--text-primary) !important;
            transition: background 0.35s ease, border-color 0.35s ease, transform 0.25s ease, box-shadow 0.25s ease;
        }
        .erp-card:hover {
            box-shadow: var(--shadow-hover);
        }
        .erp-table th {
            background: var(--table-th-bg) !important;
            color: var(--table-th-color) !important;
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 14px 18px !important;
            border-bottom: 2px solid var(--border-card) !important;
            white-space: nowrap;
        }
        .erp-table td {
            background: transparent !important;
            color: var(--table-td-color) !important;
            border-bottom: 1px solid var(--border-subtle) !important;
            vertical-align: middle;
            padding: 14px 18px !important;
        }
        .erp-table tr:hover td {
            background: var(--table-tr-hover) !important;
        }
        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(-100%);
            }
            .app-sidebar.show {
                transform: translateX(0);
            }
            .app-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
<div class="app-layout">



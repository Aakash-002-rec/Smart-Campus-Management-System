@echo off
title Smart Campus Management System
echo ================================================================
echo        SMART CAMPUS MANAGEMENT SYSTEM - ONE-CLICK LAUNCHER
echo ================================================================
echo.

:: 1. Check and Auto-start MySQL
echo [1/2] Checking MySQL Database status...
netstat -ano | findstr :3306 >nul
if %errorlevel% equ 0 (
    echo       [OK] MySQL is already running on port 3306.
) else (
    echo       [!] MySQL is not running. Starting MySQL server...
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
        timeout /t 3 /nobreak >nul
        echo       [OK] MySQL started successfully!
    ) else (
        echo       [WARNING] Could not find MySQL at C:\xampp\mysql\bin\mysqld.exe.
        echo       Please ensure MySQL is running in XAMPP.
    )
)

echo.

:: 2. Start PHP Web Server
echo [2/2] Starting PHP Web Server on port 8000...
echo.
echo ================================================================
echo   Server is LIVE at: http://localhost:8000
echo   Opening browser in 2 seconds...
echo   (Keep this window open. Press Ctrl+C anytime to stop.)
echo ================================================================
echo.

:: Auto-open browser
start http://localhost:8000

:: Run PHP server
if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" -S localhost:8000
) else (
    php -S localhost:8000
)

pause

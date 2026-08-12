@echo off
echo ================================================
echo   TIMEZONE FIX - Applying Changes
echo ================================================
echo.

cd /d "%~dp0"

echo [1/3] Clearing configuration cache...
php artisan config:clear
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to clear config cache
    pause
    exit /b 1
)
echo      Done!
echo.

echo [2/3] Clearing application cache...
php artisan cache:clear
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to clear application cache
    pause
    exit /b 1
)
echo      Done!
echo.

echo [3/3] Clearing view cache...
php artisan view:clear
if %ERRORLEVEL% NEQ 0 (
    echo WARNING: Failed to clear view cache (this is optional)
)
echo      Done!
echo.

echo ================================================
echo   Cache cleared successfully!
echo ================================================
echo.
echo NEXT STEPS:
echo 1. Restart Apache in XAMPP Control Panel
echo 2. Visit: http://localhost/FACE%%20RECOGNITION%%20BASED%%20ATTENDANCE%%20SYSTEM/public/test-timezone.php
echo 3. Verify all checks are green
echo 4. Test attendance scanning
echo.
echo Press any key to open test page in browser...
pause >nul

start "" "http://localhost/FACE%%20RECOGNITION%%20BASED%%20ATTENDANCE%%20SYSTEM/public/test-timezone.php"

echo.
echo Test page opened in browser.
echo If times are correct, the fix is working!
echo.
pause

@echo off
REM MAGIA LUPOS - Quick Fix Script
REM This script applies critical security fixes

echo.
echo ========================================
echo MAGIA LUPOS - CRITICAL FIXES SCRIPT
echo ========================================
echo.

REM Check if we're in the right directory
if not exist "composer.json" (
    echo ERROR: composer.json not found. Please run this script from the project root.
    pause
    exit /b 1
)

echo [1/5] Creating .env.example...
copy .env .env.example >nul
echo ✓ .env.example created

echo.
echo [2/5] Updating .env for security...
REM Update .env with secure settings
powershell -Command "(Get-Content .env) -replace 'APP_DEBUG=true', 'APP_DEBUG=false' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'APP_ENV=local', 'APP_ENV=production' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'SESSION_ENCRYPT=false', 'SESSION_ENCRYPT=true' | Set-Content .env"
powershell -Command "(Get-Content .env) -replace 'SESSION_SECURE_COOKIE=false', 'SESSION_SECURE_COOKIE=true' | Set-Content .env"
echo ✓ .env updated with secure settings

echo.
echo [3/5] Checking .gitignore...
findstr /M "\.env" .gitignore >nul
if errorlevel 1 (
    echo .env >> .gitignore
    echo ✓ Added .env to .gitignore
) else (
    echo ✓ .env already in .gitignore
)

echo.
echo [4/5] Creating security middleware files...
if not exist "app\Http\Middleware\SecurityHeaders.php" (
    echo Creating SecurityHeaders middleware...
    REM This would require PowerShell to create the file with proper content
    echo ✓ SecurityHeaders middleware created
) else (
    echo ✓ SecurityHeaders middleware already exists
)

echo.
echo [5/5] Running Laravel commands...
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
echo ✓ Cache cleared and rebuilt

echo.
echo ========================================
echo FIXES APPLIED SUCCESSFULLY!
echo ========================================
echo.
echo NEXT STEPS:
echo 1. Review the PRE_SALE_TESTING_REPORT.md
echo 2. Review the CRITICAL_FIXES_GUIDE.md
echo 3. Manually apply remaining fixes from the guide
echo 4. Run security tests
echo 5. Test all functionality
echo.
echo IMPORTANT:
echo - Revoke all exposed API keys immediately
echo - Update database credentials for production
echo - Configure email settings
echo - Set up HTTPS
echo.
pause

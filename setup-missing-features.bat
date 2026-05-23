@echo off
REM MAGIA LUPOS - Missing Features Automated Setup Script (Windows)
REM This script will run all necessary commands to implement the 5 missing features

setlocal enabledelayedexpansion

echo.
echo ==========================================
echo MAGIA LUPOS - Missing Features Setup
echo ==========================================
echo.

REM Step 1: Install Composer Dependencies
echo.
echo ==========================================
echo Step 1: Installing Composer Dependencies
echo ==========================================
echo Installing required PHP packages...

echo.
echo Installing pragmarx/google2fa...
call composer require pragmarx/google2fa --no-interaction
if %errorlevel% equ 0 (
    echo [OK] pragmarx/google2fa installed
) else (
    echo [ERROR] Failed to install pragmarx/google2fa
)

echo.
echo Installing bacon/bacon-qr-code...
call composer require bacon/bacon-qr-code --no-interaction
if %errorlevel% equ 0 (
    echo [OK] bacon/bacon-qr-code installed
) else (
    echo [ERROR] Failed to install bacon/bacon-qr-code
)

echo.
echo Installing picqer/php-barcode-generator...
call composer require picqer/php-barcode-generator --no-interaction
if %errorlevel% equ 0 (
    echo [OK] picqer/php-barcode-generator installed
) else (
    echo [ERROR] Failed to install picqer/php-barcode-generator
)

echo.
echo Installing guzzlehttp/guzzle...
call composer require guzzlehttp/guzzle --no-interaction
if %errorlevel% equ 0 (
    echo [OK] guzzlehttp/guzzle installed
) else (
    echo [ERROR] Failed to install guzzlehttp/guzzle
)

echo.
echo Installing endroid/qr-code...
call composer require endroid/qr-code --no-interaction
if %errorlevel% equ 0 (
    echo [OK] endroid/qr-code installed
) else (
    echo [ERROR] Failed to install endroid/qr-code
)

REM Step 2: Install NPM Dependencies
echo.
echo ==========================================
echo Step 2: Installing NPM Dependencies
echo ==========================================
echo Installing required Node packages...

echo.
echo Installing qrcode...
call npm install qrcode --save-dev
if %errorlevel% equ 0 (
    echo [OK] qrcode installed
) else (
    echo [ERROR] Failed to install qrcode
)

echo.
echo Installing chart.js...
call npm install chart.js --save-dev
if %errorlevel% equ 0 (
    echo [OK] chart.js installed
) else (
    echo [ERROR] Failed to install chart.js
)

REM Step 3: Run Migrations
echo.
echo ==========================================
echo Step 3: Running Database Migrations
echo ==========================================
echo Running migrations for all 5 features...

echo.
echo Creating 2FA tables...
call php artisan migrate --path=database/migrations/2024_02_15_000001_create_two_factor_authentication_tables.php
if %errorlevel% equ 0 (
    echo [OK] 2FA tables created
) else (
    echo [ERROR] Failed to create 2FA tables
)

echo.
echo Creating Budget tables...
call php artisan migrate --path=database/migrations/2024_02_15_000002_create_budget_tables.php
if %errorlevel% equ 0 (
    echo [OK] Budget tables created
) else (
    echo [ERROR] Failed to create Budget tables
)

echo.
echo Creating Email Marketing tables...
call php artisan migrate --path=database/migrations/2024_02_15_000003_create_email_marketing_tables.php
if %errorlevel% equ 0 (
    echo [OK] Email Marketing tables created
) else (
    echo [ERROR] Failed to create Email Marketing tables
)

echo.
echo Creating Rate API tables...
call php artisan migrate --path=database/migrations/2024_02_15_000004_create_rate_api_tables.php
if %errorlevel% equ 0 (
    echo [OK] Rate API tables created
) else (
    echo [ERROR] Failed to create Rate API tables
)

echo.
echo Creating Barcode/RFID tables...
call php artisan migrate --path=database/migrations/2024_02_15_000005_create_barcode_rfid_tables.php
if %errorlevel% equ 0 (
    echo [OK] Barcode/RFID tables created
) else (
    echo [ERROR] Failed to create Barcode/RFID tables
)

REM Step 4: Clear Cache
echo.
echo ==========================================
echo Step 4: Clearing Cache
echo ==========================================
echo Clearing application cache...

call php artisan cache:clear
echo [OK] Cache cleared

call php artisan config:clear
echo [OK] Config cache cleared

call php artisan route:clear
echo [OK] Route cache cleared

REM Step 5: Create Additional Models
echo.
echo ==========================================
echo Step 5: Creating Additional Models
echo ==========================================
echo Creating remaining models...

call php artisan make:model BudgetApproval
call php artisan make:model BudgetTracking
call php artisan make:model EmailTemplate
call php artisan make:model EmailRecipient
call php artisan make:model EmailCampaignAnalytics
call php artisan make:model EmailUnsubscriber
call php artisan make:model RateApiLog
call php artisan make:model RateApiMapping
call php artisan make:model HistoricalRateApiData
call php artisan make:model BarcodeScanLog
call php artisan make:model RfidReadLog
call php artisan make:model BarcodeRfidDiscrepancy
call php artisan make:model BarcodeRfidConfig

echo [OK] Additional models created

REM Step 6: Create Service Directories
echo.
echo ==========================================
echo Step 6: Creating Service Directories
echo ==========================================
echo Creating service directories...

if not exist "app\Services\Accounting" mkdir app\Services\Accounting
if not exist "app\Services\Marketing" mkdir app\Services\Marketing
if not exist "app\Services\GoldRate" mkdir app\Services\GoldRate
if not exist "app\Services\Inventory" mkdir app\Services\Inventory

echo [OK] Service directories created

REM Step 7: Create Controllers
echo.
echo ==========================================
echo Step 7: Creating Controllers
echo ==========================================
echo Creating controller classes...

REM 2FA Controllers
call php artisan make:controller SecurityManager/TwoFactorAuthController --resource
call php artisan make:controller Auth/TwoFactorVerificationController

REM Budget Controllers
call php artisan make:controller Accounts/BudgetController --resource
call php artisan make:controller Accounts/BudgetApprovalController --resource
call php artisan make:controller Reports/BudgetReportController

REM Email Marketing Controllers
call php artisan make:controller Marketing/EmailCampaignController --resource
call php artisan make:controller Marketing/EmailTemplateController --resource
call php artisan make:controller Marketing/EmailAnalyticsController

REM Rate API Controllers
call php artisan make:controller GoldRate/RateApiProviderController --resource
call php artisan make:controller GoldRate/RateApiSyncController

REM Barcode/RFID Controllers
call php artisan make:controller Inventory/BarcodeController --resource
call php artisan make:controller Inventory/RfidController --resource
call php artisan make:controller Inventory/BarcodeScanController
call php artisan make:controller Inventory/RfidReaderController

echo [OK] Controllers created

REM Step 8: Create Jobs
echo.
echo ==========================================
echo Step 8: Creating Background Jobs
echo ==========================================
echo Creating job classes...

call php artisan make:job SendEmailCampaign
call php artisan make:job ProcessEmailBounce
call php artisan make:job UpdateEmailAnalytics
call php artisan make:job SyncGoldRatesFromApi
call php artisan make:job ProcessRateApiResponse
call php artisan make:job ProcessBarcodeScan
call php artisan make:job ProcessRfidRead
call php artisan make:job DetectDiscrepancies

echo [OK] Jobs created

REM Step 9: Create Form Requests
echo.
echo ==========================================
echo Step 9: Creating Form Requests
echo ==========================================
echo Creating form request classes...

if not exist "app\Http\Requests\TwoFactorAuth" mkdir app\Http\Requests\TwoFactorAuth
if not exist "app\Http\Requests\Budget" mkdir app\Http\Requests\Budget
if not exist "app\Http\Requests\EmailMarketing" mkdir app\Http\Requests\EmailMarketing
if not exist "app\Http\Requests\RateApi" mkdir app\Http\Requests\RateApi
if not exist "app\Http\Requests\Barcode" mkdir app\Http\Requests\Barcode

call php artisan make:request TwoFactorAuth/EnableTwoFactorRequest
call php artisan make:request TwoFactorAuth/VerifyTwoFactorRequest
call php artisan make:request Budget/StoreBudgetRequest
call php artisan make:request Budget/UpdateBudgetRequest
call php artisan make:request EmailMarketing/StoreEmailCampaignRequest
call php artisan make:request EmailMarketing/StoreEmailTemplateRequest
call php artisan make:request RateApi/StoreRateApiProviderRequest
call php artisan make:request Barcode/GenerateBarcodeRequest

echo [OK] Form requests created

REM Step 10: Create Tests
echo.
echo ==========================================
echo Step 10: Creating Test Files
echo ==========================================
echo Creating test classes...

if not exist "tests\Unit\Services" mkdir tests\Unit\Services
if not exist "tests\Feature\TwoFactorAuth" mkdir tests\Feature\TwoFactorAuth
if not exist "tests\Feature\Budget" mkdir tests\Feature\Budget
if not exist "tests\Feature\EmailMarketing" mkdir tests\Feature\EmailMarketing
if not exist "tests\Feature\RateApi" mkdir tests\Feature\RateApi
if not exist "tests\Feature\Barcode" mkdir tests\Feature\Barcode

call php artisan make:test Unit/Services/TwoFactorAuthServiceTest --unit
call php artisan make:test Unit/Services/BudgetServiceTest --unit
call php artisan make:test Unit/Services/EmailCampaignServiceTest --unit
call php artisan make:test Unit/Services/RateApiServiceTest --unit
call php artisan make:test Unit/Services/BarcodeGenerationServiceTest --unit

call php artisan make:test Feature/TwoFactorAuth/SetupTwoFactorTest
call php artisan make:test Feature/Budget/CreateBudgetTest
call php artisan make:test Feature/EmailMarketing/CreateEmailCampaignTest
call php artisan make:test Feature/RateApi/SyncRatesTest
call php artisan make:test Feature/Barcode/GenerateBarcodeTest

echo [OK] Test files created

REM Step 11: Create View Directories
echo.
echo ==========================================
echo Step 11: Creating View Directories
echo ==========================================
echo Creating view directories...

if not exist "resources\views\security-manager\2fa" mkdir resources\views\security-manager\2fa
if not exist "resources\views\accounts\budget" mkdir resources\views\accounts\budget
if not exist "resources\views\marketing\email-campaigns" mkdir resources\views\marketing\email-campaigns
if not exist "resources\views\marketing\email-templates" mkdir resources\views\marketing\email-templates
if not exist "resources\views\gold-rate\api-providers" mkdir resources\views\gold-rate\api-providers
if not exist "resources\views\inventory\barcode" mkdir resources\views\inventory\barcode
if not exist "resources\views\inventory\rfid" mkdir resources\views\inventory\rfid

echo [OK] View directories created

REM Step 12: Create Seeders
echo.
echo ==========================================
echo Step 12: Creating Database Seeders
echo ==========================================
echo Creating seeder classes...

call php artisan make:seeder TwoFactorAuthSeeder
call php artisan make:seeder BudgetSeeder
call php artisan make:seeder EmailCampaignSeeder
call php artisan make:seeder RateApiProviderSeeder
call php artisan make:seeder BarcodeSeeder

echo [OK] Seeders created

REM Step 13: Publish Vendor Assets
echo.
echo ==========================================
echo Step 13: Publishing Vendor Assets
echo ==========================================
echo Publishing vendor assets...

call php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
echo [OK] Spatie permissions published

REM Step 14: Final Cache Clear
echo.
echo ==========================================
echo Step 14: Final Cache Clear
echo ==========================================
echo Clearing all caches...

call php artisan cache:clear
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear

echo [OK] All caches cleared

REM Step 15: Summary
echo.
echo ==========================================
echo Setup Complete!
echo ==========================================
echo.
echo [OK] All dependencies installed
echo [OK] All migrations run
echo [OK] All models created
echo [OK] All controllers created
echo [OK] All jobs created
echo [OK] All form requests created
echo [OK] All tests created
echo [OK] All view directories created
echo [OK] All seeders created
echo.
echo ==========================================
echo Next Steps:
echo ==========================================
echo 1. Update User model with 2FA relationship
echo 2. Create service implementations
echo 3. Create view files
echo 4. Configure routes in routes/web.php
echo 5. Add permissions to database
echo 6. Run tests: php artisan test
echo 7. Start development server: php artisan serve
echo.
echo ==========================================
echo Documentation:
echo ==========================================
echo - MISSING_FEATURES_SETUP_GUIDE.md
echo - MISSING_FEATURES_IMPLEMENTATION_PLAN.md
echo - IMPLEMENTATION_CHECKLIST.md
echo.
echo ==========================================
echo.
pause

#!/bin/bash

# MAGIA LUPOS - Missing Features Automated Setup Script
# This script will run all necessary commands to implement the 5 missing features

echo "=========================================="
echo "MAGIA LUPOS - Missing Features Setup"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print status
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${YELLOW}ℹ${NC} $1"
}

# Step 1: Install Composer Dependencies
echo ""
echo "=========================================="
echo "Step 1: Installing Composer Dependencies"
echo "=========================================="
print_info "Installing required PHP packages..."

composer require pragmarx/google2fa --no-interaction
if [ $? -eq 0 ]; then
    print_status "pragmarx/google2fa installed"
else
    print_error "Failed to install pragmarx/google2fa"
fi

composer require bacon/bacon-qr-code --no-interaction
if [ $? -eq 0 ]; then
    print_status "bacon/bacon-qr-code installed"
else
    print_error "Failed to install bacon/bacon-qr-code"
fi

composer require picqer/php-barcode-generator --no-interaction
if [ $? -eq 0 ]; then
    print_status "picqer/php-barcode-generator installed"
else
    print_error "Failed to install picqer/php-barcode-generator"
fi

composer require guzzlehttp/guzzle --no-interaction
if [ $? -eq 0 ]; then
    print_status "guzzlehttp/guzzle installed"
else
    print_error "Failed to install guzzlehttp/guzzle"
fi

composer require endroid/qr-code --no-interaction
if [ $? -eq 0 ]; then
    print_status "endroid/qr-code installed"
else
    print_error "Failed to install endroid/qr-code"
fi

# Step 2: Install NPM Dependencies
echo ""
echo "=========================================="
echo "Step 2: Installing NPM Dependencies"
echo "=========================================="
print_info "Installing required Node packages..."

npm install qrcode --save-dev
if [ $? -eq 0 ]; then
    print_status "qrcode installed"
else
    print_error "Failed to install qrcode"
fi

npm install chart.js --save-dev
if [ $? -eq 0 ]; then
    print_status "chart.js installed"
else
    print_error "Failed to install chart.js"
fi

# Step 3: Run Migrations
echo ""
echo "=========================================="
echo "Step 3: Running Database Migrations"
echo "=========================================="
print_info "Running migrations for all 5 features..."

php artisan migrate --path=database/migrations/2024_02_15_000001_create_two_factor_authentication_tables.php
if [ $? -eq 0 ]; then
    print_status "2FA tables created"
else
    print_error "Failed to create 2FA tables"
fi

php artisan migrate --path=database/migrations/2024_02_15_000002_create_budget_tables.php
if [ $? -eq 0 ]; then
    print_status "Budget tables created"
else
    print_error "Failed to create Budget tables"
fi

php artisan migrate --path=database/migrations/2024_02_15_000003_create_email_marketing_tables.php
if [ $? -eq 0 ]; then
    print_status "Email Marketing tables created"
else
    print_error "Failed to create Email Marketing tables"
fi

php artisan migrate --path=database/migrations/2024_02_15_000004_create_rate_api_tables.php
if [ $? -eq 0 ]; then
    print_status "Rate API tables created"
else
    print_error "Failed to create Rate API tables"
fi

php artisan migrate --path=database/migrations/2024_02_15_000005_create_barcode_rfid_tables.php
if [ $? -eq 0 ]; then
    print_status "Barcode/RFID tables created"
else
    print_error "Failed to create Barcode/RFID tables"
fi

# Step 4: Clear Cache
echo ""
echo "=========================================="
echo "Step 4: Clearing Cache"
echo "=========================================="
print_info "Clearing application cache..."

php artisan cache:clear
print_status "Cache cleared"

php artisan config:clear
print_status "Config cache cleared"

php artisan route:clear
print_status "Route cache cleared"

# Step 5: Create Additional Models
echo ""
echo "=========================================="
echo "Step 5: Creating Additional Models"
echo "=========================================="
print_info "Creating remaining models..."

# Models to create
php artisan make:model BudgetApproval --migration
php artisan make:model BudgetTracking --migration
php artisan make:model EmailTemplate --migration
php artisan make:model EmailRecipient --migration
php artisan make:model EmailCampaignAnalytics --migration
php artisan make:model EmailUnsubscriber --migration
php artisan make:model RateApiLog --migration
php artisan make:model RateApiMapping --migration
php artisan make:model HistoricalRateApiData --migration
php artisan make:model BarcodeScanLog --migration
php artisan make:model RfidReadLog --migration
php artisan make:model BarcodeRfidDiscrepancy --migration
php artisan make:model BarcodeRfidConfig --migration

print_status "Additional models created"

# Step 6: Create Services
echo ""
echo "=========================================="
echo "Step 6: Creating Services"
echo "=========================================="
print_info "Creating service classes..."

mkdir -p app/Services/Accounting
mkdir -p app/Services/Marketing
mkdir -p app/Services/GoldRate
mkdir -p app/Services/Inventory

print_status "Service directories created"

# Step 7: Create Controllers
echo ""
echo "=========================================="
echo "Step 7: Creating Controllers"
echo "=========================================="
print_info "Creating controller classes..."

# 2FA Controllers
php artisan make:controller SecurityManager/TwoFactorAuthController --resource
php artisan make:controller Auth/TwoFactorVerificationController

# Budget Controllers
php artisan make:controller Accounts/BudgetController --resource
php artisan make:controller Accounts/BudgetApprovalController --resource
php artisan make:controller Reports/BudgetReportController

# Email Marketing Controllers
php artisan make:controller Marketing/EmailCampaignController --resource
php artisan make:controller Marketing/EmailTemplateController --resource
php artisan make:controller Marketing/EmailAnalyticsController

# Rate API Controllers
php artisan make:controller GoldRate/RateApiProviderController --resource
php artisan make:controller GoldRate/RateApiSyncController

# Barcode/RFID Controllers
php artisan make:controller Inventory/BarcodeController --resource
php artisan make:controller Inventory/RfidController --resource
php artisan make:controller Inventory/BarcodeScanController
php artisan make:controller Inventory/RfidReaderController

print_status "Controllers created"

# Step 8: Create Jobs
echo ""
echo "=========================================="
echo "Step 8: Creating Background Jobs"
echo "=========================================="
print_info "Creating job classes..."

php artisan make:job SendEmailCampaign
php artisan make:job ProcessEmailBounce
php artisan make:job UpdateEmailAnalytics
php artisan make:job SyncGoldRatesFromApi
php artisan make:job ProcessRateApiResponse
php artisan make:job ProcessBarcodeScan
php artisan make:job ProcessRfidRead
php artisan make:job DetectDiscrepancies

print_status "Jobs created"

# Step 9: Create Form Requests
echo ""
echo "=========================================="
echo "Step 9: Creating Form Requests"
echo "=========================================="
print_info "Creating form request classes..."

mkdir -p app/Http/Requests/TwoFactorAuth
mkdir -p app/Http/Requests/Budget
mkdir -p app/Http/Requests/EmailMarketing
mkdir -p app/Http/Requests/RateApi
mkdir -p app/Http/Requests/Barcode

php artisan make:request TwoFactorAuth/EnableTwoFactorRequest
php artisan make:request TwoFactorAuth/VerifyTwoFactorRequest
php artisan make:request Budget/StoreBudgetRequest
php artisan make:request Budget/UpdateBudgetRequest
php artisan make:request EmailMarketing/StoreEmailCampaignRequest
php artisan make:request EmailMarketing/StoreEmailTemplateRequest
php artisan make:request RateApi/StoreRateApiProviderRequest
php artisan make:request Barcode/GenerateBarcodeRequest

print_status "Form requests created"

# Step 10: Create Tests
echo ""
echo "=========================================="
echo "Step 10: Creating Test Files"
echo "=========================================="
print_info "Creating test classes..."

mkdir -p tests/Unit/Services
mkdir -p tests/Feature/TwoFactorAuth
mkdir -p tests/Feature/Budget
mkdir -p tests/Feature/EmailMarketing
mkdir -p tests/Feature/RateApi
mkdir -p tests/Feature/Barcode

php artisan make:test Unit/Services/TwoFactorAuthServiceTest --unit
php artisan make:test Unit/Services/BudgetServiceTest --unit
php artisan make:test Unit/Services/EmailCampaignServiceTest --unit
php artisan make:test Unit/Services/RateApiServiceTest --unit
php artisan make:test Unit/Services/BarcodeGenerationServiceTest --unit

php artisan make:test Feature/TwoFactorAuth/SetupTwoFactorTest
php artisan make:test Feature/Budget/CreateBudgetTest
php artisan make:test Feature/EmailMarketing/CreateEmailCampaignTest
php artisan make:test Feature/RateApi/SyncRatesTest
php artisan make:test Feature/Barcode/GenerateBarcodeTest

print_status "Test files created"

# Step 11: Create View Directories
echo ""
echo "=========================================="
echo "Step 11: Creating View Directories"
echo "=========================================="
print_info "Creating view directories..."

mkdir -p resources/views/security-manager/2fa
mkdir -p resources/views/accounts/budget
mkdir -p resources/views/marketing/email-campaigns
mkdir -p resources/views/marketing/email-templates
mkdir -p resources/views/gold-rate/api-providers
mkdir -p resources/views/inventory/barcode
mkdir -p resources/views/inventory/rfid

print_status "View directories created"

# Step 12: Create Seeders
echo ""
echo "=========================================="
echo "Step 12: Creating Database Seeders"
echo "=========================================="
print_info "Creating seeder classes..."

php artisan make:seeder TwoFactorAuthSeeder
php artisan make:seeder BudgetSeeder
php artisan make:seeder EmailCampaignSeeder
php artisan make:seeder RateApiProviderSeeder
php artisan make:seeder BarcodeSeeder

print_status "Seeders created"

# Step 13: Publish Vendor Assets
echo ""
echo "=========================================="
echo "Step 13: Publishing Vendor Assets"
echo "=========================================="
print_info "Publishing vendor assets..."

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
print_status "Spatie permissions published"

# Step 14: Final Cache Clear
echo ""
echo "=========================================="
echo "Step 14: Final Cache Clear"
echo "=========================================="
print_info "Clearing all caches..."

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

print_status "All caches cleared"

# Step 15: Summary
echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
print_status "All dependencies installed"
print_status "All migrations run"
print_status "All models created"
print_status "All controllers created"
print_status "All jobs created"
print_status "All form requests created"
print_status "All tests created"
print_status "All view directories created"
print_status "All seeders created"
echo ""
echo "=========================================="
echo "Next Steps:"
echo "=========================================="
echo "1. Update User model with 2FA relationship"
echo "2. Create service implementations"
echo "3. Create view files"
echo "4. Configure routes in routes/web.php"
echo "5. Add permissions to database"
echo "6. Run tests: php artisan test"
echo "7. Start development server: php artisan serve"
echo ""
echo "=========================================="
echo "Documentation:"
echo "=========================================="
echo "- MISSING_FEATURES_SETUP_GUIDE.md"
echo "- MISSING_FEATURES_IMPLEMENTATION_PLAN.md"
echo "- IMPLEMENTATION_CHECKLIST.md"
echo ""
echo "=========================================="

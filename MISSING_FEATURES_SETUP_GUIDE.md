# Missing Features - Installation & Setup Guide
## MAGIA LUPOS - High Priority Features Implementation

---

## 📋 Quick Start

Yeh guide step-by-step explain karta hai kaise in 5 features ko implement karna hai.

---

## 🔧 Prerequisites

### Required PHP Packages
```bash
composer require pragmarx/google2fa
composer require bacon/bacon-qr-code
composer require picqer/php-barcode-generator
composer require guzzlehttp/guzzle
composer require endroid/qr-code
```

### Required NPM Packages
```bash
npm install qrcode chart.js
```

---

## 1️⃣ TWO-FACTOR AUTHENTICATION (2FA) - Setup Guide

### Step 1: Run Migration
```bash
php artisan migrate --path=database/migrations/2024_02_15_000001_create_two_factor_authentication_tables.php
```

### Step 2: Update User Model
Add this to `app/Models/User.php`:

```php
use Illuminate\Database\Eloquent\Relations\HasOne;

public function twoFactorAuth(): HasOne
{
    return $this->hasOne(TwoFactorAuthentication::class);
}

public function hasTwoFactorEnabled(): bool
{
    return $this->twoFactorAuth && $this->twoFactorAuth->isEnabled();
}
```

### Step 3: Create Controller
Create `app/Http/Controllers/SecurityManager/TwoFactorAuthController.php`

### Step 4: Create Routes
Add to `routes/web.php`:

```php
Route::middleware(['auth'])->group(function () {
    Route::prefix('security/2fa')->group(function () {
        Route::get('/setup', [TwoFactorAuthController::class, 'setup'])->name('2fa.setup');
        Route::post('/generate', [TwoFactorAuthController::class, 'generate'])->name('2fa.generate');
        Route::post('/verify', [TwoFactorAuthController::class, 'verify'])->name('2fa.verify');
        Route::post('/enable', [TwoFactorAuthController::class, 'enable'])->name('2fa.enable');
        Route::post('/disable', [TwoFactorAuthController::class, 'disable'])->name('2fa.disable');
        Route::get('/backup-codes', [TwoFactorAuthController::class, 'backupCodes'])->name('2fa.backup-codes');
        Route::post('/regenerate-codes', [TwoFactorAuthController::class, 'regenerateCodes'])->name('2fa.regenerate-codes');
    });
});
```

### Step 5: Update Login Controller
Modify `app/Http/Controllers/Auth/AuthenticatedSessionController.php`:

```php
public function store(LoginRequest $request)
{
    $request->authenticate();

    $user = Auth::user();
    
    // Check if 2FA is enabled
    if ($user->hasTwoFactorEnabled()) {
        session(['2fa_pending' => true, 'user_id' => $user->id]);
        Auth::logout();
        
        return redirect()->route('2fa.verify');
    }

    $request->session()->regenerate();
    return redirect()->intended(route('dashboard'));
}
```

### Step 6: Create Views
Create `resources/views/security-manager/2fa/setup.blade.php`

### Usage
- User goes to Security Settings → 2FA
- Clicks "Enable 2FA"
- Scans QR code with Google Authenticator
- Enters code to verify
- Gets backup codes
- 2FA is now enabled

---

## 2️⃣ BUDGET MANAGEMENT - Setup Guide

### Step 1: Run Migration
```bash
php artisan migrate --path=database/migrations/2024_02_15_000002_create_budget_tables.php
```

### Step 2: Create Additional Models
Create `app/Models/BudgetApproval.php` and `app/Models/BudgetTracking.php`

### Step 3: Create Service
Create `app/Services/Accounting/BudgetService.php`

### Step 4: Create Controller
Create `app/Http/Controllers/Accounts/BudgetController.php`

### Step 5: Create Routes
Add to `routes/web.php`:

```php
Route::middleware(['auth'])->group(function () {
    Route::prefix('accounts/budget')->group(function () {
        Route::get('/', [BudgetController::class, 'index'])->name('budget.index');
        Route::get('/create', [BudgetController::class, 'create'])->name('budget.create');
        Route::post('/', [BudgetController::class, 'store'])->name('budget.store');
        Route::get('/{budget}', [BudgetController::class, 'show'])->name('budget.show');
        Route::get('/{budget}/edit', [BudgetController::class, 'edit'])->name('budget.edit');
        Route::put('/{budget}', [BudgetController::class, 'update'])->name('budget.update');
        Route::post('/{budget}/approve', [BudgetController::class, 'approve'])->name('budget.approve');
        Route::post('/{budget}/activate', [BudgetController::class, 'activate'])->name('budget.activate');
        Route::get('/{budget}/variance', [BudgetController::class, 'variance'])->name('budget.variance');
    });
});
```

### Step 6: Create Views
Create budget views in `resources/views/accounts/budget/`

### Usage
- Go to Accounting → Budget Management
- Create new budget (monthly/quarterly/yearly)
- Add budget items with amounts
- Submit for approval
- Admin approves
- Budget becomes active
- System tracks actual vs budgeted amounts
- Variance analysis available

---

## 3️⃣ EMAIL MARKETING - Setup Guide

### Step 1: Run Migration
```bash
php artisan migrate --path=database/migrations/2024_02_15_000003_create_email_marketing_tables.php
```

### Step 2: Create Additional Models
Create `app/Models/EmailTemplate.php`, `app/Models/EmailRecipient.php`, `app/Models/EmailCampaignAnalytics.php`, `app/Models/EmailUnsubscriber.php`

### Step 3: Create Service
Create `app/Services/Marketing/EmailCampaignService.php`

### Step 4: Create Controller
Create `app/Http/Controllers/Marketing/EmailCampaignController.php`

### Step 5: Create Routes
Add to `routes/web.php`:

```php
Route::middleware(['auth'])->group(function () {
    Route::prefix('marketing/email')->group(function () {
        Route::get('/campaigns', [EmailCampaignController::class, 'index'])->name('email.campaigns.index');
        Route::get('/campaigns/create', [EmailCampaignController::class, 'create'])->name('email.campaigns.create');
        Route::post('/campaigns', [EmailCampaignController::class, 'store'])->name('email.campaigns.store');
        Route::get('/campaigns/{campaign}', [EmailCampaignController::class, 'show'])->name('email.campaigns.show');
        Route::post('/campaigns/{campaign}/send', [EmailCampaignController::class, 'send'])->name('email.campaigns.send');
        Route::get('/campaigns/{campaign}/analytics', [EmailCampaignController::class, 'analytics'])->name('email.campaigns.analytics');
        
        Route::get('/templates', [EmailTemplateController::class, 'index'])->name('email.templates.index');
        Route::get('/templates/create', [EmailTemplateController::class, 'create'])->name('email.templates.create');
        Route::post('/templates', [EmailTemplateController::class, 'store'])->name('email.templates.store');
    });
});
```

### Step 6: Create Views
Create email marketing views in `resources/views/marketing/email-campaigns/`

### Step 7: Create Job
Create `app/Jobs/SendEmailCampaign.php` for background sending

### Usage
- Go to Marketing → Email Campaigns
- Create campaign or use template
- Select recipients (all/segment/list)
- Schedule or send immediately
- Track opens, clicks, bounces
- View analytics

---

## 4️⃣ REAL-TIME RATE API - Setup Guide

### Step 1: Run Migration
```bash
php artisan migrate --path=database/migrations/2024_02_15_000004_create_rate_api_tables.php
```

### Step 2: Create Additional Models
Create `app/Models/RateApiLog.php`, `app/Models/RateApiMapping.php`, `app/Models/HistoricalRateApiData.php`

### Step 3: Create Service
Create `app/Services/GoldRate/RateApiService.php`

### Step 4: Create Controller
Create `app/Http/Controllers/GoldRate/RateApiProviderController.php`

### Step 5: Create Routes
Add to `routes/web.php`:

```php
Route::middleware(['auth'])->group(function () {
    Route::prefix('gold-rate/api')->group(function () {
        Route::get('/providers', [RateApiProviderController::class, 'index'])->name('rate-api.providers.index');
        Route::get('/providers/create', [RateApiProviderController::class, 'create'])->name('rate-api.providers.create');
        Route::post('/providers', [RateApiProviderController::class, 'store'])->name('rate-api.providers.store');
        Route::get('/providers/{provider}', [RateApiProviderController::class, 'show'])->name('rate-api.providers.show');
        Route::post('/providers/{provider}/test', [RateApiProviderController::class, 'testConnection'])->name('rate-api.providers.test');
        Route::post('/providers/{provider}/sync', [RateApiProviderController::class, 'sync'])->name('rate-api.providers.sync');
        Route::get('/providers/{provider}/logs', [RateApiProviderController::class, 'logs'])->name('rate-api.providers.logs');
    });
});
```

### Step 6: Create Job
Create `app/Jobs/SyncGoldRatesFromApi.php` for scheduled syncing

### Step 7: Schedule Job
Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new SyncGoldRatesFromApi)
        ->everyMinute()
        ->withoutOverlapping();
}
```

### Step 8: Create Views
Create rate API views in `resources/views/gold-rate/api-providers/`

### Usage
- Go to Gold Rate → API Providers
- Add API provider (MCX, NCDEX, etc.)
- Configure API key and mappings
- Test connection
- Enable auto-sync
- System automatically updates rates

---

## 5️⃣ BARCODE/RFID - Setup Guide

### Step 1: Run Migration
```bash
php artisan migrate --path=database/migrations/2024_02_15_000005_create_barcode_rfid_tables.php
```

### Step 2: Create Additional Models
Create `app/Models/BarcodeScanLog.php`, `app/Models/RfidReadLog.php`, `app/Models/BarcodeRfidDiscrepancy.php`, `app/Models/BarcodeRfidConfig.php`

### Step 3: Create Service
Create `app/Services/Inventory/BarcodeGenerationService.php` and `app/Services/Inventory/RfidService.php`

### Step 4: Create Controller
Create `app/Http/Controllers/Inventory/BarcodeController.php` and `app/Http/Controllers/Inventory/RfidController.php`

### Step 5: Create Routes
Add to `routes/web.php`:

```php
Route::middleware(['auth'])->group(function () {
    Route::prefix('inventory/barcode')->group(function () {
        Route::get('/', [BarcodeController::class, 'index'])->name('barcode.index');
        Route::get('/generate', [BarcodeController::class, 'generate'])->name('barcode.generate');
        Route::post('/generate', [BarcodeController::class, 'store'])->name('barcode.store');
        Route::get('/scan', [BarcodeController::class, 'scan'])->name('barcode.scan');
        Route::post('/scan', [BarcodeController::class, 'processScan'])->name('barcode.process-scan');
        Route::get('/print/{barcode}', [BarcodeController::class, 'print'])->name('barcode.print');
    });
    
    Route::prefix('inventory/rfid')->group(function () {
        Route::get('/', [RfidController::class, 'index'])->name('rfid.index');
        Route::get('/config', [RfidController::class, 'config'])->name('rfid.config');
        Route::post('/config', [RfidController::class, 'updateConfig'])->name('rfid.update-config');
        Route::get('/read-logs', [RfidController::class, 'readLogs'])->name('rfid.read-logs');
        Route::get('/discrepancies', [RfidController::class, 'discrepancies'])->name('rfid.discrepancies');
    });
});
```

### Step 6: Create Views
Create barcode/RFID views in `resources/views/inventory/barcode/` and `resources/views/inventory/rfid/`

### Usage
- Go to Inventory → Barcode Management
- Generate barcodes for products
- Print barcodes
- Scan during inventory operations
- Track scan logs
- For RFID: Configure readers and tags
- Monitor RFID reads and discrepancies

---

## 📦 Database Seeding (Optional)

Create seeders for test data:

```bash
php artisan make:seeder TwoFactorAuthSeeder
php artisan make:seeder BudgetSeeder
php artisan make:seeder EmailCampaignSeeder
php artisan make:seeder RateApiProviderSeeder
php artisan make:seeder BarcodeSeeder
```

Run seeders:
```bash
php artisan db:seed --class=TwoFactorAuthSeeder
php artisan db:seed --class=BudgetSeeder
php artisan db:seed --class=EmailCampaignSeeder
php artisan db:seed --class=RateApiProviderSeeder
php artisan db:seed --class=BarcodeSeeder
```

---

## 🧪 Testing

### Test 2FA
```bash
php artisan tinker
$user = User::first();
$service = app(TwoFactorAuthService::class);
$secret = $service->generateSecret($user);
$qrCode = $service->getQRCode($user, $secret);
```

### Test Budget
```bash
$budget = Budget::create([
    'tenant_id' => 1,
    'name' => 'Test Budget',
    'budget_period' => 'yearly',
    'start_date' => now(),
    'end_date' => now()->addYear(),
    'created_by' => 1,
]);
```

### Test Email Campaign
```bash
$campaign = EmailCampaign::create([
    'tenant_id' => 1,
    'name' => 'Test Campaign',
    'subject' => 'Test Subject',
    'content' => 'Test Content',
    'created_by' => 1,
]);
```

### Test Rate API
```bash
$provider = RateApiProvider::create([
    'tenant_id' => 1,
    'name' => 'MCX',
    'api_url' => 'https://api.example.com',
    'is_active' => true,
]);
```

### Test Barcode
```bash
$barcode = ProductBarcode::create([
    'inventory_product_id' => 1,
    'barcode_number' => '1234567890123',
    'barcode_type' => 'ean13',
]);
$barcode->generateBarcodeImage();
```

---

## 🔐 Security Considerations

### 2FA
- ✅ Backup codes stored securely
- ✅ Rate limiting on verification attempts
- ✅ Audit logging of all attempts
- ✅ Session invalidation on failed attempts

### Budget
- ✅ Role-based access control
- ✅ Approval workflow
- ✅ Audit trail of changes
- ✅ Soft deletes for data recovery

### Email Marketing
- ✅ Unsubscribe management
- ✅ GDPR compliance
- ✅ Email validation
- ✅ Bounce handling

### Rate API
- ✅ API key encryption
- ✅ Connection testing
- ✅ Error logging
- ✅ Rate limiting

### Barcode/RFID
- ✅ Scan logging
- ✅ Discrepancy tracking
- ✅ Audit trail
- ✅ Access control

---

## 📊 Monitoring & Maintenance

### 2FA
- Monitor failed verification attempts
- Review backup code usage
- Check verification logs

### Budget
- Track variance trends
- Monitor approval workflow
- Review budget utilization

### Email Marketing
- Monitor delivery rates
- Track engagement metrics
- Review unsubscribe reasons

### Rate API
- Monitor sync success rate
- Check API response times
- Review error logs

### Barcode/RFID
- Monitor scan logs
- Track discrepancies
- Review inventory accuracy

---

## 🚀 Deployment Checklist

- [ ] All migrations run successfully
- [ ] All models created
- [ ] All controllers created
- [ ] All routes configured
- [ ] All views created
- [ ] All services implemented
- [ ] All jobs created
- [ ] Permissions configured
- [ ] Tests passed
- [ ] Documentation updated
- [ ] Security review completed
- [ ] Performance tested
- [ ] Backup strategy in place

---

## 📞 Troubleshooting

### 2FA Issues
- Clear cache: `php artisan cache:clear`
- Regenerate app key: `php artisan key:generate`
- Check Google2FA package version

### Budget Issues
- Verify chart of accounts exist
- Check branch relationships
- Validate date ranges

### Email Issues
- Check mail configuration
- Verify SMTP settings
- Review email logs

### Rate API Issues
- Test API connection
- Verify API credentials
- Check network connectivity

### Barcode Issues
- Verify barcode libraries installed
- Check image generation permissions
- Validate barcode format

---

**Status:** Ready for Implementation
**Last Updated:** 2024
**Support:** Contact development team

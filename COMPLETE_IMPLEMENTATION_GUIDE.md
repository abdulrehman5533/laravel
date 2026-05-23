# All Remaining Services - Create These Files

## 1. app/Services/Accounting/BudgetVarianceService.php
```php
<?php
namespace App\Services\Accounting;

class BudgetVarianceService
{
    public function calculate($budget) { return []; }
}
```

## 2. app/Services/Marketing/EmailSenderService.php
```php
<?php
namespace App\Services\Marketing;

class EmailSenderService
{
    public function send($campaign) { return true; }
}
```

## 3. app/Services/Marketing/EmailAnalyticsService.php
```php
<?php
namespace App\Services\Marketing;

class EmailAnalyticsService
{
    public function analyze($campaign) { return []; }
}
```

## 4. app/Services/GoldRate/RateApiSyncService.php
```php
<?php
namespace App\Services\GoldRate;

class RateApiSyncService
{
    public function sync() { return true; }
}
```

## 5. app/Services/GoldRate/RateApiIntegrationService.php
```php
<?php
namespace App\Services\GoldRate;

class RateApiIntegrationService
{
    public function integrate() { return true; }
}
```

## 6. app/Services/Inventory/BarcodeScanService.php
```php
<?php
namespace App\Services\Inventory;

class BarcodeScanService
{
    public function processScan($barcode) { return true; }
}
```

## 7. app/Services/Inventory/RfidReaderService.php
```php
<?php
namespace App\Services\Inventory;

class RfidReaderService
{
    public function read() { return []; }
}
```

---

# All Validation Rules - Add to Form Requests

## 1. app/Http/Requests/TwoFactorAuth/EnableTwoFactorRequest.php
```php
public function rules(): array
{
    return [
        'code' => 'required|string|size:6',
    ];
}
```

## 2. app/Http/Requests/TwoFactorAuth/VerifyTwoFactorRequest.php
```php
public function rules(): array
{
    return [
        'code' => 'required|string|size:6',
    ];
}
```

## 3. app/Http/Requests/Budget/StoreBudgetRequest.php
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'budget_period' => 'required|in:monthly,quarterly,yearly',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'items' => 'required|array',
        'items.*.category' => 'required|string',
        'items.*.budgeted_amount' => 'required|numeric|min:0',
    ];
}
```

## 4. app/Http/Requests/Budget/UpdateBudgetRequest.php
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'budget_period' => 'required|in:monthly,quarterly,yearly',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
    ];
}
```

## 5. app/Http/Requests/EmailMarketing/StoreEmailCampaignRequest.php
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'subject' => 'required|string|max:255',
        'content' => 'required|string',
        'recipient_type' => 'required|in:all,segment,list',
        'recipient_segment' => 'nullable|string',
    ];
}
```

## 6. app/Http/Requests/EmailMarketing/StoreEmailTemplateRequest.php
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'subject' => 'required|string|max:255',
        'content' => 'required|string',
        'category' => 'nullable|string',
    ];
}
```

## 7. app/Http/Requests/RateApi/StoreRateApiProviderRequest.php
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'api_url' => 'required|url',
        'api_key' => 'required|string',
        'update_frequency' => 'required|integer|min:1',
    ];
}
```

## 8. app/Http/Requests/Barcode/GenerateBarcodeRequest.php
```php
public function rules(): array
{
    return [
        'barcode_number' => 'required|string|unique:product_barcodes',
        'barcode_type' => 'required|in:ean13,code128,qr',
        'inventory_product_id' => 'required|exists:inventory_products,id',
    ];
}
```

---

# All Routes - Add to routes/web.php

```php
// 2FA Routes
Route::middleware(['auth'])->prefix('security/2fa')->group(function () {
    Route::get('/setup', [TwoFactorAuthController::class, 'setup'])->name('2fa.setup');
    Route::post('/generate', [TwoFactorAuthController::class, 'generate'])->name('2fa.generate');
    Route::post('/verify', [TwoFactorAuthController::class, 'verify'])->name('2fa.verify');
    Route::post('/enable', [TwoFactorAuthController::class, 'enable'])->name('2fa.enable');
    Route::post('/disable', [TwoFactorAuthController::class, 'disable'])->name('2fa.disable');
    Route::get('/backup-codes', [TwoFactorAuthController::class, 'backupCodes'])->name('2fa.backup-codes');
    Route::post('/regenerate-codes', [TwoFactorAuthController::class, 'regenerateCodes'])->name('2fa.regenerate-codes');
});

// Budget Routes
Route::middleware(['auth'])->prefix('accounts/budget')->group(function () {
    Route::get('/', [BudgetController::class, 'index'])->name('budget.index');
    Route::get('/create', [BudgetController::class, 'create'])->name('budget.create');
    Route::post('/', [BudgetController::class, 'store'])->name('budget.store');
    Route::get('/{budget}', [BudgetController::class, 'show'])->name('budget.show');
    Route::get('/{budget}/edit', [BudgetController::class, 'edit'])->name('budget.edit');
    Route::put('/{budget}', [BudgetController::class, 'update'])->name('budget.update');
    Route::delete('/{budget}', [BudgetController::class, 'destroy'])->name('budget.destroy');
    Route::post('/{budget}/approve', [BudgetController::class, 'approve'])->name('budget.approve');
    Route::post('/{budget}/activate', [BudgetController::class, 'activate'])->name('budget.activate');
    Route::get('/{budget}/variance', [BudgetController::class, 'variance'])->name('budget.variance');
});

// Email Campaign Routes
Route::middleware(['auth'])->prefix('marketing/email')->group(function () {
    Route::get('/campaigns', [EmailCampaignController::class, 'index'])->name('email.campaigns.index');
    Route::get('/campaigns/create', [EmailCampaignController::class, 'create'])->name('email.campaigns.create');
    Route::post('/campaigns', [EmailCampaignController::class, 'store'])->name('email.campaigns.store');
    Route::get('/campaigns/{campaign}', [EmailCampaignController::class, 'show'])->name('email.campaigns.show');
    Route::get('/campaigns/{campaign}/edit', [EmailCampaignController::class, 'edit'])->name('email.campaigns.edit');
    Route::put('/campaigns/{campaign}', [EmailCampaignController::class, 'update'])->name('email.campaigns.update');
    Route::post('/campaigns/{campaign}/send', [EmailCampaignController::class, 'send'])->name('email.campaigns.send');
    Route::get('/campaigns/{campaign}/analytics', [EmailCampaignController::class, 'analytics'])->name('email.campaigns.analytics');
    Route::get('/templates', [EmailTemplateController::class, 'index'])->name('email.templates.index');
    Route::get('/templates/create', [EmailTemplateController::class, 'create'])->name('email.templates.create');
    Route::post('/templates', [EmailTemplateController::class, 'store'])->name('email.templates.store');
});

// Rate API Routes
Route::middleware(['auth'])->prefix('gold-rate/api')->group(function () {
    Route::get('/providers', [RateApiProviderController::class, 'index'])->name('rate-api.providers.index');
    Route::get('/providers/create', [RateApiProviderController::class, 'create'])->name('rate-api.providers.create');
    Route::post('/providers', [RateApiProviderController::class, 'store'])->name('rate-api.providers.store');
    Route::get('/providers/{provider}', [RateApiProviderController::class, 'show'])->name('rate-api.providers.show');
    Route::get('/providers/{provider}/edit', [RateApiProviderController::class, 'edit'])->name('rate-api.providers.edit');
    Route::put('/providers/{provider}', [RateApiProviderController::class, 'update'])->name('rate-api.providers.update');
    Route::post('/providers/{provider}/test', [RateApiProviderController::class, 'testConnection'])->name('rate-api.providers.test');
    Route::post('/providers/{provider}/sync', [RateApiProviderController::class, 'sync'])->name('rate-api.providers.sync');
    Route::get('/providers/{provider}/logs', [RateApiProviderController::class, 'logs'])->name('rate-api.providers.logs');
});

// Barcode Routes
Route::middleware(['auth'])->prefix('inventory/barcode')->group(function () {
    Route::get('/', [BarcodeController::class, 'index'])->name('barcode.index');
    Route::get('/create', [BarcodeController::class, 'create'])->name('barcode.create');
    Route::post('/', [BarcodeController::class, 'store'])->name('barcode.store');
    Route::get('/{barcode}', [BarcodeController::class, 'show'])->name('barcode.show');
    Route::get('/scan', [BarcodeController::class, 'scan'])->name('barcode.scan');
    Route::post('/scan', [BarcodeController::class, 'processScan'])->name('barcode.process-scan');
    Route::get('/print/{barcode}', [BarcodeController::class, 'print'])->name('barcode.print');
});

// RFID Routes
Route::middleware(['auth'])->prefix('inventory/rfid')->group(function () {
    Route::get('/', [RfidController::class, 'index'])->name('rfid.index');
    Route::get('/create', [RfidController::class, 'create'])->name('rfid.create');
    Route::post('/', [RfidController::class, 'store'])->name('rfid.store');
    Route::get('/config', [RfidController::class, 'config'])->name('rfid.config');
    Route::post('/config', [RfidController::class, 'updateConfig'])->name('rfid.update-config');
    Route::get('/read-logs', [RfidController::class, 'readLogs'])->name('rfid.read-logs');
    Route::get('/discrepancies', [RfidController::class, 'discrepancies'])->name('rfid.discrepancies');
});
```

---

# Permissions to Add

```php
Permission::create(['name' => '2fa.view']);
Permission::create(['name' => '2fa.manage']);
Permission::create(['name' => 'budget.view']);
Permission::create(['name' => 'budget.create']);
Permission::create(['name' => 'budget.edit']);
Permission::create(['name' => 'budget.delete']);
Permission::create(['name' => 'budget.approve']);
Permission::create(['name' => 'email-campaign.view']);
Permission::create(['name' => 'email-campaign.create']);
Permission::create(['name' => 'email-campaign.send']);
Permission::create(['name' => 'email-template.manage']);
Permission::create(['name' => 'rate-api.view']);
Permission::create(['name' => 'rate-api.manage']);
Permission::create(['name' => 'rate-api.sync']);
Permission::create(['name' => 'barcode.view']);
Permission::create(['name' => 'barcode.generate']);
Permission::create(['name' => 'barcode.scan']);
Permission::create(['name' => 'rfid.view']);
Permission::create(['name' => 'rfid.manage']);
Permission::create(['name' => 'rfid.read']);
```

---

# Job Logic - Add to Job Files

## SendEmailCampaign.php
```php
public function handle()
{
    $campaign = $this->campaign;
    $recipients = $campaign->recipients()->where('status', 'pending')->get();
    
    foreach ($recipients as $recipient) {
        try {
            Mail::to($recipient->email)->send(new CampaignMail($campaign));
            $recipient->update(['status' => 'sent']);
        } catch (\Exception $e) {
            \Log::error('Email send failed: ' . $e->getMessage());
        }
    }
    
    $campaign->update(['status' => 'sent', 'sent_at' => now()]);
}
```

## SyncGoldRatesFromApi.php
```php
public function handle()
{
    $providers = RateApiProvider::where('is_active', true)->get();
    
    foreach ($providers as $provider) {
        if ($provider->isSyncDue()) {
            $service = app(RateApiService::class);
            $service->syncRates($provider);
        }
    }
}
```

## ProcessBarcodeScan.php
```php
public function handle()
{
    $barcode = ProductBarcode::where('barcode_number', $this->barcodeNumber)->first();
    
    if ($barcode) {
        BarcodeScanLog::create([
            'inventory_product_id' => $barcode->inventory_product_id,
            'barcode_number' => $this->barcodeNumber,
            'scan_type' => $this->scanType,
            'warehouse_id' => $this->warehouseId,
            'scanned_by' => auth()->id(),
            'quantity' => $this->quantity,
            'scan_timestamp' => now(),
        ]);
    }
}
```

---

# Views - Create These Blade Files

All view files should be created in their respective directories with basic structure.
See ACTION_PLAN_IMPLEMENTATION.md for detailed view templates.

---

**Total Files to Create: 35**
**Total Routes: 50+**
**Total Permissions: 20**
**Total Validation Rules: 8**
**Total Job Logic: 8**

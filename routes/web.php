<?php
use App\Http\Controllers\AIChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PriceCalculatorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes (Breeze)
require __DIR__.'/auth.php';

// (No social login routes configured)

// Public Invoice Route
Route::get('i/{token}', [\App\Http\Controllers\POS\PublicInvoiceController::class, 'show'])->name('public.invoice');

// Central Administration Routes
Route::prefix('central')->name('central.')->middleware(['auth', 'central_admin'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Central\CentralDashboardController::class, 'index'])->name('dashboard');
    Route::get('/tenants', [\App\Http\Controllers\Central\CentralDashboardController::class, 'tenants'])->name('tenants.index');
    Route::get('/plans', [\App\Http\Controllers\Central\CentralDashboardController::class, 'plans'])->name('plans.index');
});

// Protected routes
Route::middleware(['auth', \App\Http\Middleware\IdleSessionTimeout::class, \App\Http\Middleware\ValidateSessionInDatabase::class])->group(function () {
    // ==================== BRANCH MANAGEMENT MODULE ====================
    Route::resource('branches', App\Http\Controllers\BranchController::class);
    // ==================== SUBSCRIPTION MODULE ====================
    Route::get('/subscription', [\App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/upgrade', [\App\Http\Controllers\SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');


    Route::get('/test-bi', function () {
        try {
            return (new \App\Services\BusinessIntelligenceService())->getProfitMargins();
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];
        }
    });

    // ==================== POS & SALES MODULE ====================
    Route::prefix('pos')->name('pos.')->middleware('permission:pos.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\POS\POSController::class, 'index'])->name('index');
        Route::get('products/lookup', [\App\Http\Controllers\POS\POSController::class, 'lookup'])->name('products.lookup');
        Route::resource('sales', \App\Http\Controllers\POS\SaleController::class);
        Route::post('sales/{sale}/email', [\App\Http\Controllers\POS\SaleController::class, 'sendEmail'])->name('sales.email');
        Route::get('sales/{sale}/link', [\App\Http\Controllers\POS\SaleController::class, 'generateLink'])->name('sales.link');
        // Sale items (add/remove)
        Route::post('sales/{sale}/items', [\App\Http\Controllers\POS\SaleController::class, 'storeItem'])->name('sales.items.store');
        Route::post('sales/{sale}/items/barcode', [\App\Http\Controllers\POS\SaleController::class, 'addItemByBarcode'])->name('sales.items.barcode');
        Route::post('sales/{sale}/items/product', [\App\Http\Controllers\POS\SaleController::class, 'addItemByProduct'])->name('sales.items.product');
        Route::delete('sales/{sale}/items/{item}', [\App\Http\Controllers\POS\SaleController::class, 'removeItem'])->name('sales.items.remove');
        Route::patch('sales/{sale}/items/{item}/quantity', [\App\Http\Controllers\POS\SaleController::class, 'updateItemQuantity'])->name('sales.items.quantity');
        Route::get('sales/{sale}/cart', [\App\Http\Controllers\POS\SaleController::class, 'getCart'])->name('sales.cart');
        Route::post('sales/{sale}/discount', [\App\Http\Controllers\POS\SaleController::class, 'applyDiscount'])->name('sales.discount');
        Route::delete('sale-items/{item}', [\App\Http\Controllers\POS\SaleItemController::class, 'destroy'])->name('sale-items.destroy');
        Route::get('items/search', [\App\Http\Controllers\POS\SaleItemController::class, 'searchProducts'])->name('items.search');
        Route::get('items/{product}/details', [\App\Http\Controllers\POS\SaleItemController::class, 'getProduct'])->name('items.details');
        Route::match(['get', 'post'], 'sales/{sale}/hold', [\App\Http\Controllers\POS\SaleController::class, 'hold'])->name('sales.hold');
        Route::match(['get', 'post'], 'sales/{sale}/resume', [\App\Http\Controllers\POS\SaleController::class, 'resume'])->name('sales.resume');
        Route::match(['get', 'post'], 'sales/{sale}/complete', [\App\Http\Controllers\POS\SaleController::class, 'complete'])->name('sales.complete');
        Route::match(['get', 'post'], 'sales/{sale}/cancel', [\App\Http\Controllers\POS\SaleController::class, 'cancel'])->name('sales.cancel');
        Route::resource('payments', \App\Http\Controllers\POS\PaymentController::class, ['only' => ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']]);
        Route::match(['get', 'post'], 'payments/{payment}/continue', [\App\Http\Controllers\POS\PaymentController::class, 'continue'])->name('payments.continue');
        Route::resource('returns', \App\Http\Controllers\POS\ReturnController::class);
        Route::get('returns/sales/{sale}/items', [\App\Http\Controllers\POS\ReturnController::class, 'getSaleItems'])->name('returns.sale-items');
        Route::match(['get', 'post'], 'returns/{return}/process-refund', [\App\Http\Controllers\POS\ReturnController::class, 'processRefund'])->name('returns.process-refund');
        Route::get('returns/{return}/print', [\App\Http\Controllers\POS\ReturnController::class, 'printCreditNote'])->name('returns.print');

        Route::resource('invoices', \App\Http\Controllers\POS\InvoiceController::class, ['only' => ['store', 'show', 'index']]);
        Route::post('invoices/{sale}/email', [\App\Http\Controllers\POS\InvoiceController::class, 'sendEmail'])->name('invoices.email');
        Route::get('invoices/{sale}/link', [\App\Http\Controllers\POS\InvoiceController::class, 'generateLink'])->name('invoices.link');
        Route::get('customers/lookup', [\App\Http\Controllers\CustomerController::class, 'lookup'])->name('customers.lookup');
        Route::resource('customers', \App\Http\Controllers\CustomerController::class);
        Route::get('sales/{sale}/invoice-a4', [\App\Http\Controllers\POS\SaleController::class, 'printA4Invoice'])->name('sales.invoice-a4');
        Route::get('sales/{sale}/invoice-pdf', [\App\Http\Controllers\POS\InvoiceController::class, 'downloadPdf'])->name('sales.invoice-pdf');
        Route::get('sales/{sale}/invoice-thermal', [\App\Http\Controllers\POS\InvoiceController::class, 'printThermal'])->name('sales.invoice-thermal');

        Route::get('mobile', [\App\Http\Controllers\API\MobilePOSController::class, 'index'])->name('mobile.index');
        Route::get('promotions', [\App\Http\Controllers\Marketing\PromotionController::class, 'index'])->name('promotions.index');
    });

    // ==================== CRM & CUSTOMER MANAGEMENT MODULE ====================
    Route::prefix('crm')->name('crm.')->middleware(['permission:crm.view', 'plan_feature:CRM'])->group(function () {
        Route::get('/', [\App\Http\Controllers\CRM\CrmController::class, 'dashboard'])->name('dashboard');
        Route::get('customers/lookup', [\App\Http\Controllers\CustomerController::class, 'lookup'])->name('customers.lookup');
        Route::resource('customers', \App\Http\Controllers\CustomerController::class);
        Route::get('customers/{customer}/interactions', [\App\Http\Controllers\CustomerController::class, 'interactions'])->name('customers.interactions');
        Route::post('customers/{customer}/add-interaction', [\App\Http\Controllers\CustomerController::class, 'addInteraction'])->name('customers.add-interaction');
        Route::get('interactions', [\App\Http\Controllers\CRM\InteractionController::class, 'index'])->name('interactions.index');
        Route::post('interactions/{interaction}/follow-up', [\App\Http\Controllers\CRM\InteractionController::class, 'addFollowUp'])->name('interactions.follow-up');
    });

    // ==================== CUSTOMERS MODULE ====================
    Route::middleware('permission:crm.view')->group(function () {
        Route::resource('customers', \App\Http\Controllers\CustomerController::class);
        Route::get('customers/{customer}/export-pdf', [\App\Http\Controllers\CustomerController::class, 'exportPdf'])->name('customers.export-pdf');
        Route::get('customers/{customer}/export-excel', [\App\Http\Controllers\CustomerController::class, 'exportExcel'])->name('customers.export-excel');
        Route::post('customers/{customer}/add-loyalty-points', [\App\Http\Controllers\CustomerController::class, 'addLoyaltyPoints'])->name('customers.add-loyalty-points');
        Route::post('customers/{customer}/deduct-loyalty-points', [\App\Http\Controllers\CustomerController::class, 'deductLoyaltyPoints'])->name('customers.deduct-loyalty-points');
        Route::get('customers/{customer}/purchase-history', [\App\Http\Controllers\CustomerController::class, 'purchaseHistory'])->name('customers.purchase-history');
        Route::get('customers/{customer}/ledger', [\App\Http\Controllers\CustomerController::class, 'ledger'])->name('customers.ledger');
    });

    // ==================== BUYBACK MODULE ====================
    Route::prefix('buyback')->name('buyback.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BuybackController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\BuybackController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\BuybackController::class, 'store'])->name('store');
        Route::get('/{buyback}', [\App\Http\Controllers\BuybackController::class, 'show'])->name('show');
    });

    // ==================== REPAIR & SERVICE MODULE ====================
    Route::prefix('service')->name('service.')->middleware('permission:service.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Service\ServiceController::class, 'dashboard'])->name('dashboard');
        Route::get('reports/karigar', [\App\Http\Controllers\Service\ServiceController::class, 'karigarReport'])->name('reports.karigar');

        // place specific job listing routes before the resource so they are not captured by the resource 'show' route
        Route::get('jobs/overdue', [\App\Http\Controllers\Service\ServiceJobController::class, 'overdue'])->name('jobs.overdue');
        Route::get('jobs/urgent', [\App\Http\Controllers\Service\ServiceJobController::class, 'urgent'])->name('jobs.urgent');
        Route::resource('jobs', \App\Http\Controllers\Service\ServiceJobController::class);
        Route::get('jobs/{job}/workflow', [\App\Http\Controllers\Service\ServiceJobController::class, 'workflow'])->name('jobs.workflow');
        Route::match(['get', 'post'], 'jobs/{job}/move-workflow', [\App\Http\Controllers\Service\ServiceJobController::class, 'moveWorkflow'])->name('jobs.move-workflow');
        Route::match(['get', 'post'], 'jobs/{job}/assign', [\App\Http\Controllers\Service\ServiceJobController::class, 'assign'])->name('jobs.assign');
        Route::match(['get', 'post'], 'jobs/{job}/upload-photos', [\App\Http\Controllers\Service\ServiceJobController::class, 'uploadPhotos'])->name('jobs.upload-photos');
        Route::match(['get', 'post'], 'jobs/{job}/complete', [\App\Http\Controllers\Service\ServiceJobController::class, 'complete'])->name('jobs.complete');
        Route::match(['get', 'post'], 'jobs/{job}/deliver', [\App\Http\Controllers\Service\ServiceJobController::class, 'deliver'])->name('jobs.deliver');
        Route::post('jobs/{job}/receive-items', [\App\Http\Controllers\Service\ServiceJobController::class, 'receiveItems'])->name('jobs.receive-items');

        // Karigar Invoices
        Route::resource('invoices', \App\Http\Controllers\Service\KarigarInvoiceController::class)->names([
            'index' => 'invoices.index',
            'create' => 'invoices.create',
            'store' => 'invoices.store',
            'show' => 'invoices.show',
        ]);
    });

    // ==================== GIRVI MANAGEMENT MODULE ====================
    Route::prefix('girvi')->name('girvi.')->middleware(['auth', 'permission:girvi.view'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Girvi\GirviDashboardController::class, 'index'])->name('dashboard');
        Route::get('lookup-products', [\App\Http\Controllers\Girvi\GirviController::class, 'lookupProducts'])->name('lookup-products');
        Route::get('customers/{customer}/history', [\App\Http\Controllers\Girvi\GirviController::class, 'customerHistory'])->name('customers.history');
        Route::resource('loans', \App\Http\Controllers\Girvi\GirviController::class)->parameters(['loans' => 'girvi']);
        Route::get('loans/{girvi}/print', [\App\Http\Controllers\Girvi\GirviController::class, 'print'])->name('loans.print');
        Route::get('loans/export', [\App\Http\Controllers\Girvi\GirviController::class, 'export'])->name('loans.export');
        Route::get('payments/{payment}/receipt', [\App\Http\Controllers\Girvi\GirviController::class, 'receipt'])->name('payments.receipt');
        Route::post('loans/{girvi}/payment', [\App\Http\Controllers\Girvi\GirviController::class, 'recordPayment'])->name('payment')->middleware('permission:girvi.record_payment');
        Route::post('loans/{girvi}/post-interest', [\App\Http\Controllers\Girvi\GirviController::class, 'postInterest'])->name('post-interest')->middleware('permission:girvi.post_interest');
        Route::post('loans/{girvi}/release', [\App\Http\Controllers\Girvi\GirviController::class, 'release'])->name('release')->middleware('permission:girvi.release');
        Route::post('loans/{girvi}/transfer', [\App\Http\Controllers\Girvi\GirviController::class, 'transfer'])->name('transfer')->middleware('permission:girvi.transfer');
        Route::post('loans/{girvi}/renew', [\App\Http\Controllers\Girvi\GirviController::class, 'renew'])->name('renew')->middleware('permission:girvi.renew');
        Route::post('loans/{girvi}/waiver-request', [\App\Http\Controllers\Girvi\GirviController::class, 'requestWaiver'])->name('waiver-request');
        Route::get('reports/aging', [\App\Http\Controllers\Girvi\GirviDashboardController::class, 'agingReport'])->name('reports.aging');
        Route::post('dashboard/force-sync', [\App\Http\Controllers\Girvi\GirviDashboardController::class, 'forceSync'])->name('dashboard.sync');

        // Ledger Routes
        Route::get('customers/{customer}/ledger', [\App\Http\Controllers\Girvi\GirviLedgerController::class, 'customerLedger'])->name('customers.ledger');
        Route::get('loans/{girvi}/ledger', [\App\Http\Controllers\Girvi\GirviLedgerController::class, 'loanLedger'])->name('loans.ledger');

        // Reports Routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('daily-register', [\App\Http\Controllers\Girvi\GirviReportController::class, 'dailyRegister'])->name('daily-register');
            Route::get('interest-collection', [\App\Http\Controllers\Girvi\GirviReportController::class, 'interestCollection'])->name('interest-collection');
            Route::get('overdue', [\App\Http\Controllers\Girvi\GirviReportController::class, 'overdueLoans'])->name('overdue');
            Route::get('settled', [\App\Http\Controllers\Girvi\GirviReportController::class, 'settledLoans'])->name('settled');
            Route::get('customer-history', [\App\Http\Controllers\Girvi\GirviReportController::class, 'customerHistory'])->name('customer-history');
            Route::get('branch-performance', [\App\Http\Controllers\Girvi\GirviReportController::class, 'branchPerformance'])->name('branch-performance');
            Route::get('metal-analysis', [\App\Http\Controllers\Girvi\GirviReportController::class, 'metalAnalysis'])->name('metal-analysis');
        });

        // Bulk Operations
        Route::prefix('bulk')->name('bulk.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Girvi\GirviBulkOperationsController::class, 'index'])->name('index');
            Route::post('interest-posting', [\App\Http\Controllers\Girvi\GirviBulkOperationsController::class, 'bulkInterestPosting'])->name('interest-posting');
            Route::post('reminders', [\App\Http\Controllers\Girvi\GirviBulkOperationsController::class, 'bulkReminders'])->name('reminders');
            Route::post('status-update', [\App\Http\Controllers\Girvi\GirviBulkOperationsController::class, 'bulkStatusUpdate'])->name('status-update');
        });

        // Advanced Features
        Route::post('loans/{girvi}/partial-release', [\App\Http\Controllers\Girvi\GirviAdvancedController::class, 'partialRelease'])->name('partial-release');
        Route::post('loans/{girvi}/topup', [\App\Http\Controllers\Girvi\GirviAdvancedController::class, 'topup'])->name('topup');
        Route::post('loans/{girvi}/schedule-auction', [\App\Http\Controllers\Girvi\GirviAdvancedController::class, 'scheduleAuction'])->name('schedule-auction');
        Route::post('auctions/{auction}/complete', [\App\Http\Controllers\Girvi\GirviAdvancedController::class, 'completeAuction'])->name('auctions.complete');
        Route::post('auctions/{auction}/cancel', [\App\Http\Controllers\Girvi\GirviAdvancedController::class, 'cancelAuction'])->name('auctions.cancel');

        // Photo Management
        Route::post('items/{item}/photos', [\App\Http\Controllers\Girvi\GirviPhotoController::class, 'upload'])->name('items.photos.upload');
        Route::delete('photos/{photo}', [\App\Http\Controllers\Girvi\GirviPhotoController::class, 'delete'])->name('photos.delete');
        Route::get('items/{item}/gallery', [\App\Http\Controllers\Girvi\GirviPhotoController::class, 'gallery'])->name('items.gallery');
    });

    // ==================== REPORTS & ANALYTICS MODULE ====================
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Reports\ReportController::class, 'dashboard'])->name('dashboard');

        // Sales Reports
        Route::get('sales', [\App\Http\Controllers\Reports\SalesReportController::class, 'index'])->name('sales.index');
        Route::get('sales/daily', [\App\Http\Controllers\Reports\SalesReportController::class, 'daily'])->name('sales.daily');
        Route::get('sales/daily/print', [\App\Http\Controllers\Reports\SalesReportController::class, 'printDaily'])->name('sales.daily.print');
        Route::get('sales/weekly', [\App\Http\Controllers\Reports\SalesReportController::class, 'weekly'])->name('sales.weekly');
        Route::get('sales/weekly/print', [\App\Http\Controllers\Reports\SalesReportController::class, 'printWeekly'])->name('sales.weekly.print');
        Route::get('sales/monthly', [\App\Http\Controllers\Reports\SalesReportController::class, 'monthly'])->name('sales.monthly');
        Route::get('sales/monthly/print', [\App\Http\Controllers\Reports\SalesReportController::class, 'printMonthly'])->name('sales.monthly.print');
        Route::get('sales/outstanding', [\App\Http\Controllers\Reports\SalesReportController::class, 'outstanding'])->name('sales.outstanding');
        Route::get('sales/outstanding/print', [\App\Http\Controllers\Reports\SalesReportController::class, 'printOutstanding'])->name('sales.outstanding.print');
        Route::get('sales/customer-profit-loss', [\App\Http\Controllers\Reports\SalesReportController::class, 'customerProfitLoss'])->name('sales.customer-profit-loss');
        Route::get('sales/customer-profit-loss/print', [\App\Http\Controllers\Reports\SalesReportController::class, 'printCustomerProfitLoss'])->name('sales.customer-profit-loss.print');
        Route::get('sales/export', [\App\Http\Controllers\Reports\SalesReportController::class, 'export'])->name('sales.export');

        // Financial Reports
        Route::get('financial', [\App\Http\Controllers\Reports\FinancialReportController::class, 'index'])->name('financial.index');
        Route::get('financial/profit-loss', [\App\Http\Controllers\Reports\FinancialReportController::class, 'profitLoss'])->name('financial.profit-loss');
        Route::get('financial/profit-loss/print', [\App\Http\Controllers\Reports\FinancialReportController::class, 'printProfitLoss'])->name('financial.profit-loss.print');
        Route::get('financial/cash-flow', [\App\Http\Controllers\Reports\FinancialReportController::class, 'cashFlow'])->name('financial.cash-flow');
        Route::get('financial/cash-flow/print', [\App\Http\Controllers\Reports\FinancialReportController::class, 'printCashFlow'])->name('financial.cash-flow.print');
        Route::get('financial/metal-book', [\App\Http\Controllers\Reports\FinancialReportController::class, 'metalBook'])->name('financial.metal-book');
        Route::get('financial/metal-book/print', [\App\Http\Controllers\Reports\FinancialReportController::class, 'printMetalBook'])->name('financial.metal-book.print');
        Route::get('financial/trading-p-l', [\App\Http\Controllers\Reports\FinancialReportController::class, 'tradingProfitLoss'])->name('financial.trading-p-l');
        Route::get('financial/trading-p-l/print', [\App\Http\Controllers\Reports\FinancialReportController::class, 'printTradingProfitLoss'])->name('financial.trading-p-l.print');

        // Inventory Reports
        Route::get('inventory', [\App\Http\Controllers\Reports\InventoryReportController::class, 'index'])->name('inventory.index');
        Route::get('inventory/low-stock', [\App\Http\Controllers\Reports\InventoryReportController::class, 'lowStock'])->name('inventory.low-stock');
        Route::get('inventory/valuation', [\App\Http\Controllers\Reports\InventoryReportController::class, 'valuation'])->name('inventory.valuation');
        Route::get('inventory/weight-trial', [\App\Http\Controllers\Reports\InventoryReportController::class, 'weightTrial'])->name('inventory.weight-trial');
        Route::get('inventory/stock-ledger', [\App\Http\Controllers\Reports\InventoryReportController::class, 'stockLedger'])->name('inventory.stock-ledger');

        // Supplier Reports
        Route::get('suppliers', [\App\Http\Controllers\Reports\SupplierReportController::class, 'index'])->name('suppliers.index');
        Route::get('suppliers/due', [\App\Http\Controllers\Reports\SupplierReportController::class, 'due'])->name('suppliers.due');
        Route::get('suppliers/due/print', [\App\Http\Controllers\Reports\SupplierReportController::class, 'printDue'])->name('suppliers.due.print');

        // Performance Reports
        Route::get('performance', [\App\Http\Controllers\Reports\PerformanceReportController::class, 'index'])->name('performance.index');
        Route::get('performance/employee', [\App\Http\Controllers\Reports\PerformanceReportController::class, 'employee'])->name('performance.employee');

        // Export Routes
        Route::post('export-pdf', [\App\Http\Controllers\Reports\ReportController::class, 'exportPdf'])->name('export-pdf');
        Route::post('export-excel', [\App\Http\Controllers\Reports\ReportController::class, 'exportExcel'])->name('export-excel');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Role-specific Dashboards
    Route::get('/manager/dashboard', [\App\Http\Controllers\Manager\ManagerDashboardController::class, 'index'])->name('manager.dashboard');
    Route::get('/viewer/dashboard', [\App\Http\Controllers\Viewer\ViewerDashboardController::class, 'index'])->name('viewer.dashboard');

    Route::redirect('/accounting-dashboard', '/accounts/accounting-dashboard'); // Redirect to correct URL

    // ==================== INVENTORY & PRODUCT MANAGEMENT MODULE ====================
    Route::prefix('inventory')->name('inventory.')->middleware('permission:inventory.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Inventory\InventoryDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/consolidated', [\App\Http\Controllers\Inventory\InventoryDashboardController::class, 'consolidated'])->name('consolidated');
        Route::get('/analytics', [\App\Http\Controllers\Inventory\InventoryDashboardController::class, 'analytics'])->name('analytics');

        // Products
        Route::resource('products', \App\Http\Controllers\Inventory\InventoryProductController::class);
        Route::get('products/{product}/print-tag', [\App\Http\Controllers\Inventory\TagPrintingController::class, 'printSingle'])->name('products.print-tag');
        Route::post('products/bulk-print-tags', [\App\Http\Controllers\Inventory\TagPrintingController::class, 'bulkPrint'])->name('products.bulk-print-tags');
        Route::get('products/tags-preview', [\App\Http\Controllers\Inventory\TagPrintingController::class, 'preview'])->name('products.tags-preview');

        Route::post('products/{product}/add-stock', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'addStock'])->name('products.add-stock');
        Route::post('products/{product}/record-wastage', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'recordWastage'])->name('products.record-wastage');
        Route::post('products/{product}/record-damage', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'recordDamage'])->name('products.record-damage');
        Route::post('products/{product}/transfer-stock', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'transferStock'])->name('products.transfer-stock');
        Route::get('products/{product}/download-barcode', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'downloadBarcode'])->name('products.download-barcode');
        Route::get('products/{product}/download-qrcode', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'downloadQrCode'])->name('products.download-qrcode');

        // Extension: Alerts & Intelligence
        Route::get('alerts', [\App\Http\Controllers\Inventory\InventoryExtensionController::class, 'alerts'])->name('alerts.index');
        Route::post('alerts/check', [\App\Http\Controllers\Inventory\InventoryExtensionController::class, 'checkReorder'])->name('alerts.check');
        Route::post('alerts/{alert}/acknowledge', [\App\Http\Controllers\Inventory\InventoryExtensionController::class, 'acknowledgeAlert'])->name('alerts.acknowledge');
        Route::get('intelligence', [\App\Http\Controllers\Inventory\InventoryExtensionController::class, 'intelligence'])->name('intelligence.index');
        Route::get('scan', [\App\Http\Controllers\Inventory\InventoryExtensionController::class, 'scan'])->name('scan');
        Route::get('products/{product}/print-label', [\App\Http\Controllers\Inventory\InventoryExtensionController::class, 'printLabel'])->name('products.print-label');

        // Extension: Multi-Branch Transfers
        Route::prefix('transfers')->name('transfers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Inventory\InventoryTransferController::class, 'index'])->name('index')->middleware('permission:inventory.transfer.view');
            Route::get('/create', [\App\Http\Controllers\Inventory\InventoryTransferController::class, 'create'])->name('create')->middleware('permission:inventory.transfer.request');
            Route::post('/', [\App\Http\Controllers\Inventory\InventoryTransferController::class, 'store'])->name('store')->middleware('permission:inventory.transfer.request');
            Route::get('/{transfer}', [\App\Http\Controllers\Inventory\InventoryTransferController::class, 'show'])->name('show')->middleware('permission:inventory.transfer.view');
            Route::post('/{transfer}/approve', [\App\Http\Controllers\Inventory\InventoryTransferController::class, 'approve'])->name('approve')->middleware('permission:inventory.transfer.approve');
            Route::post('/{transfer}/dispatch', [\App\Http\Controllers\Inventory\InventoryTransferController::class, 'dispatch'])->name('dispatch')->middleware('permission:inventory.transfer.dispatch');
            Route::post('/{transfer}/receive', [\App\Http\Controllers\Inventory\InventoryTransferController::class, 'receive'])->name('receive')->middleware('permission:inventory.transfer.receive');
        });

        // Reports
        Route::get('low-stock-alert', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'lowStockAlert'])->name('low-stock-alert');
        Route::get('stock-aging', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'stockAgingReport'])->name('stock-aging');
        Route::get('wastage-report', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'wastageReport'])->name('wastage-report');
        Route::get('multi-location-stock', [\App\Http\Controllers\Inventory\InventoryProductController::class, 'multiLocationStock'])->name('multi-location-stock');

        // Warehouses
        Route::resource('warehouses', \App\Http\Controllers\Inventory\WarehouseController::class);
        Route::post('warehouses/{warehouse}/bins', [\App\Http\Controllers\Inventory\WarehouseController::class, 'storeBin'])->name('warehouses.bins.store');

        // Warehouse Reconciliation
        Route::prefix('reconciliation')->name('reconciliation.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Inventory\WarehouseReconciliationController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Inventory\WarehouseReconciliationController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Inventory\WarehouseReconciliationController::class, 'store'])->name('store');
            Route::get('/{reconciliation}', [\App\Http\Controllers\Inventory\WarehouseReconciliationController::class, 'show'])->name('show');
            Route::post('/{reconciliation}/items/{item}', [\App\Http\Controllers\Inventory\WarehouseReconciliationController::class, 'updateItem'])->name('update-item');
            Route::post('/{reconciliation}/finalize', [\App\Http\Controllers\Inventory\WarehouseReconciliationController::class, 'finalize'])->name('finalize');
        });
    });

    // ==================== WEIGHT & PRICE CALCULATOR ====================
    Route::prefix('calculator')->name('calculator.')->middleware('permission:calculator.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'index'])->name('index');
        Route::post('/convert', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'convert'])->name('convert');
        Route::post('/detect-unit', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'detectUnit'])->name('detect-unit');
        Route::post('/calculate', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'calculate'])->name('calculate');
        Route::post('/save', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'save'])->name('save');
        Route::post('/save-as-product', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'saveAsProduct'])->name('save-as-product');
        Route::get('/history', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'history'])->name('history');
        Route::delete('/{id}', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'delete'])->name('delete');
        Route::get('/{id}/export', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'exportPDF'])->name('export');
        Route::get('/market-rate', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'marketRate'])->name('market-rate');
        Route::post('/use-in-pos', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'useInPOS'])->name('use-in-pos');
        Route::get('/{id}', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'view'])->name('view');
        Route::get('/presets', [\App\Http\Controllers\Calculator\WeightCalculatorController::class, 'presets'])->name('presets');
    });

    // Products
    Route::resource('products', ProductController::class);
    Route::post('/products/update-stock/{id}', [ProductController::class, 'updateStock'])->name('products.update-stock');

    // Vendors
    Route::resource('vendors', VendorController::class);

    // Sales - Legacy Routes (now redirected to POS module)
    Route::get('/sales', fn () => redirect()->route('pos.sales.index'))->name('sales.index');
    Route::get('/sales/create', fn () => redirect()->route('pos.sales.create'))->name('sales.create');
    Route::post('/sales', function () {
        return redirect(route('pos.sales.store'), 307);
    })->name('sales.store');
    Route::get('/sales/{sale}', function ($sale) {
        return redirect()->route('pos.sales.show', $sale);
    })->name('sales.show');
    Route::get('/sales/{sale}/edit', function ($sale) {
        return redirect()->route('pos.sales.edit', $sale);
    })->name('sales.edit');
    Route::match(['put', 'patch'], '/sales/{sale}', function ($sale) {
        return redirect(route('pos.sales.update', $sale), 307);
    })->name('sales.update');
    Route::delete('/sales/{sale}', function ($sale) {
        return redirect(route('pos.sales.destroy', $sale), 307);
    })->name('sales.destroy');
    Route::get('/sales/{sale}/invoice', [\App\Http\Controllers\POS\SaleController::class, 'printA4Invoice'])->name('sales.invoice');
    Route::get('/sales/{sale}/print', [\App\Http\Controllers\POS\SaleController::class, 'printA4Invoice'])->name('sales.print');

    // Price Calculator
    Route::get('/price-calculator', [PriceCalculatorController::class, 'index'])->name('calculator.index');
    Route::post('/price-calculator/calculate', [PriceCalculatorController::class, 'calculate'])->name('calculator.calculate');

    // Gold Rate
    // ==================== RATE & MARKET PRICE MANAGEMENT ====================
    Route::prefix('gold-rates')->name('gold-rates.')->group(function () {
        Route::get('/', [\App\Http\Controllers\GoldRateController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\GoldRateController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\GoldRateController::class, 'store'])->name('store');
        Route::get('/{goldRate}/edit', [\App\Http\Controllers\GoldRateController::class, 'edit'])->name('edit');
        Route::patch('/{goldRate}', [\App\Http\Controllers\GoldRateController::class, 'update'])->name('update');
        Route::post('/{goldRate}/approve', [\App\Http\Controllers\GoldRateController::class, 'approve'])->name('approve');
        Route::post('/{goldRate}/lock', [\App\Http\Controllers\GoldRateController::class, 'lock'])->name('lock');
        Route::post('/{goldRate}/unlock', [\App\Http\Controllers\GoldRateController::class, 'unlock'])->name('unlock');
    });

    Route::get('/gold-rate', [DashboardController::class, 'goldRate'])->name('gold-rate');
    Route::post('/gold-rate/update', [DashboardController::class, 'updateGoldRate'])->name('gold-rate.update');

    // ==================== ACCOUNTS & FINANCE ROUTES ====================
    Route::prefix('accounts')->name('accounts.')->middleware(['permission:reports.view', 'plan_feature:Accounting'])->group(function () {
        // ==================== PURCHASE & SUPPLIER
        Route::prefix('purchases')->name('purchases.')->middleware('permission:purchase.view')->group(function () {
            Route::get('orders/pending-payments', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'pendingPayments'])->name('orders.pending-payments');
            Route::get('orders/overdue', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'overdue'])->name('orders.overdue');
            Route::get('orders/export', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'export'])->name('orders.export');

            Route::resource('orders', \App\Http\Controllers\Accounts\PurchaseOrderController::class);
            Route::match(['get', 'post'], 'orders/{order}/confirm', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'confirm'])->name('orders.confirm');
            Route::match(['get', 'post'], 'orders/{order}/receive', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'receive'])->name('orders.receive');
            Route::match(['get', 'post'], 'orders/{order}/cancel', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'cancel'])->name('orders.cancel');
            Route::match(['get', 'post'], 'orders/{order}/payment', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'recordPayment'])->name('orders.payment');
            Route::match(['get', 'post'], 'orders/bulk-payment', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'bulkPayment'])->name('orders.bulk-payment');
            Route::match(['get', 'post'], 'orders/bulk-status', [\App\Http\Controllers\Accounts\PurchaseOrderController::class, 'bulkStatusUpdate'])->name('orders.bulk-status');

            Route::get('returns/export', [\App\Http\Controllers\Accounts\PurchaseReturnController::class, 'export'])->name('returns.export');
            Route::match(['get', 'post'], 'returns/{return}/approve', [\App\Http\Controllers\Accounts\PurchaseReturnController::class, 'approve'])->name('returns.approve');
            Route::match(['get', 'post'], 'returns/{return}/refund', [\App\Http\Controllers\Accounts\PurchaseReturnController::class, 'processRefund'])->name('returns.refund');
            Route::resource('returns', \App\Http\Controllers\Accounts\PurchaseReturnController::class);

            Route::get('payments/export', [\App\Http\Controllers\Accounts\SupplierPaymentController::class, 'export'])->name('payments.export');
            Route::match(['get', 'post'], 'payments/{payment}/approve', [\App\Http\Controllers\Accounts\SupplierPaymentController::class, 'approve'])->name('payments.approve');
            Route::match(['get', 'post'], 'payments/{payment}/reverse', [\App\Http\Controllers\Accounts\SupplierPaymentController::class, 'reverse'])->name('payments.reverse');
            Route::resource('payments', \App\Http\Controllers\Accounts\SupplierPaymentController::class);

            Route::get('analytics/dashboard', [\App\Http\Controllers\Accounts\PurchaseAnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
        });

        // ==================== PAYABLES
        Route::prefix('payables')->name('payables.')->group(function () {
            Route::resource('invoices', \App\Http\Controllers\Accounts\PurchaseOrderController::class);
        });

        // ==================== SUPPLIERS
        Route::prefix('suppliers')->name('suppliers.')->group(function () {
            Route::get('', [\App\Http\Controllers\Accounts\SupplierController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Accounts\SupplierController::class, 'create'])->name('create');
            Route::post('', [\App\Http\Controllers\Accounts\SupplierController::class, 'store'])->name('store');
            Route::get('{supplier}', [\App\Http\Controllers\Accounts\SupplierController::class, 'show'])->name('show');
            Route::get('{supplier}/edit', [\App\Http\Controllers\Accounts\SupplierController::class, 'edit'])->name('edit');
            Route::put('{supplier}', [\App\Http\Controllers\Accounts\SupplierController::class, 'update'])->name('update');
            Route::delete('{supplier}', [\App\Http\Controllers\Accounts\SupplierController::class, 'destroy'])->name('destroy');
            Route::match(['get', 'post'], '{supplier}/block', [\App\Http\Controllers\Accounts\SupplierController::class, 'block'])->name('block');
            Route::match(['get', 'post'], '{supplier}/unblock', [\App\Http\Controllers\Accounts\SupplierController::class, 'unblock'])->name('unblock');
            Route::match(['get', 'post'], '{supplier}/suspend', [\App\Http\Controllers\Accounts\SupplierController::class, 'suspend'])->name('suspend');
            Route::get('{supplier}/ledger', [\App\Http\Controllers\Accounts\SupplierController::class, 'ledger'])->name('ledger');
            Route::get('{supplier}/price-history', [\App\Http\Controllers\Accounts\SupplierController::class, 'priceHistory'])->name('price-history');
            Route::get('export', [\App\Http\Controllers\Accounts\SupplierController::class, 'export'])->name('export');
        });

        // ==================== GENERAL LEDGER
        Route::get('general-ledger/export-pdf', [\App\Http\Controllers\Accounts\GeneralLedgerController::class, 'exportPdf'])->name('general-ledger.export-pdf');
        Route::get('general-ledger/export-excel', [\App\Http\Controllers\Accounts\GeneralLedgerController::class, 'exportExcel'])->name('general-ledger.export-excel');
        Route::resource('general-ledger', \App\Http\Controllers\Accounts\GeneralLedgerController::class, ['only' => ['index', 'show']]);

        // ==================== ACCOUNTING DASHBOARD
        Route::get('accounting-dashboard', [\App\Http\Controllers\Accounts\AccountingDashboardController::class, 'index'])->name('accounting-dashboard.index');
        Route::get('accounting-dashboard/data', [\App\Http\Controllers\Accounts\AccountingDashboardController::class, 'data'])->name('accounting-dashboard.data');

        // ==================== FINANCIAL REPORTS
        Route::prefix('financial-reports')->name('financial-reports.')->group(function () {
            Route::get('trial-balance', [\App\Http\Controllers\Accounts\FinancialReportsController::class, 'trialBalance'])->name('trial-balance');
            Route::get('profit-loss', [\App\Http\Controllers\Accounts\FinancialReportsController::class, 'profitLoss'])->name('profit-loss');
            Route::get('balance-sheet', [\App\Http\Controllers\Accounts\FinancialReportsController::class, 'balanceSheet'])->name('balance-sheet');
            Route::get('export-pdf', [\App\Http\Controllers\Accounts\FinancialReportsController::class, 'exportPdf'])->name('export-pdf');
            Route::get('export-excel', [\App\Http\Controllers\Accounts\FinancialReportsController::class, 'exportExcel'])->name('export-excel');
        });

        // ==================== BANK & PAYMENTS
        Route::prefix('bank-payments')->name('bank-payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'index'])->name('index');

            // Bank Account CRUD
            Route::resource('accounts', \App\Http\Controllers\BankAccountController::class)->names([
                'index' => 'accounts.index',
                'create' => 'accounts.create',
                'store' => 'accounts.store',
                'show' => 'accounts.show',
                'edit' => 'accounts.edit',
                'update' => 'accounts.update',
                'destroy' => 'accounts.destroy',
            ]);

            // Deposits
            Route::get('/deposits/create', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'createDeposit'])->name('deposits.create');
            Route::post('/deposits', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'storeDeposit'])->name('deposits.store');

            // Withdrawals
            Route::get('/withdrawals/create', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'createWithdrawal'])->name('withdrawals.create');
            Route::post('/withdrawals', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'storeWithdrawal'])->name('withdrawals.store');

            // Transfers
            Route::get('/transfers/create', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'createTransfer'])->name('transfers.create');
            Route::post('/transfers', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'storeTransfer'])->name('transfers.store');

            // Cheques
            Route::get('/cheques', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'manageCheques'])->name('cheques.index');
            Route::get('/cheques/issue', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'issueCheque'])->name('cheques.issue');
            Route::post('/cheques/issue', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'storeIssuedCheque'])->name('cheques.store');

            // Reconciliation
            Route::get('/reconcile', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'reconcile'])->name('reconcile');
            Route::post('/reconcile', [\App\Http\Controllers\Accounts\BankPaymentsController::class, 'reconcile'])->name('reconcile.post');
        });

        // ==================== CASHBOOK, EXPENSE, INSTALLMENT
        Route::resource('cashbook', \App\Http\Controllers\CashbookController::class);
        Route::post('cashbook/{cashbook}/verify', [\App\Http\Controllers\CashbookController::class, 'verify'])->name('cashbook.verify');
        Route::post('cashbook/verify-all', [\App\Http\Controllers\CashbookController::class, 'verifyAll'])->name('cashbook.verify-all');
        Route::get('expense/subcategories', [\App\Http\Controllers\ExpenseController::class, 'getSubcategories'])->name('expense.subcategories');
        Route::get('expense/export', [\App\Http\Controllers\ExpenseController::class, 'export'])->name('expense.export');
        Route::post('expense/{expense}/approve', [\App\Http\Controllers\ExpenseController::class, 'approve'])->name('expense.approve');
        Route::post('expense/{expense}/reject', [\App\Http\Controllers\ExpenseController::class, 'reject'])->name('expense.reject');
        Route::resource('expense', \App\Http\Controllers\ExpenseController::class);

        Route::post('installment/apply-late-fees', [\App\Http\Controllers\InstallmentController::class, 'applyLateFees'])->name('installment.apply-late-fees');
        Route::post('installment/schedule/{schedule}/payment', [\App\Http\Controllers\InstallmentController::class, 'recordPayment'])->name('installment.record-payment');
        Route::post('installment/schedule/{schedule}/skip', [\App\Http\Controllers\InstallmentController::class, 'skipInstallment'])->name('installment.skip');
        Route::post('installment/schedule/{schedule}/reminder', [\App\Http\Controllers\InstallmentController::class, 'sendReminder'])->name('installment.send-reminder');
        Route::get('installment/dashboard', [\App\Http\Controllers\InstallmentController::class, 'dashboard'])->name('installment.dashboard');
        Route::resource('installment', \App\Http\Controllers\InstallmentController::class);
    });

    // ==================== AUDIT TRAIL ====================
    // Route::get('/audit-trail', [\App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit-trail');

    // ==================== AI CHATBOT ====================
    Route::middleware('plan_feature:AI Analytics')->group(function () {
        Route::get('/ai-chatbot', [AIChatbotController::class, 'chat'])->name('ai-chatbot.index');
        Route::post('/ai-chatbot/message', [AIChatbotController::class, 'chat'])->name('ai-chatbot.message');
        Route::get('/ai-chatbot/providers', [AIChatbotController::class, 'getProviders'])->name('ai-chatbot.providers');
    });

    // ==================== USER PROFILE & SECURITY ====================
    // Password Security (Accessible to all authenticated users)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/change-password', [\App\Http\Controllers\SecurityManager\SecurityManagerController::class, 'showChangePassword'])->name('password.change');
        Route::post('/change-password', [\App\Http\Controllers\SecurityManager\SecurityManagerController::class, 'updatePassword'])->name('password.update');
    });

    // ==================== SECURITY MANAGER MODULE ====================

    Route::prefix('security-manager')->name('security-manager.')->middleware('permission:security_manager.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\SecurityManager\SecurityManagerController::class, 'dashboard'])->name('dashboard');

        // Global Settings
        Route::get('/settings', [\App\Http\Controllers\SecurityManager\SecurityManagerController::class, 'settings'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SecurityManager\SecurityManagerController::class, 'updateSettings'])->name('settings.update');
        Route::get('/stats', [\App\Http\Controllers\SecurityManager\SecurityManagerController::class, 'stats'])->name('stats');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'store'])->name('store');
            Route::get('/{user}', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'edit'])->name('edit');
            Route::patch('/{user}', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'update'])->name('update');
            Route::post('/{user}/reset-password', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'resetPassword'])->name('reset-password');
            Route::post('/{user}/suspend', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'suspend'])->name('suspend');
            Route::post('/{user}/unlock', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'unlock'])->name('unlock');
            Route::post('/{user}/activate', [\App\Http\Controllers\SecurityManager\UserManagementController::class, 'activate'])->name('activate');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'store'])->name('store');
            Route::get('/{role}', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'show'])->name('show');
            Route::get('/{role}/edit', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'edit'])->name('edit');
            Route::patch('/{role}', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'update'])->name('update');
            Route::post('/{role}/clone', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'clone'])->name('clone');
            Route::post('/{role}/permissions/toggle', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'togglePermission'])->name('permissions.toggle');
            Route::post('/{role}/permissions/sync', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'updatePermissions'])->name('permissions.sync');
            Route::post('/{role}/permissions', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'assignPermission'])->name('permissions.assign');
            Route::delete('/{role}/permissions/{permission}', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'revokePermission'])->name('permissions.revoke');
            Route::get('/matrix/permissions', [\App\Http\Controllers\SecurityManager\RoleManagementController::class, 'permissionMatrix'])->name('permission-matrix');
        });

        Route::prefix('sessions')->name('sessions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SecurityManager\SessionManagementController::class, 'index'])->name('index');
            Route::get('/users/{user}/sessions', [\App\Http\Controllers\SecurityManager\SessionManagementController::class, 'userSessions'])->name('user-sessions');
            Route::post('/sessions/{session}/terminate', [\App\Http\Controllers\SecurityManager\SessionManagementController::class, 'terminate'])->name('terminate');
            Route::post('/users/{user}/terminate-all', [\App\Http\Controllers\SecurityManager\SessionManagementController::class, 'terminateAll'])->name('terminate-all');
            Route::get('/users/{user}/history', [\App\Http\Controllers\SecurityManager\SessionManagementController::class, 'sessionHistory'])->name('history');
            Route::post('/users/{user}/configure-timeout', [\App\Http\Controllers\SecurityManager\SessionManagementController::class, 'configureTimeout'])->name('configure-timeout');

            // Session Control Manager
            Route::get('/control', [\App\Http\Controllers\SecurityManager\SessionControlController::class, 'index'])->name('control.index');
            Route::post('/control/global', [\App\Http\Controllers\SecurityManager\SessionControlController::class, 'updateGlobal'])->name('control.global.update');
            Route::patch('/control/roles/{role}', [\App\Http\Controllers\SecurityManager\SessionControlController::class, 'updateRole'])->name('control.roles.update');
            Route::patch('/control/users/{user}', [\App\Http\Controllers\SecurityManager\SessionControlController::class, 'updateUser'])->name('control.users.update');
        });

        Route::prefix('audit')->name('audit.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SecurityManager\SecurityAuditController::class, 'index'])->name('index');
            Route::get('/export/csv', [\App\Http\Controllers\SecurityManager\SecurityAuditController::class, 'export'])->name('export');
            Route::get('/login-history', [\App\Http\Controllers\SecurityManager\SecurityAuditController::class, 'loginHistory'])->name('login-history');
            Route::get('/failed-attempts', [\App\Http\Controllers\SecurityManager\SecurityAuditController::class, 'failedAttempts'])->name('failed-attempts');
            Route::get('/{log}', [\App\Http\Controllers\SecurityManager\SecurityAuditController::class, 'show'])->name('show');
        });
    });

    // ==================== HR & PAYROLL MODULE ====================
    Route::prefix('hr')->name('hr.')->group(function () {
        // Employee Self-Service (Accessible to all employees)
        Route::get('/self-service', [\App\Http\Controllers\HR\SelfServiceController::class, 'index'])->name('self-service.index');
        Route::post('/self-service/attendance/mark', [\App\Http\Controllers\HR\SelfServiceController::class, 'markAttendance'])->name('self-service.attendance.mark');
        Route::post('/self-service/attendance/break', [\App\Http\Controllers\HR\SelfServiceController::class, 'toggleBreak'])->name('self-service.attendance.break');
        Route::post('/self-service/leave/store', [\App\Http\Controllers\HR\SelfServiceController::class, 'storeLeave'])->name('self-service.leave.store');

        Route::middleware(['permission:hr.view'])->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\HR\HRDashboardController::class, 'index'])->name('dashboard');
            Route::resource('employees', \App\Http\Controllers\HR\EmployeeController::class);
            Route::post('employees/{employee}/assign-shift', [\App\Http\Controllers\HR\EmployeeController::class, 'assignShift'])->name('employees.assign-shift');
            Route::post('employees/{employee}/documents', [\App\Http\Controllers\HR\EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
            Route::post('employees/{employee}/lifecycle', [\App\Http\Controllers\HR\LifecycleController::class, 'store'])->name('employees.lifecycle.store');

            Route::get('performance', [\App\Http\Controllers\HR\PerformanceController::class, 'index'])->name('performance.index');
            Route::get('employees/{employee}/performance/create', [\App\Http\Controllers\HR\PerformanceController::class, 'create'])->name('performance.create');
            Route::post('employees/{employee}/performance', [\App\Http\Controllers\HR\PerformanceController::class, 'store'])->name('performance.store');

            Route::get('attendance', [\App\Http\Controllers\HR\AttendanceController::class, 'index'])->name('attendance.index');
            Route::post('attendance/mark', [\App\Http\Controllers\HR\AttendanceController::class, 'mark'])->name('attendance.mark');
            Route::post('attendance/break', [\App\Http\Controllers\HR\AttendanceController::class, 'toggleBreak'])->name('attendance.break');
            Route::post('attendance/manual', [\App\Http\Controllers\HR\AttendanceController::class, 'manualMark'])->name('attendance.manual');
            Route::get('attendance/report', [\App\Http\Controllers\HR\AttendanceController::class, 'report'])->name('attendance.report');
            Route::get('attendance/export-excel', [\App\Http\Controllers\HR\AttendanceController::class, 'exportExcel'])->name('attendance.export-excel');
            Route::get('attendance/export-pdf', [\App\Http\Controllers\HR\AttendanceController::class, 'exportPdf'])->name('attendance.export-pdf');
            Route::post('attendance/lock', [\App\Http\Controllers\HR\AttendanceController::class, 'lockAttendance'])->name('attendance.lock');

            Route::resource('shifts', \App\Http\Controllers\HR\ShiftController::class);
            Route::resource('attendance-rules', \App\Http\Controllers\HR\AttendanceRuleController::class);

            Route::get('leave', [\App\Http\Controllers\HR\LeaveController::class, 'index'])->name('leave.index');
            Route::post('leave', [\App\Http\Controllers\HR\LeaveController::class, 'store'])->name('leave.store');
            Route::post('leave/{leaveRequest}/approve', [\App\Http\Controllers\HR\LeaveController::class, 'approve'])->name('leave.approve');
            Route::get('leave/{leaveRequest}/reject', [\App\Http\Controllers\HR\LeaveController::class, 'reject'])->name('leave.reject');

            Route::resource('holidays', \App\Http\Controllers\HR\HolidayController::class);

            Route::get('expenses', [\App\Http\Controllers\HR\ExpenseController::class, 'index'])->name('expenses.index');
            Route::post('employees/{employee}/expenses', [\App\Http\Controllers\HR\ExpenseController::class, 'store'])->name('expenses.store');
            Route::post('expenses/{expense}/approve', [\App\Http\Controllers\HR\ExpenseController::class, 'approve'])->name('expenses.approve');

            Route::get('payroll', [\App\Http\Controllers\HR\PayrollController::class, 'index'])->name('payroll.index');
            Route::get('payroll/settings', [\App\Http\Controllers\HR\PayrollSettingsController::class, 'index'])->name('payroll.settings');
            Route::post('payroll/settings/components', [\App\Http\Controllers\HR\PayrollSettingsController::class, 'storeComponent'])->name('payroll.settings.components.store');
            Route::post('payroll/settings/rules', [\App\Http\Controllers\HR\PayrollSettingsController::class, 'storeRule'])->name('payroll.settings.rules.store');
            Route::post('payroll/bulk-approve', [\App\Http\Controllers\HR\PayrollController::class, 'bulkApprove'])->name('payroll.bulk-approve');
            Route::post('payroll/bulk-pay', [\App\Http\Controllers\HR\PayrollController::class, 'bulkPay'])->name('payroll.bulk-pay');
            Route::get('payroll/{id}/download', [\App\Http\Controllers\HR\PayrollController::class, 'downloadPayslip'])->name('payroll.download');

            Route::post('employees/{employee}/compensation/salary', [\App\Http\Controllers\HR\CompensationController::class, 'updateSalaryStructure'])->name('employees.compensation.salary.update');
            Route::post('employees/{employee}/compensation/karigar', [\App\Http\Controllers\HR\CompensationController::class, 'updateKarigarRate'])->name('employees.compensation.karigar.update');
            Route::post('employees/{employee}/compensation/sales', [\App\Http\Controllers\HR\CompensationController::class, 'updateSalesCommission'])->name('employees.compensation.sales.update');

            Route::get('payroll/{id}', [\App\Http\Controllers\HR\PayrollController::class, 'show'])->name('payroll.show');
            Route::post('payroll/generate', [\App\Http\Controllers\HR\PayrollController::class, 'generate'])->name('payroll.generate');
            Route::post('payroll/{id}/approve', [\App\Http\Controllers\HR\PayrollController::class, 'approve'])->name('payroll.approve');
            Route::post('payroll/{id}/pay', [\App\Http\Controllers\HR\PayrollController::class, 'pay'])->name('payroll.pay');

            // Recruitment Module
            Route::get('recruitment', [\App\Http\Controllers\HR\RecruitmentController::class, 'index'])->name('recruitment.index');
            Route::get('recruitment/jobs/create', [\App\Http\Controllers\HR\RecruitmentController::class, 'createJob'])->name('recruitment.jobs.create');
            Route::post('recruitment/jobs', [\App\Http\Controllers\HR\RecruitmentController::class, 'storeJob'])->name('recruitment.jobs.store');
            Route::get('recruitment/jobs/{job}', [\App\Http\Controllers\HR\RecruitmentController::class, 'showJob'])->name('recruitment.jobs.show');
            Route::post('recruitment/jobs/{job}/apply', [\App\Http\Controllers\HR\RecruitmentController::class, 'apply'])->name('recruitment.jobs.apply');
            Route::post('recruitment/candidates/{candidate}/schedule', [\App\Http\Controllers\HR\RecruitmentController::class, 'scheduleInterview'])->name('recruitment.candidates.schedule');
            Route::post('recruitment/candidates/{candidate}/hire', [\App\Http\Controllers\HR\RecruitmentController::class, 'hire'])->name('recruitment.candidates.hire');
            Route::post('recruitment/interviews/{interview}/feedback', [\App\Http\Controllers\HR\RecruitmentController::class, 'submitFeedback'])->name('recruitment.interviews.feedback');

            // Assets Management (HR)
            Route::get('assets-hr', [\App\Http\Controllers\HR\AssetController::class, 'index'])->name('assets.index');
            Route::post('assets-hr', [\App\Http\Controllers\HR\AssetController::class, 'store'])->name('assets.store');
            Route::post('assets-hr/{asset}/assign', [\App\Http\Controllers\HR\AssetController::class, 'assign'])->name('assets.assign');
            Route::post('assets-hr/assignments/{assignment}/return', [\App\Http\Controllers\HR\AssetController::class, 'return'])->name('assets.return');

            // Disciplinary Management
            Route::get('disciplinary', [\App\Http\Controllers\HR\DisciplinaryController::class, 'index'])->name('disciplinary.index');
            Route::post('disciplinary', [\App\Http\Controllers\HR\DisciplinaryController::class, 'store'])->name('disciplinary.store');
            Route::patch('disciplinary/{action}', [\App\Http\Controllers\HR\DisciplinaryController::class, 'update'])->name('disciplinary.update');

            // Onboarding Module
            Route::get('onboarding', [\App\Http\Controllers\HR\OnboardingController::class, 'index'])->name('onboarding.index');
            Route::post('onboarding/start', [\App\Http\Controllers\HR\OnboardingController::class, 'start'])->name('onboarding.start');
            Route::post('onboarding/tasks/{task}/complete', [\App\Http\Controllers\HR\OnboardingController::class, 'completeTask'])->name('onboarding.tasks.complete');
        });
    });

    // ==================== ASSET MANAGEMENT MODULE ====================
    Route::group(['prefix' => 'assets', 'as' => 'assets.', 'middleware' => ['permission:assets.view']], function () {
        Route::get('/', [\App\Http\Controllers\Assets\AssetController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Assets\AssetController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Assets\AssetController::class, 'store'])->name('store');
        Route::get('/{asset}', [\App\Http\Controllers\Assets\AssetController::class, 'show'])->name('show');
        Route::post('/{asset}/depreciation', [\App\Http\Controllers\Assets\AssetController::class, 'calculateDepreciation'])->name('depreciation');
        Route::post('/{asset}/dispose', [\App\Http\Controllers\Assets\AssetController::class, 'dispose'])->name('dispose');
    });

    // ==================== MARKETING & PROMOTIONS MODULE ====================
    Route::prefix('marketing')->name('marketing.')->middleware(['permission:marketing.view'])->group(function () {
        Route::resource('promotions', \App\Http\Controllers\Marketing\PromotionController::class);
        Route::post('coupons/apply', [\App\Http\Controllers\Marketing\PromotionController::class, 'applyCoupon'])->name('coupons.apply');
    });

    // ==================== TAX & COMPLIANCE MODULE ====================
    Route::prefix('compliance')->name('compliance.')->middleware(['permission:compliance.view'])->group(function () {
        Route::get('tax', [\App\Http\Controllers\Compliance\TaxComplianceController::class, 'index'])->name('tax.index');
        Route::get('tax/reports', [\App\Http\Controllers\Compliance\TaxComplianceController::class, 'reports'])->name('tax.reports');
        Route::get('tax/input-tax-credit', [\App\Http\Controllers\Compliance\TaxComplianceController::class, 'inputTaxCredit'])->name('tax.itc');
        Route::post('tax/slabs', [\App\Http\Controllers\Compliance\TaxComplianceController::class, 'storeSlab'])->name('tax.slabs.store');
        Route::post('tax/configurations', [\App\Http\Controllers\Compliance\TaxComplianceController::class, 'storeTaxConfig'])->name('tax.config.store');
        Route::post('tax/filing', [\App\Http\Controllers\Compliance\TaxComplianceController::class, 'storeFilingRecord'])->name('tax.filing.store');
        Route::get('aml', [\App\Http\Controllers\Compliance\TaxComplianceController::class, 'amlAlerts'])->name('aml.alerts');
    });

    // ==================== WORKFLOW & APPROVALS MODULE ====================
    Route::prefix('workflow')->name('workflow.')->middleware(['auth'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Workflow\WorkflowController::class, 'index'])->name('index');
        Route::get('my-approvals', [\App\Http\Controllers\Workflow\WorkflowController::class, 'myApprovals'])->name('approvals');
        Route::post('approve/{id}', [\App\Http\Controllers\Workflow\WorkflowController::class, 'approve'])->name('approve');
        Route::post('reject/{id}', [\App\Http\Controllers\Workflow\WorkflowController::class, 'reject'])->name('reject');
    });

    // ==================== ENTERPRISE ADMIN MODULES ====================
    Route::prefix('admin')->name('admin.')->middleware(['permission:admin.view'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\EnterpriseAdminController::class, 'dashboard'])->name('dashboard');

        // Workflow Management
        Route::resource('workflows', \App\Http\Controllers\Workflow\WorkflowController::class);
        Route::get('my-approvals', [\App\Http\Controllers\Workflow\WorkflowController::class, 'myApprovals'])->name('approvals');
        Route::post('approve/{id}', [\App\Http\Controllers\Workflow\WorkflowController::class, 'approve'])->name('approve');
        Route::post('reject/{id}', [\App\Http\Controllers\Workflow\WorkflowController::class, 'reject'])->name('reject');


        // API Manager
        Route::get('api', [\App\Http\Controllers\Admin\ApiManagerController::class, 'index'])->name('api.index');
        Route::post('api/keys', [\App\Http\Controllers\Admin\ApiManagerController::class, 'storeKey'])->name('api.keys.store');
        Route::post('api/keys/{apiKey}/rotate', [\App\Http\Controllers\Admin\ApiManagerController::class, 'rotate'])->name('api.keys.rotate');
        Route::put('api/keys/{apiKey}', [\App\Http\Controllers\Admin\ApiManagerController::class, 'update'])->name('api.keys.update');
        Route::delete('api/keys/{apiKey}', [\App\Http\Controllers\Admin\ApiManagerController::class, 'destroy'])->name('api.keys.destroy');


        // Backup & Recovery
        Route::get('backup', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('backup.index');
        Route::post('backup', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('backup.create');

        // Notifications
        Route::get('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/templates', [\App\Http\Controllers\Admin\NotificationController::class, 'storeTemplate'])->name('notifications.templates.store');
    });

    // ==================== CRM & LOYALTY MODULE ====================
    Route::prefix('crm')->name('crm.')->middleware(['permission:crm.view'])->group(function () {
        Route::get('loyalty', [\App\Http\Controllers\CRM\LoyaltyController::class, 'index'])->name('loyalty.index');
    });

    // ==================== VENDOR RATING MODULE ====================
    Route::prefix('vendors')->name('vendors.')->middleware(['permission:purchase.view'])->group(function () {
        Route::get('ratings', [\App\Http\Controllers\Procurement\VendorRatingController::class, 'index'])->name('ratings.index');
        Route::post('ratings', [\App\Http\Controllers\Procurement\VendorRatingController::class, 'store'])->name('ratings.store');
        Route::get('performance/{supplier}', [\App\Http\Controllers\Procurement\VendorRatingController::class, 'supplierPerformance'])->name('performance');
    });

    // ==================== API ROUTES (for AJAX) ====================
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('branches', [\App\Http\Controllers\API\BranchController::class, 'store'])->name('branches.store');
        Route::get('gold-rate', [DashboardController::class, 'getGoldRateApi'])->name('gold-rate.api');

        // Security Session Heartbeat
        Route::post('heartbeat', [\App\Http\Controllers\SecurityManager\SecurityHeartbeatController::class, 'beat'])->name('heartbeat');
        Route::post('session-logout', [\App\Http\Controllers\SecurityManager\SecurityHeartbeatController::class, 'logout'])->name('session-logout');
        // Tab close logout route (CSRF-exempt for sendBeacon)
        Route::post('tab-close-logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'tabCloseLogout'])
            ->name('logout.tab-close')
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

        // Mobile POS API
        Route::get('mobile/products', [\App\Http\Controllers\API\MobilePOSController::class, 'searchProducts']);
        Route::post('mobile/sales', [\App\Http\Controllers\API\MobilePOSController::class, 'createSale']);
    });
});

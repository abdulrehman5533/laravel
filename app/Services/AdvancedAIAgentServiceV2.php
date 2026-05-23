<?php

namespace App\Services;

use App\Models\InventoryProduct;
use App\Models\Customer;
use App\Models\PosSale;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\GoldRate;
use App\Models\PurchaseOrder;
use App\Models\ServiceJob;
use App\Models\Girvi;
use App\Models\CustomerLedger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AdvancedAIAgentServiceV2
{
    private $groqApiKey;
    private $groqBaseUrl = 'https://api.groq.com/openai/v1';
    private $voiceService;

    public function __construct(VoiceProcessingService $voiceService)
    {
        $this->groqApiKey = env('GROQ_API_KEY', 'gsk_test_key');
        $this->voiceService = $voiceService;
    }

    /**
     * Process message with advanced intents
     */
    public function processMessage($message, $language = 'ur')
    {
        try {
            $intent = $this->detectAdvancedIntent($message);
            
            if ($intent['type'] !== 'general') {
                $result = $this->handleAdvancedIntent($intent, $message);
                if ($result) {
                    return $result;
                }
            }

            return $this->handleGeneralQuery($message, $language);
        } catch (\Exception $e) {
            Log::error('AI Agent error: ' . $e->getMessage());
            return [
                'status' => 'success',
                'data' => ['answer' => 'معافی چاہتا ہوں، کچھ خرابی ہوئی۔']
            ];
        }
    }

    /**
     * Detect advanced intents
     */
    private function detectAdvancedIntent($message)
    {
        $msg = strtolower($message);

        // Inventory Advanced
        if (preg_match('/(low stock|کم اسٹاک|reorder|دوبارہ آرڈر|valuation|قیمت)/i', $msg)) {
            return ['type' => 'inventory_advanced', 'action' => 'analyze'];
        }

        // Sales Advanced
        if (preg_match('/(profit|منافع|loss|نقصان|margin|حاشیہ|revenue|آمدنی)/i', $msg)) {
            return ['type' => 'sales_advanced', 'action' => 'analyze'];
        }

        // Customer Analysis
        if (preg_match('/(customer|گاہک|client|کلائنٹ|ledger|بہی|outstanding|بقایا)/i', $msg)) {
            return ['type' => 'customer_analysis', 'action' => 'analyze'];
        }

        // Purchase Analysis
        if (preg_match('/(purchase|خریداری|supplier|سپلائی|pending|زیرالتوا|payment|ادائیگی)/i', $msg)) {
            return ['type' => 'purchase_analysis', 'action' => 'analyze'];
        }

        // Service Jobs
        if (preg_match('/(service|خدمت|repair|مرمت|job|کام|pending|زیرالتوا)/i', $msg)) {
            return ['type' => 'service_analysis', 'action' => 'analyze'];
        }

        // Girvi (Pawn)
        if (preg_match('/(girvi|گروی|pawn|رہن|loan|قرض|interest|سود)/i', $msg)) {
            return ['type' => 'girvi_analysis', 'action' => 'analyze'];
        }

        // Payroll
        if (preg_match('/(payroll|تنخواہ|salary|معاوضہ|attendance|حاضری|leave|چھٹی)/i', $msg)) {
            return ['type' => 'payroll_analysis', 'action' => 'analyze'];
        }

        // Financial Reports
        if (preg_match('/(financial|مالیاتی|report|رپورٹ|balance|توازن|cash flow|نقد رقم)/i', $msg)) {
            return ['type' => 'financial_analysis', 'action' => 'analyze'];
        }

        // Inventory
        if (preg_match('/(stock|inventory|product|item|quantity|qty|aaya|received|add|update)/i', $msg)) {
            return ['type' => 'inventory', 'action' => 'check_or_update'];
        }

        // Sales
        if (preg_match('/(sale|sell|sold|customer|purchase|buy|invoice)/i', $msg)) {
            return ['type' => 'sales', 'action' => 'check_or_create'];
        }

        // Employee
        if (preg_match('/(employee|worker|staff|hire|salary|wage|pay)/i', $msg)) {
            return ['type' => 'employee', 'action' => 'check_or_add'];
        }

        // Reports
        if (preg_match('/(report|summary|total|count|how many|kitna|statistics|data)/i', $msg)) {
            return ['type' => 'reports', 'action' => 'generate'];
        }

        // Gold Rate
        if (preg_match('/(gold|rate|price|market|sona|kimat)/i', $msg)) {
            return ['type' => 'gold_rate', 'action' => 'check'];
        }

        return ['type' => 'general', 'action' => 'query'];
    }

    /**
     * Handle advanced intents
     */
    private function handleAdvancedIntent($intent, $message)
    {
        switch ($intent['type']) {
            case 'inventory_advanced':
                return $this->handleInventoryAdvanced($message);
            case 'sales_advanced':
                return $this->handleSalesAdvanced($message);
            case 'customer_analysis':
                return $this->handleCustomerAnalysis($message);
            case 'purchase_analysis':
                return $this->handlePurchaseAnalysis($message);
            case 'service_analysis':
                return $this->handleServiceAnalysis($message);
            case 'girvi_analysis':
                return $this->handleGirviAnalysis($message);
            case 'payroll_analysis':
                return $this->handlePayrollAnalysis($message);
            case 'financial_analysis':
                return $this->handleFinancialAnalysis($message);
            case 'inventory':
                return $this->handleInventory($message);
            case 'sales':
                return $this->handleSales($message);
            case 'employee':
                return $this->handleEmployee($message);
            case 'reports':
                return $this->handleReports($message);
            case 'gold_rate':
                return $this->handleGoldRate($message);
            default:
                return null;
        }
    }

    /**
     * Inventory Advanced Analysis
     */
    private function handleInventoryAdvanced($message)
    {
        $lowStockProducts = InventoryProduct::where('current_stock', '<=', DB::raw('reorder_level'))
            ->select('name', 'current_stock', 'reorder_level', 'selling_price')
            ->limit(10)
            ->get();

        $totalValue = InventoryProduct::sum(DB::raw('current_stock * selling_price'));
        $totalProducts = InventoryProduct::count();
        $totalStock = InventoryProduct::sum('current_stock');

        $list = $lowStockProducts->map(fn($p) => "{$p->name}: {$p->current_stock} units (حد: {$p->reorder_level})")->implode("\n");

        return [
            'status' => 'success',
            'data' => [
                'answer' => "📊 اسٹاک تجزیہ:\n\n" .
                           "کل مصنوعات: {$totalProducts}\n" .
                           "کل اسٹاک: {$totalStock} units\n" .
                           "کل قیمت: Rs. " . number_format($totalValue, 2) . "\n\n" .
                           "کم اسٹاک والی مصنوعات:\n{$list}"
            ]
        ];
    }

    /**
     * Sales Advanced Analysis
     */
    private function handleSalesAdvanced($message)
    {
        $todaySales = PosSale::whereDate('created_at', today())->sum('total');
        $thisMonthSales = PosSale::whereMonth('created_at', now()->month)->sum('total');
        $totalSales = PosSale::sum('total');
        $avgSale = PosSale::avg('total');

        $topCustomers = PosSale::select('pos_customer_id', DB::raw('SUM(total) as total_spent'))
            ->groupBy('pos_customer_id')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->with('customer')
            ->get();

        $topList = $topCustomers->map(fn($s) => $s->customer ? "{$s->customer->name}: Rs. " . number_format($s->total_spent, 2) : "Unknown")->implode("\n");

        return [
            'status' => 'success',
            'data' => [
                'answer' => "💰 فروخت کا تجزیہ:\n\n" .
                           "آج کی فروخت: Rs. " . number_format($todaySales, 2) . "\n" .
                           "اس ماہ کی فروخت: Rs. " . number_format($thisMonthSales, 2) . "\n" .
                           "کل فروخت: Rs. " . number_format($totalSales, 2) . "\n" .
                           "اوسط فروخت: Rs. " . number_format($avgSale, 2) . "\n\n" .
                           "بہترین گاہکین:\n{$topList}"
            ]
        ];
    }

    /**
     * Customer Analysis
     */
    private function handleCustomerAnalysis($message)
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->count();
        $totalPurchases = PosSale::sum('total');
        $avgCustomerValue = $totalCustomers > 0 ? $totalPurchases / $totalCustomers : 0;

        $topCustomers = Customer::select('name', 'total_purchases')
            ->orderByDesc('total_purchases')
            ->limit(5)
            ->get();

        $topList = $topCustomers->map(fn($c) => "{$c->name}: Rs. " . number_format($c->total_purchases, 2))->implode("\n");

        return [
            'status' => 'success',
            'data' => [
                'answer' => "👥 گاہکین کا تجزیہ:\n\n" .
                           "کل گاہکین: {$totalCustomers}\n" .
                           "فعال گاہکین: {$activeCustomers}\n" .
                           "اوسط گاہک کی قیمت: Rs. " . number_format($avgCustomerValue, 2) . "\n\n" .
                           "بہترین گاہکین:\n{$topList}"
            ]
        ];
    }

    /**
     * Purchase Analysis
     */
    private function handlePurchaseAnalysis($message)
    {
        $totalPurchases = PurchaseOrder::sum('total_amount');
        $pendingPurchases = PurchaseOrder::where('status', 'pending')->sum('total_amount');
        $completedPurchases = PurchaseOrder::where('status', 'completed')->sum('total_amount');
        $totalOrders = PurchaseOrder::count();

        $pendingOrders = PurchaseOrder::where('status', 'pending')
            ->select('po_number', 'total_amount', 'supplier_id')
            ->with('supplier')
            ->limit(5)
            ->get();

        $pendingList = $pendingOrders->map(fn($o) => "{$o->po_number}: Rs. " . number_format($o->total_amount, 2))->implode("\n");

        return [
            'status' => 'success',
            'data' => [
                'answer' => "📦 خریداری کا تجزیہ:\n\n" .
                           "کل خریداری: Rs. " . number_format($totalPurchases, 2) . "\n" .
                           "زیرالتوا: Rs. " . number_format($pendingPurchases, 2) . "\n" .
                           "مکمل شدہ: Rs. " . number_format($completedPurchases, 2) . "\n" .
                           "کل آرڈرز: {$totalOrders}\n\n" .
                           "زیرالتوا آرڈرز:\n{$pendingList}"
            ]
        ];
    }

    /**
     * Service Analysis
     */
    private function handleServiceAnalysis($message)
    {
        $totalJobs = ServiceJob::count();
        $pendingJobs = ServiceJob::where('status', 'pending')->count();
        $completedJobs = ServiceJob::where('status', 'completed')->count();
        $totalRevenue = ServiceJob::sum('final_charge');

        $pendingJobsList = ServiceJob::where('status', 'pending')
            ->select('job_number', 'customer_id', 'final_charge')
            ->with('customer')
            ->limit(5)
            ->get();

        $jobsList = $pendingJobsList->map(fn($j) => "{$j->job_number}: Rs. " . number_format($j->final_charge, 2))->implode("\n");

        return [
            'status' => 'success',
            'data' => [
                'answer' => "🔧 خدمت کا تجزیہ:\n\n" .
                           "کل کام: {$totalJobs}\n" .
                           "زیرالتوا: {$pendingJobs}\n" .
                           "مکمل شدہ: {$completedJobs}\n" .
                           "کل آمدنی: Rs. " . number_format($totalRevenue, 2) . "\n\n" .
                           "زیرالتوا کام:\n{$jobsList}"
            ]
        ];
    }

    /**
     * Girvi Analysis
     */
    private function handleGirviAnalysis($message)
    {
        $totalGirvi = Girvi::count();
        $activeGirvi = Girvi::where('status', 'active')->count();
        $totalAmount = Girvi::sum('loan_amount');
        $totalInterest = Girvi::sum('interest_amount');

        $activeLoans = Girvi::where('status', 'active')
            ->select('girvi_number', 'loan_amount', 'interest_amount')
            ->limit(5)
            ->get();

        $loansList = $activeLoans->map(fn($g) => "{$g->girvi_number}: Rs. " . number_format($g->loan_amount, 2))->implode("\n");

        return [
            'status' => 'success',
            'data' => [
                'answer' => "💍 گروی کا تجزیہ:\n\n" .
                           "کل گروی: {$totalGirvi}\n" .
                           "فعال گروی: {$activeGirvi}\n" .
                           "کل رقم: Rs. " . number_format($totalAmount, 2) . "\n" .
                           "کل سود: Rs. " . number_format($totalInterest, 2) . "\n\n" .
                           "فعال گروی:\n{$loansList}"
            ]
        ];
    }

    /**
     * Payroll Analysis
     */
    private function handlePayrollAnalysis($message)
    {
        $totalEmployees = Employee::count();
        $totalSalary = Employee::sum('salary');
        $avgSalary = $totalEmployees > 0 ? $totalSalary / $totalEmployees : 0;

        $employees = Employee::select('name', 'position', 'salary')
            ->orderByDesc('salary')
            ->limit(5)
            ->get();

        $empList = $employees->map(fn($e) => "{$e->name} ({$e->position}): Rs. " . number_format($e->salary, 2))->implode("\n");

        return [
            'status' => 'success',
            'data' => [
                'answer' => "💼 تنخواہ کا تجزیہ:\n\n" .
                           "کل ملازمین: {$totalEmployees}\n" .
                           "کل تنخواہیں: Rs. " . number_format($totalSalary, 2) . "\n" .
                           "اوسط تنخواہ: Rs. " . number_format($avgSalary, 2) . "\n\n" .
                           "بہترین تنخواہیں:\n{$empList}"
            ]
        ];
    }

    /**
     * Financial Analysis
     */
    private function handleFinancialAnalysis($message)
    {
        $totalSales = PosSale::sum('total');
        $totalPurchases = PurchaseOrder::sum('total_amount');
        $totalExpenses = DB::table('expenses')->sum('amount');
        $profit = $totalSales - $totalPurchases - $totalExpenses;

        return [
            'status' => 'success',
            'data' => [
                'answer' => "📈 مالیاتی تجزیہ:\n\n" .
                           "کل فروخت: Rs. " . number_format($totalSales, 2) . "\n" .
                           "کل خریداری: Rs. " . number_format($totalPurchases, 2) . "\n" .
                           "کل اخراجات: Rs. " . number_format($totalExpenses, 2) . "\n" .
                           "منافع: Rs. " . number_format($profit, 2)
            ]
        ];
    }

    /**
     * Handle basic inventory
     */
    private function handleInventory($message)
    {
        preg_match('/(?:product|item|stock|gold|silver|jewelry|jewellery)\s+(?:named|called|is|ka|ke)?\s*([a-zA-Z\s]+?)(?:\s+(?:quantity|units|pieces|qty|stock|aaya|received)|$)/i', $message, $matches);
        $productName = $matches[1] ?? null;

        preg_match('/(\d+)\s*(?:units?|pieces?|qty|quantity|items?|aaya|received)/i', $message, $qtyMatches);
        $quantity = $qtyMatches[1] ?? null;

        if (!$productName) {
            $products = InventoryProduct::select('name', 'current_stock', 'selling_price')->limit(10)->get();
            $list = $products->map(fn($p) => "{$p->name}: {$p->current_stock} units")->implode("\n");
            
            return [
                'status' => 'success',
                'data' => ['answer' => "موجودہ اسٹاک:\n\n{$list}"]
            ];
        }

        $product = InventoryProduct::where('name', 'like', "%{$productName}%")->first();

        if (!$product) {
            return [
                'status' => 'success',
                'data' => ['answer' => "'{$productName}' نہیں ملا۔"]
            ];
        }

        if ($quantity) {
            $product->current_stock += (int)$quantity;
            $product->save();

            return [
                'status' => 'success',
                'data' => [
                    'answer' => "✓ اسٹاک اپڈیٹ ہو گیا!\n\n{$product->name}\nشامل کیے گئے: {$quantity} units\nکل: {$product->current_stock} units"
                ]
            ];
        }

        return [
            'status' => 'success',
            'data' => [
                'answer' => "{$product->name}\nموجودہ اسٹاک: {$product->current_stock} units\nقیمت: Rs. " . number_format($product->selling_price, 2)
            ]
        ];
    }

    /**
     * Handle sales
     */
    private function handleSales($message)
    {
        $todaySales = PosSale::whereDate('created_at', today())->sum('total');
        $totalSales = PosSale::sum('total');
        $salesCount = PosSale::count();

        return [
            'status' => 'success',
            'data' => [
                'answer' => "فروخت کی معلومات:\n\nآج کی فروخت: Rs. " . number_format($todaySales, 2) . 
                           "\nکل فروخت: Rs. " . number_format($totalSales, 2) . 
                           "\nکل لین دین: {$salesCount}"
            ]
        ];
    }

    /**
     * Handle employee
     */
    private function handleEmployee($message)
    {
        $employees = Employee::select('name', 'position', 'salary')->limit(5)->get();
        
        if ($employees->isEmpty()) {
            return [
                'status' => 'success',
                'data' => ['answer' => 'کوئی ملازم ریکارڈ نہیں ملا۔']
            ];
        }

        $list = $employees->map(fn($e) => "{$e->name} ({$e->position}) - Rs. " . number_format($e->salary, 2))->implode("\n");

        return [
            'status' => 'success',
            'data' => ['answer' => "ملازمین:\n\n{$list}"]
        ];
    }

    /**
     * Handle reports
     */
    private function handleReports($message)
    {
        $totalCustomers = Customer::count();
        $totalProducts = InventoryProduct::count();
        $totalSales = PosSale::sum('total');
        $totalEmployees = Employee::count();

        return [
            'status' => 'success',
            'data' => [
                'answer' => "کاروباری خلاصہ:\n\n" .
                           "کل گاہک: {$totalCustomers}\n" .
                           "کل مصنوعات: {$totalProducts}\n" .
                           "کل فروخت: Rs. " . number_format($totalSales, 2) . "\n" .
                           "کل ملازمین: {$totalEmployees}"
            ]
        ];
    }

    /**
     * Handle gold rate
     */
    private function handleGoldRate($message)
    {
        $rate = GoldRate::latest()->first();

        if (!$rate) {
            return [
                'status' => 'success',
                'data' => ['answer' => 'سونے کی قیمت کی معلومات دستیاب نہیں۔']
            ];
        }

        return [
            'status' => 'success',
            'data' => [
                'answer' => "سونے کی موجودہ قیمت:\n\n" .
                           "22K: Rs. " . number_format($rate->rate_22k, 2) . "\n" .
                           "24K: Rs. " . number_format($rate->rate_24k, 2) . "\n" .
                           "18K: Rs. " . number_format($rate->rate_18k, 2)
            ]
        ];
    }

    /**
     * Handle general query with Groq
     */
    private function handleGeneralQuery($message, $language = 'ur')
    {
        try {
            $response = Http::timeout(10)->post("{$this->groqBaseUrl}/chat/completions", [
                'model' => 'mixtral-8x7b-32768',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'آپ ایک مفید جوہری کاروبار کے AI معاون ہیں۔ اردو اور انگریزی میں جوابات دیں۔ مختصر اور عملی جوابات دیں۔'
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ], [
                'Authorization' => "Bearer {$this->groqApiKey}"
            ]);

            $data = $response->json();
            
            if (isset($data['choices'][0]['message']['content'])) {
                return [
                    'status' => 'success',
                    'data' => [
                        'answer' => $data['choices'][0]['message']['content']
                    ]
                ];
            }
        } catch (\Exception $e) {
            Log::error('Groq API error: ' . $e->getMessage());
        }

        return [
            'status' => 'success',
            'data' => [
                'answer' => "میں آپ کے سوال کو سمجھ گیا: {$message}\n\nبراہ کرم مزید تفصیل دیں۔"
            ]
        ];
    }

    /**
     * Process voice input
     */
    public function processVoiceInput($audioFile, $language = 'ur')
    {
        return $this->voiceService->transcribeAudio($audioFile, $this->getGoogleLanguageCode($language));
    }

    /**
     * Generate voice response
     */
    public function generateVoiceResponse($text, $language = 'ur')
    {
        return $this->voiceService->synthesizeSpeech($text, $this->getGoogleLanguageCode($language));
    }

    /**
     * Get Google language code
     */
    private function getGoogleLanguageCode($language)
    {
        $codes = [
            'ur' => 'ur-PK',
            'en' => 'en-US',
            'hi' => 'hi-IN',
            'pa' => 'pa-IN',
        ];

        return $codes[$language] ?? 'ur-PK';
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\InventoryProduct;
use App\Models\Customer;
use App\Models\PosSale;
use App\Models\GoldRate;

class AdvancedAIService
{
    private $apiKey;
    private $apiProvider;
    private $baseUrl;

    public function __construct()
    {
        $this->apiProvider = env('AI_PROVIDER', 'gemini'); // gemini or openai
        $this->apiKey = env('GEMINI_API_KEY') ?: env('OPENAI_API_KEY');
        
        if ($this->apiProvider === 'openai') {
            $this->baseUrl = 'https://api.openai.com/v1';
        } else {
            $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
        }
    }

    /**
     * Process message with real AI
     */
    public function processMessage($message, $language = 'ur')
    {
        try {
            // Get context from database
            $context = $this->buildContext();
            
            // Create system prompt
            $systemPrompt = $this->buildSystemPrompt($language, $context);
            
            // Call AI API
            $response = $this->callAI($message, $systemPrompt, $language);
            
            // Parse and execute commands from AI response
            $result = $this->parseAndExecuteAIResponse($response, $message);
            
            return [
                'status' => 'success',
                'answer' => $result['answer'],
                'action_taken' => $result['action_taken'],
                'data' => $result['data'] ?? null
            ];
        } catch (\Exception $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'answer' => 'معافی چاہتا ہوں، کچھ خرابی ہوئی۔',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Build context from database
     */
    private function buildContext()
    {
        $user = auth()->user();
        $branchId = $user?->branch_id ?? 1;

        return [
            'total_employees' => Employee::where('branch_id', $branchId)->count(),
            'total_products' => InventoryProduct::where('branch_id', $branchId)->count(),
            'total_customers' => Customer::where('branch_id', $branchId)->count(),
            'total_sales' => PosSale::where('branch_id', $branchId)->sum('total'),
            'recent_employees' => Employee::where('branch_id', $branchId)->latest()->limit(5)->get(['first_name', 'last_name', 'base_salary']),
            'recent_products' => InventoryProduct::where('branch_id', $branchId)->latest()->limit(5)->get(['name', 'selling_price', 'current_stock']),
            'gold_rate' => GoldRate::latest()->first(['rate_22k', 'rate_24k', 'rate_18k', 'silver_rate']),
        ];
    }

    /**
     * Build system prompt for AI
     */
    private function buildSystemPrompt($language, $context)
    {
        $prompt = "آپ ایک جواہرات کی دکان کے لیے ایک ذہین AI assistant ہیں۔\n\n";
        
        $prompt .= "موجودہ ڈیٹا:\n";
        $prompt .= "- کل ملازمین: {$context['total_employees']}\n";
        $prompt .= "- کل پروڈکٹس: {$context['total_products']}\n";
        $prompt .= "- کل کسٹمرز: {$context['total_customers']}\n";
        $prompt .= "- کل فروخت: {$context['total_sales']}\n";
        
        if ($context['gold_rate']) {
            $prompt .= "\nسونے کی موجودہ قیمت:\n";
            $prompt .= "- 22K: {$context['gold_rate']->rate_22k} روپے\n";
            $prompt .= "- 24K: {$context['gold_rate']->rate_24k} روپے\n";
            $prompt .= "- 18K: {$context['gold_rate']->rate_18k} روپے\n";
        }
        
        $prompt .= "\nآپ یہ کام کر سکتے ہیں:\n";
        $prompt .= "1. ملازمین شامل/حذف/اپڈیٹ کریں\n";
        $prompt .= "2. پروڈکٹس شامل/حذف/اپڈیٹ کریں\n";
        $prompt .= "3. کسٹمرز شامل/حذف/اپڈیٹ کریں\n";
        $prompt .= "4. سیلز بنائیں اور ٹریک کریں\n";
        $prompt .= "5. تجزیہ اور رپورٹس دیں\n";
        $prompt .= "6. سونے کی قیمت بتائیں\n\n";
        
        $prompt .= "جب صارف کوئی کمانڈ دے تو:\n";
        $prompt .= "1. سب سے پہلے اردو میں جواب دیں\n";
        $prompt .= "2. اگر ڈیٹا بیس میں تبدیلی ہو تو JSON format میں یہ شامل کریں:\n";
        $prompt .= "   {\"action\": \"add_employee\", \"data\": {\"name\": \"...\", \"salary\": ...}}\n";
        $prompt .= "3. ہمیشہ مفید اور دوستانہ جواب دیں\n";
        $prompt .= "4. اگر معلومات نہ ہو تو صارف سے پوچھیں\n";
        
        return $prompt;
    }

    /**
     * Call AI API (Gemini or OpenAI) with fallback to Python agent
     */
    private function callAI($message, $systemPrompt, $language)
    {
        try {
            if ($this->apiProvider === 'openai') {
                return $this->callOpenAI($message, $systemPrompt);
            } else {
                return $this->callGemini($message, $systemPrompt);
            }
        } catch (\Exception $e) {
            Log::warning('Primary AI API failed, using Python agent: ' . $e->getMessage());
            return $this->callPythonAgent($message, $language);
        }
    }

    /**
     * Call OpenAI API with timeout
     */
    private function callOpenAI($message, $systemPrompt)
    {
        $response = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        throw new \Exception('OpenAI API Error: ' . $response->body());
    }

    /**
     * Call Google Gemini API with timeout
     */
    private function callGemini($message, $systemPrompt)
    {
        $response = Http::timeout(10)->post($this->baseUrl . '/gemini-pro:generateContent', [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $systemPrompt . "\n\nصارف: " . $message]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 1000
            ]
        ], [
            'key' => $this->apiKey
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new \Exception('Gemini API Error: ' . $response->body());
    }

    /**
     * Call Python AI Agent as fallback
     */
    private function callPythonAgent($message, $language)
    {
        $pythonAgentUrl = env('PYTHON_AGENT_URL', 'http://localhost:8001');
        
        try {
            $response = Http::timeout(5)->post($pythonAgentUrl . '/api/chat/message', [
                'message' => $message,
                'language' => $language
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['message'] ?? 'Message processed';
            }
        } catch (\Exception $e) {
            Log::error('Python agent error: ' . $e->getMessage());
        }

        return "✓ Message received: {$message}";
    }

    /**
     * Parse and execute AI response
     */
    private function parseAndExecuteAIResponse($aiResponse, $originalMessage)
    {
        $user = auth()->user();
        $branchId = $user?->branch_id ?? 1;
        $userId = auth()->id();

        // Extract JSON action if present
        $jsonPattern = '/\{.*?"action"\s*:\s*".*?"(,|\})/s';
        $hasAction = preg_match($jsonPattern, $aiResponse, $matches);

        $actionTaken = false;
        $data = null;

        // Strict allowed action list (foundation for intelligence)
        $allowedActions = [
            'add_employee',
            'add_product',
            'add_customer',
            'create_sale',
            'update_employee',
            'delete_employee',
        ];

        if ($hasAction) {
            try {
                $action = json_decode($matches[0], true);

                if (is_array($action) && isset($action['action'], $action['data'])) {
                    $actionName = $action['action'];
                    $payload = $action['data'];

                    if (in_array($actionName, $allowedActions, true)) {
                        // DB consistency
                        DB::beginTransaction();

                        switch ($actionName) {
                            case 'add_employee':
                                $data = $this->addEmployee($payload, $branchId);
                                $actionTaken = true;
                                break;

                            case 'add_product':
                                $data = $this->addProduct($payload, $branchId, $userId);
                                $actionTaken = true;
                                break;

                            case 'add_customer':
                                $data = $this->addCustomer($payload, $branchId);
                                $actionTaken = true;
                                break;

                            case 'create_sale':
                                $data = $this->createSale($payload, $branchId, $userId);
                                $actionTaken = true;
                                break;

                            case 'update_employee':
                                $data = $this->updateEmployee($payload);
                                $actionTaken = true;
                                break;

                            case 'delete_employee':
                                $data = $this->deleteEmployee($payload);
                                $actionTaken = true;
                                break;
                        }

                        DB::commit();
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Action execution error: ' . $e->getMessage());
            }
        }

        // Extract answer (remove JSON from response)
        $answer = preg_replace('/\{.*?"action".*?\}/s', '', $aiResponse);
        $answer = trim($answer);

        return [
            'answer' => $answer ?: 'کمانڈ پروسیس ہو رہی ہے...',
            'action_taken' => $actionTaken,
            'data' => $data,
        ];
    }

    /**
     * Add employee
     */
    private function addEmployee($data, $branchId)
    {
        $employee = Employee::create([
            'employee_code' => 'EMP' . str_pad(Employee::count() + 1, 4, '0', STR_PAD_LEFT),
            'first_name' => $data['name'] ?? $data['first_name'] ?? 'Employee',
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'] ?? 'emp' . time() . '@company.com',
            'phone' => $data['phone'] ?? '',
            'base_salary' => $data['salary'] ?? $data['base_salary'] ?? 0,
            'department' => $data['department'] ?? 'General',
            'designation' => $data['designation'] ?? 'Staff',
            'joining_date' => now()->toDateString(),
            'status' => 'active',
            'branch_id' => $branchId,
        ]);

        return $employee;
    }

    /**
     * Add product
     */
    private function addProduct($data, $branchId, $userId)
    {
        $product = InventoryProduct::create([
            'sku' => 'SKU' . str_pad(InventoryProduct::count() + 1, 5, '0', STR_PAD_LEFT),
            'name' => $data['name'] ?? 'Product',
            'description' => $data['description'] ?? '',
            'category_id' => $data['category_id'] ?? 1,
            'purity_id' => $data['purity_id'] ?? 1,
            'weight' => $data['weight'] ?? 0,
            'cost_price' => $data['cost_price'] ?? 0,
            'selling_price' => $data['selling_price'] ?? $data['price'] ?? 0,
            'current_stock' => $data['stock'] ?? 0,
            'status' => 'active',
            'branch_id' => $branchId,
            'created_by' => $userId,
        ]);

        return $product;
    }

    /**
     * Add customer
     */
    private function addCustomer($data, $branchId)
    {
        $name = $data['name'] ?? 'Customer';
        $names = explode(' ', $name);
        
        $customer = Customer::create([
            'customer_code' => 'CUST' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT),
            'name' => $name,
            'first_name' => $names[0],
            'last_name' => implode(' ', array_slice($names, 1)),
            'email' => $data['email'] ?? 'cust' . time() . '@customer.com',
            'phone' => $data['phone'] ?? '',
            'mobile' => $data['phone'] ?? '',
            'customer_type' => 'individual',
            'branch_id' => $branchId,
            'is_active' => true,
        ]);

        return $customer;
    }

    /**
     * Create sale
     */
    private function createSale($data, $branchId, $userId)
    {
        $sale = PosSale::create([
            'branch_id' => $branchId,
            'pos_customer_id' => $data['customer_id'] ?? null,
            'created_by' => $userId,
            'sale_time' => now(),
            'subtotal' => $data['amount'] ?? $data['total'] ?? 0,
            'total' => $data['amount'] ?? $data['total'] ?? 0,
            'status' => 'completed',
            'payment_status' => 'paid',
            'stock_moved' => true,
        ]);

        return $sale;
    }

    /**
     * Update employee
     */
    private function updateEmployee($data)
    {
        $employee = Employee::where('first_name', 'like', '%' . ($data['name'] ?? '') . '%')
            ->orWhere('employee_code', $data['code'] ?? '')
            ->first();

        if ($employee) {
            $employee->update($data);
            return $employee;
        }

        return null;
    }

    /**
     * Delete employee
     */
    private function deleteEmployee($data)
    {
        $employee = Employee::where('first_name', 'like', '%' . ($data['name'] ?? '') . '%')
            ->orWhere('employee_code', $data['code'] ?? '')
            ->first();

        if ($employee) {
            $employee->delete();
            return ['deleted' => true, 'name' => $employee->first_name];
        }

        return null;
    }
}

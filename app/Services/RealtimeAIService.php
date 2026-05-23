<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\InventoryProduct;
use App\Models\Customer;
use App\Models\PosSale;
use App\Models\GoldRate;

class RealtimeAIService
{
    private $apiKey;
    private $apiProvider;
    private $baseUrl;

    public function __construct()
    {
        $this->apiProvider = env('AI_PROVIDER', 'openai');
        $this->apiKey = env('OPENAI_API_KEY') ?: env('GEMINI_API_KEY');
        $this->baseUrl = 'https://api.openai.com/v1';
    }

    /**
     * Stream AI response in real-time
     */
    public function streamMessage($message, $language = 'ur', $callback = null)
    {
        try {
            $context = $this->buildContext();
            $systemPrompt = $this->buildSystemPrompt($language, $context);
            
            if ($this->apiProvider === 'openai') {
                return $this->streamOpenAI($message, $systemPrompt, $callback);
            } else {
                return $this->streamGemini($message, $systemPrompt, $callback);
            }
        } catch (\Exception $e) {
            Log::error('Realtime AI Error: ' . $e->getMessage());
            if ($callback) {
                $callback('error', 'معافی چاہتا ہوں، کچھ خرابی ہوئی۔');
            }
        }
    }

    /**
     * Stream from OpenAI with real-time chunks
     */
    private function streamOpenAI($message, $systemPrompt, $callback)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/chat/completions', [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1000,
            'stream' => true
        ], function ($response) use ($callback) {
            $buffer = '';
            
            foreach ($response->getBody() as $chunk) {
                $buffer .= $chunk;
                
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);
                    
                    if (strpos($line, 'data: ') === 0) {
                        $data = substr($line, 6);
                        
                        if ($data === '[DONE]') {
                            if ($callback) {
                                $callback('done', '');
                            }
                            continue;
                        }
                        
                        $json = json_decode($data, true);
                        if (isset($json['choices'][0]['delta']['content'])) {
                            $content = $json['choices'][0]['delta']['content'];
                            if ($callback) {
                                $callback('chunk', $content);
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * Stream from Gemini with real-time chunks
     */
    private function streamGemini($message, $systemPrompt, $callback)
    {
        $response = Http::post($this->baseUrl . '/gemini-pro:streamGenerateContent', [
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
            
            if (isset($data[0]['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $data[0]['candidates'][0]['content']['parts'][0]['text'];
                
                // Stream character by character for real-time effect
                for ($i = 0; $i < strlen($text); $i++) {
                    if ($callback) {
                        $callback('chunk', $text[$i]);
                    }
                    usleep(10000); // 10ms delay for smooth streaming
                }
                
                if ($callback) {
                    $callback('done', '');
                }
            }
        }
    }

    /**
     * Process and execute AI commands
     */
    public function executeAICommand($aiResponse, $originalMessage)
    {
        $branchId = auth()->user()->branch_id ?? 1;
        $userId = auth()->id();
        
        $jsonPattern = '/\{.*?"action".*?\}/s';
        $hasAction = preg_match($jsonPattern, $aiResponse, $matches);
        
        $actionTaken = false;
        $data = null;
        
        if ($hasAction) {
            try {
                $action = json_decode($matches[0], true);
                
                switch ($action['action']) {
                    case 'add_employee':
                        $data = $this->addEmployee($action['data'], $branchId);
                        $actionTaken = true;
                        break;
                    
                    case 'add_product':
                        $data = $this->addProduct($action['data'], $branchId, $userId);
                        $actionTaken = true;
                        break;
                    
                    case 'add_customer':
                        $data = $this->addCustomer($action['data'], $branchId);
                        $actionTaken = true;
                        break;
                    
                    case 'create_sale':
                        $data = $this->createSale($action['data'], $branchId, $userId);
                        $actionTaken = true;
                        break;
                    
                    case 'update_employee':
                        $data = $this->updateEmployee($action['data']);
                        $actionTaken = true;
                        break;
                    
                    case 'delete_employee':
                        $data = $this->deleteEmployee($action['data']);
                        $actionTaken = true;
                        break;
                }
            } catch (\Exception $e) {
                Log::error('Action execution error: ' . $e->getMessage());
            }
        }
        
        return [
            'action_taken' => $actionTaken,
            'data' => $data
        ];
    }

    /**
     * Build context from database
     */
    private function buildContext()
    {
        $branchId = auth()->user()->branch_id ?? 1;
        
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
     * Build system prompt
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

    // Database operations
    private function addEmployee($data, $branchId)
    {
        return Employee::create([
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
    }

    private function addProduct($data, $branchId, $userId)
    {
        return InventoryProduct::create([
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
    }

    private function addCustomer($data, $branchId)
    {
        $name = $data['name'] ?? 'Customer';
        $names = explode(' ', $name);
        
        return Customer::create([
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
    }

    private function createSale($data, $branchId, $userId)
    {
        return PosSale::create([
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
    }

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

    /**
     * Process message with AI and return response
     */
    public function processWithAI($message, $language = 'ur')
    {
        try {
            $context = $this->buildContext();
            $systemPrompt = $this->buildSystemPrompt($language, $context);
            
            if ($this->apiProvider === 'openai') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json'
                ])->post($this->baseUrl . '/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $message]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1000
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['choices'][0]['message']['content'] ?? 'No response';
                }
            } else {
                $response = Http::post($this->baseUrl . '/gemini-pro:generateContent', [
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
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        return $data['candidates'][0]['content']['parts'][0]['text'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Process AI Error: ' . $e->getMessage());
        }

        return null;
    }
}

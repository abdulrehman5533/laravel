<?php

namespace App\Services;

use App\Models\InventoryProduct;
use App\Models\Customer;
use App\Models\PosSale;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\GoldRate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdvancedAIAgentService
{
    private $groqApiKey;
    private $groqBaseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->groqApiKey = env('GROQ_API_KEY', 'gsk_test_key');
    }

    /**
     * Process chat message with AI
     */
    public function processMessage($message, $language = 'ur')
    {
        try {
            // Detect intent from message
            $intent = $this->detectIntent($message);
            
            // Handle specific intents
            if ($intent['type'] !== 'general') {
                $result = $this->handleSpecificIntent($intent, $message);
                if ($result) {
                    return $result;
                }
            }

            // For general queries, use Groq API
            return $this->handleGeneralQuery($message, $language);
        } catch (\Exception $e) {
            Log::error('AI Agent error: ' . $e->getMessage());
            return [
                'status' => 'success',
                'data' => [
                    'answer' => 'معافی چاہتا ہوں، کچھ خرابی ہوئی۔ براہ کرم دوبارہ کوشش کریں۔'
                ]
            ];
        }
    }

    /**
     * Detect user intent
     */
    private function detectIntent($message)
    {
        $msg = strtolower($message);

        // Stock/Inventory
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
     * Handle specific intents
     */
    private function handleSpecificIntent($intent, $message)
    {
        switch ($intent['type']) {
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
     * Handle inventory queries and updates
     */
    private function handleInventory($message)
    {
        // Extract product name
        preg_match('/(?:product|item|stock|gold|silver|jewelry|jewellery)\s+(?:named|called|is|ka|ke)?\s*([a-zA-Z\s]+?)(?:\s+(?:quantity|units|pieces|qty|stock|aaya|received)|$)/i', $message, $matches);
        $productName = $matches[1] ?? null;

        // Extract quantity
        preg_match('/(\d+)\s*(?:units?|pieces?|qty|quantity|items?|aaya|received)/i', $message, $qtyMatches);
        $quantity = $qtyMatches[1] ?? null;

        if (!$productName) {
            // Show all products
            $products = InventoryProduct::select('name', 'current_stock', 'selling_price')->limit(10)->get();
            $list = $products->map(fn($p) => "{$p->name}: {$p->current_stock} units")->implode("\n");
            
            return [
                'status' => 'success',
                'data' => [
                    'answer' => "موجودہ اسٹاک:\n\n{$list}"
                ]
            ];
        }

        $product = InventoryProduct::where('name', 'like', "%{$productName}%")->first();

        if (!$product) {
            return [
                'status' => 'success',
                'data' => [
                    'answer' => "'{$productName}' نہیں ملا۔ براہ کرم صحیح نام درج کریں۔"
                ]
            ];
        }

        // If quantity provided, update stock
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

        // Just show stock
        return [
            'status' => 'success',
            'data' => [
                'answer' => "{$product->name}\nموجودہ اسٹاک: {$product->current_stock} units\nقیمت: Rs. " . number_format($product->selling_price, 2)
            ]
        ];
    }

    /**
     * Handle sales queries
     */
    private function handleSales($message)
    {
        // Get today's sales
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
     * Handle employee queries
     */
    private function handleEmployee($message)
    {
        $employees = Employee::select('name', 'position', 'salary')->limit(5)->get();
        
        if ($employees->isEmpty()) {
            return [
                'status' => 'success',
                'data' => [
                    'answer' => 'کوئی ملازم ریکارڈ نہیں ملا۔'
                ]
            ];
        }

        $list = $employees->map(fn($e) => "{$e->name} ({$e->position}) - Rs. " . number_format($e->salary, 2))->implode("\n");

        return [
            'status' => 'success',
            'data' => [
                'answer' => "ملازمین:\n\n{$list}"
            ]
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
     * Handle gold rate queries
     */
    private function handleGoldRate($message)
    {
        $rate = GoldRate::latest()->first();

        if (!$rate) {
            return [
                'status' => 'success',
                'data' => [
                    'answer' => 'سونے کی قیمت کی معلومات دستیاب نہیں۔'
                ]
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
     * Handle general queries with Groq API
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

        // Fallback response
        return [
            'status' => 'success',
            'data' => [
                'answer' => "میں آپ کے سوال کو سمجھ گیا: {$message}\n\nبراہ کرم مزید تفصیل دیں یا دوبارہ کوشش کریں۔"
            ]
        ];
    }

    /**
     * Process voice input
     */
    public function processVoiceInput($audioFile, $language = 'ur')
    {
        try {
            // For now, return a placeholder
            // In production, use Google Speech-to-Text or AssemblyAI
            return [
                'status' => 'success',
                'data' => [
                    'transcript' => 'آپ کی آواز سنی گئی',
                    'language' => $language
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Voice processing error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'آواز کی پروسیسنگ میں خرابی'
            ];
        }
    }

    /**
     * Generate voice response
     */
    public function generateVoiceResponse($text, $language = 'ur')
    {
        try {
            // For now, return a placeholder
            // In production, use Google Text-to-Speech
            return [
                'status' => 'success',
                'data' => [
                    'audio_file' => 'response.mp3',
                    'url' => '/storage/voice/response.mp3'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Voice generation error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'آواز کی تیاری میں خرابی'
            ];
        }
    }
}

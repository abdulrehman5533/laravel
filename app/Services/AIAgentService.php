<?php

namespace App\Services;

use App\Models\InventoryProduct;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AIAgentService
{
    private $apiKey;
    private $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY');
    }

    /**
     * Process voice input and convert to text
     */
    public function processVoiceInput($audioFile, $language = 'ur-PK')
    {
        try {
            // Using Google Speech-to-Text (Free tier available)
            $transcript = $this->transcribeAudio($audioFile, $language);
            return $this->processUserInput($transcript);
        } catch (\Exception $e) {
            Log::error('Voice processing error: ' . $e->getMessage());
            return ['error' => 'Voice processing failed'];
        }
    }

    /**
     * Process chat input
     */
    public function processChatInput($userMessage)
    {
        return $this->processUserInput($userMessage);
    }

    /**
     * Main processing logic
     */
    private function processUserInput($userMessage)
    {
        try {
            // Detect intent
            $intent = $this->detectIntent($userMessage);

            $response = match ($intent['type']) {
                'stock_update' => $this->handleStockUpdate($intent, $userMessage),
                'employee_add' => $this->handleEmployeeAdd($intent, $userMessage),
                'salary_calculation' => $this->handleSalaryCalculation($intent, $userMessage),
                'inventory_check' => $this->handleInventoryCheck($intent, $userMessage),
                'employee_info' => $this->handleEmployeeInfo($intent, $userMessage),
                'general_query' => $this->handleGeneralQuery($userMessage),
                default => ['error' => 'Intent not recognized']
            };

            return $response;
        } catch (\Exception $e) {
            Log::error('AI Agent error: ' . $e->getMessage());
            return ['error' => 'Processing failed: ' . $e->getMessage()];
        }
    }

    /**
     * Detect user intent using keyword matching
     */
    private function detectIntent($message)
    {
        $message = strtolower($message);

        // Stock update keywords
        if (preg_match('/stock|inventory|aaya|received|add|update|quantity/i', $message)) {
            return ['type' => 'stock_update'];
        }

        // Employee add keywords
        if (preg_match('/employee|naya|new|hire|join|add staff|worker/i', $message)) {
            return ['type' => 'employee_add'];
        }

        // Salary keywords
        if (preg_match('/salary|wage|pay|compensation|increment|bonus/i', $message)) {
            return ['type' => 'salary_calculation'];
        }

        // Inventory check
        if (preg_match('/check|kitna|how much|stock level|available|inventory/i', $message)) {
            return ['type' => 'inventory_check'];
        }

        // Employee info
        if (preg_match('/employee|worker|staff|person|details|info/i', $message)) {
            return ['type' => 'employee_info'];
        }

        return ['type' => 'general_query'];
    }

    /**
     * Handle stock updates
     */
    private function handleStockUpdate($intent, $message)
    {
        try {
            // Extract product name and quantity using AI
            $extracted = $this->extractEntities($message, ['product_name', 'quantity', 'warehouse']);

            if (!$extracted['product_name'] || !$extracted['quantity']) {
                return [
                    'status' => 'clarification_needed',
                    'message' => 'Kaunsi product aur kitni quantity? (Which product and how much?)',
                    'fields' => ['product_name', 'quantity']
                ];
            }

            // Find product
            $product = InventoryProduct::where('name', 'like', '%' . $extracted['product_name'] . '%')
                ->first();

            if (!$product) {
                return [
                    'status' => 'error',
                    'message' => "Product '{$extracted['product_name']}' nahi mila (not found)"
                ];
            }

            // Update stock
            $quantity = (int)$extracted['quantity'];
            $product->quantity += $quantity;
            $product->save();

            // Log movement
            StockMovement::create([
                'inventory_product_id' => $product->id,
                'movement_type' => 'inbound',
                'quantity' => $quantity,
                'reference_type' => 'voice_input',
                'notes' => "Voice input: $message"
            ]);

            return [
                'status' => 'success',
                'message' => "✓ Stock update ho gaya! {$product->name} - {$quantity} units add kiye gaye. Total: {$product->quantity}",
                'data' => [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_added' => $quantity,
                    'total_quantity' => $product->quantity
                ]
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Stock update fail: ' . $e->getMessage()];
        }
    }

    /**
     * Handle employee addition
     */
    private function handleEmployeeAdd($intent, $message)
    {
        try {
            $extracted = $this->extractEntities($message, [
                'employee_name',
                'position',
                'salary',
                'phone',
                'email'
            ]);

            if (!$extracted['employee_name'] || !$extracted['position']) {
                return [
                    'status' => 'clarification_needed',
                    'message' => 'Employee ka naam aur position batao (Name and position needed)',
                    'fields' => ['employee_name', 'position', 'salary', 'phone']
                ];
            }

            // Create employee
            $employee = Employee::create([
                'name' => $extracted['employee_name'],
                'position' => $extracted['position'],
                'email' => $extracted['email'] ?? 'employee_' . time() . '@company.com',
                'phone' => $extracted['phone'] ?? null,
                'salary' => (float)($extracted['salary'] ?? 0),
                'status' => 'active',
                'hire_date' => now()
            ]);

            return [
                'status' => 'success',
                'message' => "✓ Employee add ho gaya! {$employee->name} ({$employee->position}) - ID: {$employee->id}",
                'data' => [
                    'employee_id' => $employee->id,
                    'name' => $employee->name,
                    'position' => $employee->position,
                    'salary' => $employee->salary
                ]
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Employee add fail: ' . $e->getMessage()];
        }
    }

    /**
     * Handle salary calculations
     */
    private function handleSalaryCalculation($intent, $message)
    {
        try {
            $extracted = $this->extractEntities($message, ['employee_name', 'salary_amount', 'bonus']);

            if (!$extracted['employee_name']) {
                return [
                    'status' => 'clarification_needed',
                    'message' => 'Kis employee ka salary? (Which employee?)',
                    'fields' => ['employee_name']
                ];
            }

            $employee = Employee::where('name', 'like', '%' . $extracted['employee_name'] . '%')->first();

            if (!$employee) {
                return ['status' => 'error', 'message' => "Employee '{$extracted['employee_name']}' nahi mila"];
            }

            $baseSalary = (float)($extracted['salary_amount'] ?? $employee->salary);
            $bonus = (float)($extracted['bonus'] ?? 0);
            $totalSalary = $baseSalary + $bonus;

            // Calculate deductions (example: 5% tax)
            $tax = $totalSalary * 0.05;
            $netSalary = $totalSalary - $tax;

            // Create payroll record
            $payroll = Payroll::create([
                'employee_id' => $employee->id,
                'base_salary' => $baseSalary,
                'bonus' => $bonus,
                'deductions' => $tax,
                'net_salary' => $netSalary,
                'month' => now()->format('Y-m'),
                'status' => 'calculated'
            ]);

            return [
                'status' => 'success',
                'message' => "✓ Salary calculated! {$employee->name} - Net: Rs. " . number_format($netSalary, 2),
                'data' => [
                    'employee_name' => $employee->name,
                    'base_salary' => $baseSalary,
                    'bonus' => $bonus,
                    'tax' => $tax,
                    'net_salary' => $netSalary,
                    'payroll_id' => $payroll->id
                ]
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Salary calculation fail: ' . $e->getMessage()];
        }
    }

    /**
     * Handle inventory checks
     */
    private function handleInventoryCheck($intent, $message)
    {
        try {
            $extracted = $this->extractEntities($message, ['product_name']);

            if (!$extracted['product_name']) {
                $allProducts = InventoryProduct::select('name', 'quantity')->limit(5)->get();
                return [
                    'status' => 'success',
                    'message' => 'Current inventory:',
                    'data' => $allProducts
                ];
            }

            $product = InventoryProduct::where('name', 'like', '%' . $extracted['product_name'] . '%')->first();

            if (!$product) {
                return ['status' => 'error', 'message' => "Product '{$extracted['product_name']}' nahi mila"];
            }

            return [
                'status' => 'success',
                'message' => "{$product->name} - Stock: {$product->quantity} units",
                'data' => [
                    'product_name' => $product->name,
                    'quantity' => $product->quantity,
                    'price' => $product->price ?? 0
                ]
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Inventory check fail: ' . $e->getMessage()];
        }
    }

    /**
     * Handle employee info queries
     */
    private function handleEmployeeInfo($intent, $message)
    {
        try {
            $extracted = $this->extractEntities($message, ['employee_name']);

            if (!$extracted['employee_name']) {
                $employees = Employee::select('name', 'position', 'salary')->limit(5)->get();
                return [
                    'status' => 'success',
                    'message' => 'Employees:',
                    'data' => $employees
                ];
            }

            $employee = Employee::where('name', 'like', '%' . $extracted['employee_name'] . '%')->first();

            if (!$employee) {
                return ['status' => 'error', 'message' => "Employee '{$extracted['employee_name']}' nahi mila"];
            }

            return [
                'status' => 'success',
                'message' => "Employee: {$employee->name}",
                'data' => [
                    'name' => $employee->name,
                    'position' => $employee->position,
                    'salary' => $employee->salary,
                    'phone' => $employee->phone,
                    'hire_date' => $employee->hire_date
                ]
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Employee info fail: ' . $e->getMessage()];
        }
    }

    /**
     * Handle general queries with AI
     */
    private function handleGeneralQuery($message)
    {
        try {
            $response = $this->callGroqAPI($message);
            return [
                'status' => 'success',
                'message' => $response,
                'type' => 'general'
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Query processing failed'];
        }
    }

    /**
     * Extract entities from message using pattern matching
     */
    private function extractEntities($message, $fields)
    {
        $extracted = [];

        foreach ($fields as $field) {
            $extracted[$field] = $this->extractField($message, $field);
        }

        return $extracted;
    }

    /**
     * Extract specific field from message
     */
    private function extractField($message, $field)
    {
        switch ($field) {
            case 'product_name':
                preg_match('/(?:product|item|stock|gold|silver|jewelry)\s+(?:named|called|is|ka|ke)?\s*([a-zA-Z\s]+?)(?:\s+(?:quantity|units|pieces|qty)|$)/i', $message, $matches);
                return $matches[1] ?? null;

            case 'quantity':
                preg_match('/(\d+)\s*(?:units?|pieces?|qty|quantity|items?)/i', $message, $matches);
                return $matches[1] ?? null;

            case 'employee_name':
                preg_match('/(?:employee|worker|staff|person|named|called)\s+(?:is|ka|ke)?\s*([a-zA-Z\s]+?)(?:\s+(?:position|role|as|salary)|$)/i', $message, $matches);
                return $matches[1] ?? null;

            case 'position':
                preg_match('/(?:position|role|as|job)\s+(?:is|ka|ke)?\s*([a-zA-Z\s]+?)(?:\s+(?:salary|with|earning)|$)/i', $message, $matches);
                return $matches[1] ?? null;

            case 'salary':
            case 'salary_amount':
                preg_match('/(?:salary|wage|pay|earning)\s+(?:is|of|ka|ke)?\s*(?:rs\.?|rupees?)?\s*(\d+(?:,\d{3})*(?:\.\d{2})?)/i', $message, $matches);
                return str_replace(',', '', $matches[1] ?? null);

            case 'bonus':
                preg_match('/(?:bonus|extra|additional)\s+(?:is|of|ka|ke)?\s*(?:rs\.?|rupees?)?\s*(\d+(?:,\d{3})*(?:\.\d{2})?)/i', $message, $matches);
                return str_replace(',', '', $matches[1] ?? null);

            case 'phone':
                preg_match('/(?:phone|contact|number|mobile)\s+(?:is|ka|ke)?\s*(\d{10,})/i', $message, $matches);
                return $matches[1] ?? null;

            case 'email':
                preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $message, $matches);
                return $matches[1] ?? null;

            case 'warehouse':
                preg_match('/(?:warehouse|location|branch)\s+(?:is|ka|ke)?\s*([a-zA-Z\s]+?)(?:\s+|$)/i', $message, $matches);
                return $matches[1] ?? null;

            default:
                return null;
        }
    }

    /**
     * Transcribe audio to text using Google Speech-to-Text
     */
    private function transcribeAudio($audioFile, $language = 'ur-PK')
    {
        try {
            // Using Google Cloud Speech-to-Text (Free tier: 60 minutes/month)
            // Alternative: Use AssemblyAI (free tier available)
            
            $client = new \Google\Cloud\Speech\V1\SpeechClient([
                'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS')
            ]);

            $audio = file_get_contents($audioFile);
            $audio = new \Google\Cloud\Speech\V1\RecognitionAudio();
            $audio->setContent($audio);

            $config = new \Google\Cloud\Speech\V1\RecognitionConfig();
            $config->setEncoding(\Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding::LINEAR16);
            $config->setSampleRateHertz(16000);
            $config->setLanguageCode($language);

            $response = $client->recognize($config, $audio);
            $results = $response->getResults();

            if (count($results) > 0) {
                $alternatives = $results[0]->getAlternatives();
                if (count($alternatives) > 0) {
                    return $alternatives[0]->getTranscript();
                }
            }

            return '';
        } catch (\Exception $e) {
            Log::error('Transcription error: ' . $e->getMessage());
            // Fallback to AssemblyAI if Google fails
            return $this->transcribeWithAssemblyAI($audioFile, $language);
        }
    }

    /**
     * Fallback transcription using AssemblyAI
     */
    private function transcribeWithAssemblyAI($audioFile, $language = 'ur')
    {
        try {
            $apiKey = env('ASSEMBLYAI_API_KEY');
            
            // Upload file
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.assemblyai.com/v2/upload');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $apiKey]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($audioFile));
            
            $uploadResponse = json_decode(curl_exec($ch), true);
            curl_close($ch);

            if (!isset($uploadResponse['upload_url'])) {
                throw new \Exception('Upload failed');
            }

            // Transcribe
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.assemblyai.com/v2/transcript');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: ' . $apiKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'audio_url' => $uploadResponse['upload_url'],
                'language_code' => $language
            ]));

            $transcriptResponse = json_decode(curl_exec($ch), true);
            curl_close($ch);

            return $transcriptResponse['text'] ?? '';
        } catch (\Exception $e) {
            Log::error('AssemblyAI transcription error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Call Groq API for general queries
     */
    private function callGroqAPI($message)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                'model' => 'mixtral-8x7b-32768',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful jewellery management system assistant. Answer in Urdu/English mix. Keep responses short and actionable.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ],
                'max_tokens' => 500
            ]));

            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);

            return $response['choices'][0]['message']['content'] ?? 'Unable to process query';
        } catch (\Exception $e) {
            Log::error('Groq API error: ' . $e->getMessage());
            return 'API error occurred';
        }
    }

    /**
     * Generate voice response
     */
    public function generateVoiceResponse($text, $language = 'ur-PK')
    {
        try {
            // Using Google Text-to-Speech (Free tier available)
            $client = new \Google\Cloud\TextToSpeech\V1\TextToSpeechClient([
                'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS')
            ]);

            $input = new \Google\Cloud\TextToSpeech\V1\SynthesisInput();
            $input->setText($text);

            $voice = new \Google\Cloud\TextToSpeech\V1\VoiceSelectionParams();
            $voice->setLanguageCode($language);
            $voice->setName($language . '-Neural2-A');

            $audioConfig = new \Google\Cloud\TextToSpeech\V1\AudioConfig();
            $audioConfig->setAudioEncoding(\Google\Cloud\TextToSpeech\V1\AudioEncoding::MP3);

            $response = $client->synthesizeSpeech($input, $voice, $audioConfig);
            $audioContent = $response->getAudioContent();

            $filename = 'response_' . time() . '.mp3';
            $path = storage_path('app/public/voice/' . $filename);
            file_put_contents($path, $audioContent);

            return [
                'audio_file' => $filename,
                'url' => asset('storage/voice/' . $filename)
            ];
        } catch (\Exception $e) {
            Log::error('Text-to-Speech error: ' . $e->getMessage());
            return ['error' => 'Voice generation failed'];
        }
    }
}

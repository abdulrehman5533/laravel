<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdvancedAIService;
use App\Services\AIAgentChatService;
use Illuminate\Support\Facades\Log;

class AIAgentChatController extends Controller
{
    private $aiService;
    private $chatService;

    public function __construct(AdvancedAIService $aiService, AIAgentChatService $chatService)
    {
        $this->aiService = $aiService;
        $this->chatService = $chatService;
    }

    /**
     * Chat interface - Main AI Agent UI
     */
    public function index()
    {
        return view('ai-agent.chat');
    }

    /**
     * Professional Chat interface - Advanced AI Agent UI
     */
    public function professionalChat()
    {
        return view('ai-agent.professional-chat');
    }

    /**
     * Process chat message with real AI
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
                'language' => 'nullable|in:ur,en,hi,pa',
                'conversation_id' => 'nullable|string|max:64'
            ]);

            $message = $request->message;
            $language = $request->input('language', 'en');
            $conversationId = $request->input('conversation_id', uniqid('conv_'));

            $result = $this->chatService->processMessage($message, $language, $conversationId);

            return response()->json([
                'status' => $result['status'],
                'message' => $result['answer'],
                'data' => $result['data'],
                'action' => $result['intent'] ?? null,
                'action_taken' => $result['action_taken'] ?? false,
                'source' => $result['source'] ?? null,
                'conversation_id' => $result['conversation_id'] ?? $conversationId,
            ]);
        } catch (\Exception $e) {
            Log::error('Chat processing error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stream chat messages via SSE
     */
    public function streamMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
                'language' => 'nullable|in:ur,en,hi,pa',
                'conversation_id' => 'nullable|string|max:64'
            ]);

            $conversationId = $request->input('conversation_id', uniqid('conv_'));
            $language = $request->input('language', 'en');
            $message = $request->message;

            $response = new \Illuminate\Http\StreamedResponse(function () use ($message, $language, $conversationId) {
                try {
                    $stream = $this->chatService->streamMessage($message, $language, $conversationId);

                    echo "data: " . json_encode(['type' => 'start', 'conversation_id' => $conversationId]) . "\n\n";
                    ob_flush();
                    flush();

                    foreach ($stream as $chunk) {
                        echo "data: " . json_encode($chunk) . "\n\n";
                        ob_flush();
                        flush();
                    }

                    echo "data: " . json_encode(['type' => 'done']) . "\n\n";
                    ob_flush();
                    flush();
                } catch (\Exception $e) {
                    Log::error('Stream chat error: ' . $e->getMessage());
                    echo "data: " . json_encode(['type' => 'error', 'content' => 'Streaming failed']) . "\n\n";
                    ob_flush();
                    flush();
                }
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Connection' => 'keep-alive',
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Stream chat error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Streaming failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conversation list
     */
    public function getConversations()
    {
        try {
            $result = $this->chatService->getConversations();
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Get conversations error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load conversations'
            ], 500);
        }
    }

    /**
     * Get conversation history
     */
    public function getHistory($conversationId)
    {
        try {
            $result = $this->chatService->getHistory($conversationId);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Get history error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load history'
            ], 500);
        }
    }

    /**
     * Clear conversation
     */
    public function clearConversation($conversationId)
    {
        try {
            $this->chatService->clearConversation($conversationId);
            return response()->json([
                'status' => 'success',
                'message' => 'Conversation cleared'
            ]);
        } catch (\Exception $e) {
            Log::error('Clear conversation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to clear conversation'
            ], 500);
        }
    }

    /**
     * Get agent status with detailed capabilities
     */
    public function getAgentStatus()
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'name' => 'MAGIA AI Agent',
                    'version' => '5.0.0',
                    'status' => 'online',
                    'ai_provider' => env('AI_PROVIDER', 'auto'),
                    'streaming' => true,
                    'memory' => true,
                    'tools' => [
                        'database_query',
                        'analytics',
                        'reports',
                        'search',
                        'id_generation',
                        'count_records'
                    ],
                    'capabilities' => [
                        '🤖 Natural Language Processing',
                        '👥 Employee Management (Add, Update, Delete, List)',
                        '📦 Inventory Management (Add, Update, Delete, List, Search)',
                        '🛍️ Customer Management (Add, Update, Delete, List)',
                        '💰 Sales Management (Create, List, Analytics)',
                        '📊 Real-time Analytics & Dashboard',
                        '💎 Gold/Silver Rate Tracking',
                        '📄 Report Generation (PDF, CSV, JSON)',
                        '🧠 Conversation Memory & Context',
                        '⚡ Streaming Real-time Responses',
                        '🔧 Automatic Tool/Function Calling',
                        '🌍 Multi-language Support (Urdu, English, Hindi, Punjabi)',
                        '📱 Voice Input/Output (Speech-to-Text & Text-to-Speech)'
                    ],
                    'supported_models' => [
                        'gpt-4o',
                        'gpt-4',
                        'gpt-3.5-turbo',
                        'mixtral-8x7b-32768',
                        'gemini-1.5-pro',
                        'gemini-1.5-flash'
                    ],
                    'languages' => [
                        ['code' => 'ur', 'name' => 'اردو', 'flag' => '🇵🇰'],
                        ['code' => 'en', 'name' => 'English', 'flag' => '🇬🇧'],
                        ['code' => 'hi', 'name' => 'हिंदी', 'flag' => '🇮🇳'],
                        ['code' => 'pa', 'name' => 'ਪੰਜਾਬੀ', 'flag' => '🇵🇰']
                    ],
                    'ai_model' => $this->getActiveModel()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Get status error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get agent status'
            ], 500);
        }
    }

    /**
     * Get supported commands
     */
    public function getSupportedCommands()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'employees' => [
                    'نیا ملازم علی شامل کریں، سیلری 50000' => 'ملازم شامل کریں',
                    'تمام ملازمین دیکھیں' => 'ملازمین کی فہرست',
                    'علی کی سیلری 60000 کریں' => 'ملازم کو اپڈیٹ کریں',
                    'علی کو ہٹائیں' => 'ملازم کو حذف کریں',
                    'ملازم جوڑیان کی معلومات' => 'ملازم کی معلومات'
                ],
                'products' => [
                    'سونے کی انگوٹھی شامل کریں، وزن 10، قیمت 50000' => 'پروڈکٹ شامل کریں',
                    'تمام پروڈکٹس دیکھیں' => 'پروڈکٹس کی فہرست',
                    'انگوٹھی کی قیمت 55000 کریں' => 'پروڈکٹ کو اپڈیٹ کریں',
                    'انگوٹھی کو ہٹائیں' => 'پروڈکٹ کو حذف کریں',
                    'چاندی کا ڈھنگر تلاش کریں' => 'پروڈکٹ تلاش کریں'
                ],
                'customers' => [
                    'احمد کو کسٹمر کے طور پر شامل کریں' => 'کسٹمر شامل کریں',
                    'تمام کسٹمرز دیکھیں' => 'کسٹمرز کی فہرست',
                    'احمد کا فون نمبر بدلیں' => 'کسٹمر کو اپڈیٹ کریں',
                    'احمد کو ہٹائیں' => 'کسٹمر کو حذف کریں'
                ],
                'sales' => [
                    '100000 کا سیل بنائیں' => 'نیا سیل',
                    'آج کے سیلز دیکھیں' => 'سیلز کی فہرست',
                    'اس ماہ کی کل فروخت' => 'فروخت کا تجزیہ'
                ],
                'analytics' => [
                    'تجزیہ دیں' => 'مکمل تجزیہ',
                    'سونے کی قیمت کیا ہے' => 'موجودہ قیمت',
                    'آج کی تجزیہ' => 'تجزیہ'
                ]
            ]
        ]);
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                ['code' => 'ur', 'name' => 'اردو', 'flag' => '🇵🇰'],
                ['code' => 'en', 'name' => 'English', 'flag' => '🇬🇧'],
                ['code' => 'hi', 'name' => 'हिंदी', 'flag' => '🇮🇳'],
                ['code' => 'pa', 'name' => 'ਪੰਜਾਬੀ', 'flag' => '🇵🇰']
            ]
        ]);
    }

    /**
     * Get active AI model info
     */
    private function getActiveModel()
    {
        $provider = env('AI_PROVIDER', 'auto');
        $models = [
            'openai' => 'GPT-4o',
            'groq' => 'Mixtral 8x7B',
            'gemini' => 'Gemini 1.5 Pro',
            'auto' => 'Auto-routed (GPT-4o / Mixtral / Gemini)'
        ];

        return $models[$provider] ?? 'Auto-routed';
    }
}
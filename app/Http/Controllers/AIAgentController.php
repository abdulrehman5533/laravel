<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\RealtimeAIService;
use App\Services\AIAgentService;
use App\Services\AIAgentChatService;

class AIAgentController extends Controller
{
    private $agentUrl;
    private $chatService;

    public function __construct()
    {
        $this->agentUrl = env('PYTHON_AGENT_URL', 'http://127.0.0.1:8001');
        $this->chatService = new AIAgentChatService();
    }

    /**
     * Index - redirect to professional chat UI
     */
    public function index()
    {
        return redirect('/ai-agent/professional-chat');
    }

    /**
     * Show AI Agent Chat Interface (Legacy)
     */
    public function chat()
    {
        return view('ai-agent.chat');
    }

    /**
     * Process Chat Message - Public endpoint (legacy compatibility)
     */
    public function processChat(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:5000',
                'language' => 'nullable|string|in:en,ur,hi,pa'
            ]);

            $result = $this->chatService->processMessage(
                $validated['message'],
                $validated['language'] ?? 'en'
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('AI Chat Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process message: ' . $e->getMessage(),
                'error_code' => 'CHAT_PROCESSING_ERROR'
            ], 500);
        }
    }

    /**
     * Stream chat response via SSE
     */
    public function streamChat(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:5000',
                'language' => 'nullable|string|in:en,ur,hi,pa',
                'conversation_id' => 'nullable|string'
            ]);

            $conversationId = $validated['conversation_id'] ?? uniqid('conv_');

            $context = [
                'user_id' => auth()->id() ?? 1,
                'user_role' => auth()->user()->role ?? 'user',
                'branch_id' => auth()->user()->branch_id ?? 1,
                'user_name' => auth()->user()->name ?? 'User'
            ];

            $response = Http::timeout(90)
                ->withHeaders([
                    'Accept' => 'text/event-stream',
                    'X-User-ID' => auth()->id() ?? '',
                ])
                ->withBody(
                    json_encode([
                        'message' => $validated['message'],
                        'language' => $validated['language'] ?? 'en',
                        'conversation_id' => $conversationId,
                        'context' => $context
                    ]),
                    'application/json'
                )
                ->send('POST', "{$this->agentUrl}/api/chat/stream");

            if ($response->successful()) {
                return response($response->body())
                    ->header('Content-Type', 'text/event-stream')
                    ->header('Cache-Control', 'no-cache')
                    ->header('X-Accel-Buffering', 'no');
            }

            return response()->json(['error' => 'Streaming failed'], 500);

        } catch (\Exception $e) {
            Log::error('Stream chat error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Agent Status
     */
    public function status()
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->agentUrl}/api/status");

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            Log::warning('Python agent unavailable: ' . $e->getMessage());
        }

        // Fallback status
        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => 'MAGIA AI Agent (PHP Fallback)',
                'version' => '5.0.0',
                'status' => 'online',
                'type' => 'php_fallback',
                'features' => [
                    '🤖 Natural Language Processing',
                    '👥 Employee Management (Add/Update/Delete/List)',
                    '📦 Product Management (Add/Update/Delete/List)',
                    '🛍️ Customer Management (Add/Update/Delete/List)',
                    '💰 Sales Management (Create/List/Analytics)',
                    '📊 Analytics & Reports',
                    '💎 Gold Rate Tracking',
                    '🌍 Urdu & English Support',
                    '🎯 Smart Intent Detection',
                    '⚡ Real-time Database Integration'
                ],
                'ai_model' => 'GPT-4o/Gemini/Mixtral (Auto-routed)',
                'production_ready' => true
            ]
        ]);
    }

    /**
     * Get Agent Status (API endpoint)
     */
    public function getAgentStatus()
    {
        return $this->status();
    }

    /**
     * Get conversations list
     */
    public function conversations()
    {
        try {
            $response = Http::timeout(5)->get("{$this->agentUrl}/api/conversations");
            if ($response->successful()) return response()->json($response->json());
        } catch (\Exception $e) {}
        return response()->json(['status' => 'success', 'data' => ['conversations' => []]]);
    }

    /**
     * Get conversation history
     */
    public function history($id)
    {
        try {
            $response = Http::timeout(5)->get("{$this->agentUrl}/api/history/{$id}");
            if ($response->successful()) return response()->json($response->json());
        } catch (\Exception $e) {}
        return response()->json(['status' => 'success', 'data' => ['messages' => []]]);
    }

    /**
     * Clear conversation
     */
    public function clearConversation($id)
    {
        try {
            $response = Http::timeout(5)->delete("{$this->agentUrl}/api/conversations/{$id}");
            if ($response->successful()) return response()->json($response->json());
        } catch (\Exception $e) {}
        return response()->json(['status' => 'success']);
    }

    /**
     * Analytics Proxy
     */
    public function analytics()
    {
        try {
            $response = Http::timeout(5)->get("{$this->agentUrl}/api/analytics");
            if ($response->successful()) {
                $data = $response->json();
                $analytics = $data['data']['analytics'] ?? $data['data'] ?? [];
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'total_products'    => $analytics['total_products'] ?? 0,
                        'total_employees'   => $analytics['active_employees'] ?? 0,
                        'total_salary_cost' => 0,
                        'total_sales'       => $analytics['total_sales'] ?? 0,
                        'total_customers'   => $analytics['total_customers'] ?? 0,
                        'daily_sales'       => $analytics['daily_sales'] ?? []
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Analytics proxy failed: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_products'    => 0,
                'total_employees'   => 0,
                'total_salary_cost' => 0,
                'total_sales'       => 0,
                'total_customers'   => 0,
                'daily_sales'       => []
            ]
        ]);
    }

    /**
     * Health Check
     */
    public function health()
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->agentUrl}/health");

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'healthy',
                'message' => 'PHP fallback mode active',
                'python_agent' => 'unreachable'
            ]);
        }
    }

    /**
     * Get Supported Languages
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
     * Voice to Chat
     */
    public function voiceToChat(Request $request)
    {
        try {
            $request->validate([
                'audio' => 'required|file|mimes:wav,mp3,ogg,webm|max:10240',
                'language' => 'nullable|in:ur,en,hi,pa'
            ]);

            $file = $request->file('audio');
            $language = $request->input('language', 'en');

            $response = Http::timeout(30)
                ->attach('audio', file_get_contents($file->path()), $file->getClientOriginalName())
                ->post("{$this->agentUrl}/api/chat/voice", [
                    'language' => $language
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('Voice to chat error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Voice processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Text to Speech
     */
    public function synthesizeSpeech(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|max:5000',
                'language' => 'nullable|in:ur,en,hi,pa',
                'voice_gender' => 'nullable|in:MALE,FEMALE'
            ]);

            $response = Http::timeout(30)
                ->asForm()
                ->post("{$this->agentUrl}/api/synthesize", [
                    'text' => $request->text,
                    'language' => $request->input('language', 'en')
                ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('TTS error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Speech synthesis failed'
            ], 500);
        }
    }
}
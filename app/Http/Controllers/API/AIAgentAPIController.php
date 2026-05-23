<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Services\NotificationService;
use App\Services\VoiceProcessingService;
use App\Services\AdvancedAIAgentServiceV2;
use App\Services\AIAgentChatService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class AIAgentAPIController extends Controller
{
    private $analyticsService;
    private $notificationService;
    private $voiceService;
    private $aiService;
    private $chatService;

    public function __construct(
        AnalyticsService $analyticsService,
        NotificationService $notificationService,
        VoiceProcessingService $voiceService,
        AdvancedAIAgentServiceV2 $aiService,
        AIAgentChatService $chatService
    ) {
        $this->analyticsService = $analyticsService;
        $this->notificationService = $notificationService;
        $this->voiceService = $voiceService;
        $this->aiService = $aiService;
        $this->chatService = $chatService;
    }

    /**
     * AI Agent - Process Chat via Python Agent (Primary)
     */
    public function processChat(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:5000',
                'language' => 'nullable|in:ur,en,hi,pa',
                'conversation_id' => 'nullable|string|max:64'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Invalid request', 400, $validator->errors());
            }

            $pythonAgentUrl = env('PYTHON_AGENT_URL', 'http://127.0.0.1:8001');

            try {
                $response = Http::timeout(45)->post("{$pythonAgentUrl}/api/chat/message", [
                    'message' => $request->message,
                    'language' => $request->input('language', 'en'),
                    'conversation_id' => $request->input('conversation_id'),
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->role ?? null,
                    'context' => [
                        'user_id' => auth()->id(),
                        'user_role' => auth()->user()->role ?? null,
                        'branch_id' => auth()->user()->branch_id ?? 1,
                        'business_type' => 'jewellery'
                    ]
                ]);

                if ($response->successful()) {
                    return $this->successResponse(
                        $response->json()['data'] ?? null,
                        $response->json()['message'] ?? 'Message processed',
                        ['conversation_id' => $response->json()['conversation_id'] ?? null]
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Python agent unavailable: ' . $e->getMessage());
            }

            // Fallback to PHP AI service
            $result = $this->aiService->processMessage(
                $request->message,
                $request->input('language', 'ur')
            );

            return $this->successResponse(
                $result['data'] ?? null,
                $result['answer'] ?? 'Message processed',
                ['source' => 'php_fallback']
            );

        } catch (\Exception $e) {
            Log::error('AI chat API error: ' . $e->getMessage());
            return $this->errorResponse('Failed to process message', 500);
        }
    }

    /**
     * AI Agent - Stream Chat Response
     */
    public function streamChat(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:5000',
                'language' => 'nullable|in:ur,en,hi,pa',
                'conversation_id' => 'nullable|string|max:64'
            ]);

            $pythonAgentUrl = env('PYTHON_AGENT_URL', 'http://127.0.0.1:8001');
            $conversationId = $validated['conversation_id'] ?? uniqid('conv_');

            $response = Http::timeout(90)
                ->withHeaders(['Accept' => 'text/event-stream'])
                ->post("{$pythonAgentUrl}/api/chat/stream", [
                    'message' => $validated['message'],
                    'language' => $validated['language'] ?? 'en',
                    'conversation_id' => $conversationId,
                    'user_id' => auth()->id(),
                    'context' => [
                        'user_id' => auth()->id(),
                        'user_role' => auth()->user()->role ?? null,
                        'branch_id' => auth()->user()->branch_id ?? 1,
                    ]
                ]);

            if ($response->successful()) {
                return response($response->body())
                    ->header('Content-Type', 'text/event-stream')
                    ->header('Cache-Control', 'no-cache')
                    ->header('X-Accel-Buffering', 'no');
            }

            return $this->errorResponse('Streaming unavailable, trying standard chat', 503);

        } catch (\Exception $e) {
            Log::error('Stream chat API error: ' . $e->getMessage());
            return $this->errorResponse('Streaming failed', 500);
        }
    }

    /**
     * AI Agent - Get Status
     */
    public function getAgentStatus()
    {
        try {
            $pythonAgentUrl = env('PYTHON_AGENT_URL', 'http://127.0.0.1:8001');

            $response = Http::timeout(10)
                ->get("{$pythonAgentUrl}/api/status");

            if ($response->successful()) {
                return $this->successResponse(
                    $response->json()['data'] ?? [],
                    'Agent status retrieved'
                );
            }
        } catch (\Exception $e) {
            Log::warning('Python agent status unavailable: ' . $e->getMessage());
        }

        // Fallback status
        return $this->successResponse([
            'name' => 'MAGIA AI Agent',
            'version' => '5.0.0',
            'status' => 'online',
            'type' => 'hybrid_php_python',
            'features' => [
                '🤖 Natural Language Processing (GPT-4o, Gemini, Groq)',
                '👥 Employee Management (Add, Update, Delete, List)',
                '📦 Product/Inventory Management (Add, Update, Delete, Search)',
                '🛍️ Customer Management (Add, Update, Delete, List)',
                '💰 Sales Management (Create, List, Analytics)',
                '📊 Real-time Analytics & Dashboard',
                '💎 Gold/Silver Rate Tracking',
                '📄 Multi-format Report Generation',
                '🧠 Conversation Memory & Context Awareness',
                '⚡ Real-time Streaming (SSE)',
                '🔧 Automatic Database Operations',
                '🌍 Multi-language (Urdu, English, Hindi, Punjabi)',
                '📱 Voice Input/Output Support'
            ],
            'ai_model' => 'GPT-4o / Gemini 1.5 Pro / Mixtral 8x7B (Auto-routed)',
            'production_ready' => true,
            'streaming_enabled' => true,
            'memory_enabled' => true
        ], 'Agent status (fallback)');
    }

    // ==================== ANALYTICS ENDPOINTS ====================

    public function getSalesAnalytics(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $validator = Validator::make(['days' => $days], ['days' => 'integer|min:1|max:365']);
            if ($validator->fails()) return $this->errorResponse('Invalid days', 400, $validator->errors());

            $result = $this->analyticsService->getSalesAnalytics($days);
            return $this->successResponse($result['data'], 'Sales analytics retrieved');
        } catch (\Exception $e) {
            Log::error('Sales analytics error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve sales analytics', 500);
        }
    }

    public function getInventoryAnalytics(Request $request)
    {
        try {
            $result = $this->analyticsService->getInventoryAnalytics();
            return $this->successResponse($result['data'], 'Inventory analytics retrieved');
        } catch (\Exception $e) {
            Log::error('Inventory analytics error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve inventory analytics', 500);
        }
    }

    public function getCustomerAnalytics(Request $request)
    {
        try {
            $result = $this->analyticsService->getCustomerAnalytics();
            return $this->successResponse($result['data'], 'Customer analytics retrieved');
        } catch (\Exception $e) {
            Log::error('Customer analytics error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve customer analytics', 500);
        }
    }

    public function getEmployeeAnalytics(Request $request)
    {
        try {
            $result = $this->analyticsService->getEmployeeAnalytics();
            return $this->successResponse($result['data'], 'Employee analytics retrieved');
        } catch (\Exception $e) {
            Log::error('Employee analytics error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve employee analytics', 500);
        }
    }

    public function getPurchaseAnalytics(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $validator = Validator::make(['days' => $days], ['days' => 'integer|min:1|max:365']);
            if ($validator->fails()) return $this->errorResponse('Invalid days', 400, $validator->errors());

            $result = $this->analyticsService->getPurchaseAnalytics($days);
            return $this->successResponse($result['data'], 'Purchase analytics retrieved');
        } catch (\Exception $e) {
            Log::error('Purchase analytics error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve purchase analytics', 500);
        }
    }

    public function getServiceAnalytics(Request $request)
    {
        try {
            $result = $this->analyticsService->getServiceAnalytics();
            return $this->successResponse($result['data'], 'Service analytics retrieved');
        } catch (\Exception $e) {
            Log::error('Service analytics error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve service analytics', 500);
        }
    }

    public function getFinancialAnalytics(Request $request)
    {
        try {
            $days = $request->input('days', 30);
            $validator = Validator::make(['days' => $days], ['days' => 'integer|min:1|max:365']);
            if ($validator->fails()) return $this->errorResponse('Invalid days', 400, $validator->errors());

            $result = $this->analyticsService->getFinancialAnalytics($days);
            return $this->successResponse($result['data'], 'Financial analytics retrieved');
        } catch (\Exception $e) {
            Log::error('Financial analytics error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve financial analytics', 500);
        }
    }

    public function getDashboardSummary(Request $request)
    {
        try {
            $result = $this->analyticsService->getDashboardSummary();
            return $this->successResponse($result['data'], 'Dashboard summary retrieved');
        } catch (\Exception $e) {
            Log::error('Dashboard summary error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve dashboard summary', 500);
        }
    }

    public function getTrends(Request $request)
    {
        try {
            $days = $request->input('days', 90);
            $validator = Validator::make(['days' => $days], ['days' => 'integer|min:1|max:365']);
            if ($validator->fails()) return $this->errorResponse('Invalid days', 400, $validator->errors());

            $result = $this->analyticsService->getTrends($days);
            return $this->successResponse($result['data'], 'Trends retrieved');
        } catch (\Exception $e) {
            Log::error('Trends error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve trends', 500);
        }
    }

    // ==================== NOTIFICATION ENDPOINTS ====================

    public function sendEmail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'nullable|in:info,warning,error,success'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $result = $this->notificationService->sendEmail(
                $request->email, $request->subject, $request->message,
                $request->input('type', 'info')
            );
            return $this->successResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Send email error: ' . $e->getMessage());
            return $this->errorResponse('Failed to send email', 500);
        }
    }

    public function sendSMS(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^\+?[0-9]{10,}$/',
                'message' => 'required|string|max:160'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $result = $this->notificationService->sendSMS($request->phone, $request->message);
            return $this->successResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Send SMS error: ' . $e->getMessage());
            return $this->errorResponse('Failed to send SMS', 500);
        }
    }

    public function sendInAppNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'nullable|in:info,warning,error,success',
                'action_url' => 'nullable|url'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $result = $this->notificationService->sendInAppNotification(
                $request->user_id, $request->title, $request->message,
                $request->input('type', 'info'), $request->input('action_url')
            );
            return $this->successResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Send in-app notification error: ' . $e->getMessage());
            return $this->errorResponse('Failed to send notification', 500);
        }
    }

    public function sendWhatsApp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|regex:/^\+?[0-9]{10,}$/',
                'message' => 'required|string'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $result = $this->notificationService->sendWhatsApp($request->phone, $request->message);
            return $this->successResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Send WhatsApp error: ' . $e->getMessage());
            return $this->errorResponse('Failed to send WhatsApp', 500);
        }
    }

    public function sendBulkNotifications(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'integer|exists:users,id',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'nullable|in:info,warning,error,success'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $result = $this->notificationService->sendBulkNotifications(
                $request->user_ids, $request->title, $request->message,
                $request->input('type', 'info')
            );
            return $this->successResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Send bulk notifications error: ' . $e->getMessage());
            return $this->errorResponse('Failed to send bulk notifications', 500);
        }
    }

    public function getUserNotifications(Request $request)
    {
        try {
            $userId = $request->input('user_id', auth()->id());
            $limit = $request->input('limit', 10);
            $validator = Validator::make(['limit' => $limit], ['limit' => 'integer|min:1|max:100']);
            if ($validator->fails()) return $this->errorResponse('Invalid limit', 400, $validator->errors());

            $result = $this->notificationService->getUserNotifications($userId, $limit);
            return $this->successResponse($result['data'], 'Notifications retrieved');
        } catch (\Exception $e) {
            Log::error('Get notifications error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve notifications', 500);
        }
    }

    public function markNotificationAsRead(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'notification_id' => 'required|integer|exists:notification_histories,id'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $result = $this->notificationService->markAsRead($request->notification_id);
            return $this->successResponse([], $result['message']);
        } catch (\Exception $e) {
            Log::error('Mark as read error: ' . $e->getMessage());
            return $this->errorResponse('Failed to mark as read', 500);
        }
    }

    // ==================== VOICE ENDPOINTS ====================

    public function transcribeAudio(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'audio' => 'required|file|mimes:wav,mp3,ogg,webm,m4a|max:10240',
                'language' => 'nullable|in:ur,en,hi,pa'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $file = $request->file('audio');
            $language = $request->input('language', 'ur');

            $result = $this->voiceService->transcribeAudio($file->path(), $language);
            return $this->successResponse($result['data'], 'Audio transcribed');
        } catch (\Exception $e) {
            Log::error('Transcribe audio error: ' . $e->getMessage());
            return $this->errorResponse('Failed to transcribe audio', 500);
        }
    }

    public function synthesizeSpeech(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'text' => 'required|string|max:1000',
                'language' => 'nullable|in:ur,en,hi,pa',
                'voice_gender' => 'nullable|in:MALE,FEMALE'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $result = $this->voiceService->synthesizeSpeech(
                $request->text,
                $request->input('language', 'ur'),
                $request->input('voice_gender', 'FEMALE')
            );
            return $this->successResponse($result['data'], 'Speech synthesized');
        } catch (\Exception $e) {
            Log::error('Synthesize speech error: ' . $e->getMessage());
            return $this->errorResponse('Failed to synthesize speech', 500);
        }
    }

    public function detectLanguage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'audio' => 'required|file|mimes:wav,mp3,ogg,webm,m4a|max:10240'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            $file = $request->file('audio');
            $result = $this->voiceService->detectLanguage($file->path());
            return $this->successResponse($result['data'], 'Language detected');
        } catch (\Exception $e) {
            Log::error('Detect language error: ' . $e->getMessage());
            return $this->errorResponse('Failed to detect language', 500);
        }
    }

    // ==================== AI AGENT CORE ENDPOINTS ====================

    public function processMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'message' => 'required|string|max:1000',
                'language' => 'nullable|in:ur,en,hi,pa'
            ]);
            if ($validator->fails()) return $this->errorResponse('Validation failed', 400, $validator->errors());

            // Try Python agent first
            $pythonUrl = env('PYTHON_AGENT_URL', 'http://127.0.0.1:8001');
            try {
                $response = Http::timeout(15)->post("{$pythonUrl}/api/chat/message", [
                    'message' => $request->message,
                    'language' => $request->input('language', 'ur')
                ]);
                if ($response->successful()) {
                    return $this->successResponse(
                        $response->json()['data'] ?? null,
                        $response->json()['message'] ?? 'Processed'
                    );
                }
            } catch (\Exception $e) {
                Log::warning('Python agent failed, using PHP: ' . $e->getMessage());
            }

            // Fallback to PHP AI
            $result = $this->aiService->processMessage(
                $request->message,
                $request->input('language', 'ur')
            );
            return $this->successResponse($result['data'], $result['answer']);
        } catch (\Exception $e) {
            Log::error('Process message error: ' . $e->getMessage());
            return $this->errorResponse('Failed to process message', 500);
        }
    }

    // ==================== HELPER METHODS ====================

    private function successResponse($data = [], $message = 'Success', $statusCode = 200, $extra = [])
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }
        return response()->json($response, $statusCode);
    }

    private function errorResponse($message = 'Error', $statusCode = 400, $errors = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        if ($errors) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $statusCode);
    }
}
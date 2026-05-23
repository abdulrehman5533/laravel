<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RealtimeAIService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RealtimeAIAgentController extends Controller
{
    private $aiService;

    public function __construct(RealtimeAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Chat interface
     */
    public function index()
    {
        return view('ai-agent.realtime-chat');
    }

    /**
     * Stream AI response in real-time using SSE
     */
    public function streamMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
            'language' => 'nullable|in:ur,en'
        ]);

        $message = $request->message;
        $language = $request->input('language', 'ur');

        return new StreamedResponse(function () use ($message, $language) {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            $fullResponse = '';
            $isFirstChunk = true;

            // Stream AI response
            $this->aiService->streamMessage($message, $language, function ($type, $content) use (&$fullResponse, &$isFirstChunk) {
                if ($type === 'chunk') {
                    $fullResponse .= $content;
                    
                    // Send chunk as SSE event
                    echo "data: " . json_encode([
                        'type' => 'chunk',
                        'content' => $content,
                        'full_response' => $fullResponse
                    ]) . "\n\n";
                    
                    ob_flush();
                    flush();
                    
                } elseif ($type === 'done') {
                    // Process commands after full response
                    $commandResult = $this->aiService->executeAICommand($fullResponse, $message);
                    
                    // Send completion event
                    echo "data: " . json_encode([
                        'type' => 'done',
                        'full_response' => $fullResponse,
                        'action_taken' => $commandResult['action_taken'],
                        'data' => $commandResult['data']
                    ]) . "\n\n";
                    
                    ob_flush();
                    flush();
                    
                } elseif ($type === 'error') {
                    echo "data: " . json_encode([
                        'type' => 'error',
                        'content' => $content
                    ]) . "\n\n";
                    
                    ob_flush();
                    flush();
                }
            });

        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get agent status
     */
    public function getAgentStatus()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => 'Real-time AI Agent',
                'version' => '3.0',
                'status' => 'online',
                'ai_provider' => env('AI_PROVIDER', 'gemini'),
                'streaming' => true,
                'capabilities' => [
                    '⚡ Real-time Streaming Responses',
                    '🤖 Natural Language Processing',
                    '👥 Employee Management',
                    '📦 Inventory Management',
                    '🛍️ Customer Management',
                    '💰 Sales Management',
                    '📊 Analytics & Reports',
                    '💎 Gold Rate Tracking',
                    '🌍 Urdu & English Support',
                    '🎯 Smart Intent Detection',
                    '⚡ Real-time Database Integration',
                    '🔄 Live Streaming with SSE'
                ],
                'languages' => ['Urdu (اردو)', 'English'],
                'ai_model' => env('AI_PROVIDER') === 'openai' ? 'GPT-3.5 Turbo (Streaming)' : 'Google Gemini Pro (Streaming)'
            ]
        ]);
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
                    'نیا ملازم علی شامل کریں، سیلری 50000',
                    'تمام ملازمین دیکھیں',
                    'علی کی سیلری 60000 کریں',
                    'علی کو ہٹائیں'
                ],
                'products' => [
                    'سونے کی انگوٹھی شامل کریں، وزن 10، قیمت 50000',
                    'تمام پروڈکٹس دیکھیں',
                    'انگوٹھی کی قیمت 55000 کریں',
                    'انگوٹھی کو ہٹائیں'
                ],
                'customers' => [
                    'احمد کو کسٹمر کے طور پر شامل کریں',
                    'تمام کسٹمرز دیکھیں',
                    'احمد کا فون نمبر بدلیں',
                    'احمد کو ہٹائیں'
                ],
                'sales' => [
                    '100000 کا سیل بنائیں',
                    'آج کے سیلز دیکھیں'
                ],
                'analytics' => [
                    'مجھے تجزیہ دیں',
                    'سونے کی قیمت کیا ہے'
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
                ['code' => 'en', 'name' => 'English', 'flag' => '🇬🇧']
            ]
        ]);
    }
}

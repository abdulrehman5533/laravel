<?php

namespace App\Services;

use App\Services\AdvancedAIService;
use App\Services\IntegratedAIAgentService;
use Illuminate\Support\Facades\Log;

class AIAgentChatService
{
    private $aiService;
    private $integratedService;

    public function __construct()
    {
        $this->aiService = new AdvancedAIService();
        $this->integratedService = new IntegratedAIAgentService();
    }

    /**
     * Process chat message through multiple AI layers
     */
    public function processMessage(string $message, string $language = 'en', string $conversationId = null): array
    {
        try {
            // Layer 1: Try Integrated AI Agent Service (keyword-based, fast)
            $integratedResult = $this->integratedService->processCommand($message, $language);

            if ($integratedResult['status'] === 'success' && ($integratedResult['action_taken'] ?? false) === true) {
                return [
                    'status' => 'success',
                    'answer' => $integratedResult['answer'],
                    'data' => $integratedResult['data'] ?? null,
                    'action_taken' => true,
                    'intent' => $integratedResult['intent'],
                    'source' => 'integrated_tools_auto',
                    'conversation_id' => $conversationId,
                ];
            }

            // Layer 2: Try Advanced AI Service (real AI API - Gemini/OpenAI)
            $aiResult = $this->aiService->processMessage($message, $language);

            if ($aiResult['status'] === 'success') {
                return [
                    'status' => 'success',
                    'answer' => $aiResult['answer'],
                    'data' => $aiResult['data'] ?? null,
                    'action_taken' => $aiResult['action_taken'] ?? false,
                    'intent' => $aiResult['action_taken'] ? 'ai_tool_call' : 'general_query',
                    'source' => 'real_ai_service',
                    'conversation_id' => $conversationId,
                ];
            }

            // Layer 3: Fallback - Integrated service general query
            if (isset($integratedResult['answer'])) {
                return [
                    'status' => 'success',
                    'answer' => $integratedResult['answer'],
                    'data' => $integratedResult['data'] ?? null,
                    'action_taken' => false,
                    'intent' => $integratedResult['intent'],
                    'source' => 'integrated_tools_answer',
                    'conversation_id' => $conversationId,
                ];
            }

            return [
                'status' => 'success',
                'answer' => 'معافی چاہتا ہوں، میں یہ سمجھ نہیں سکا۔ براہ کرم دوبارہ کوشش کریں۔',
                'data' => null,
                'action_taken' => false,
                'intent' => 'unknown',
                'source' => 'fallback',
                'conversation_id' => $conversationId,
            ];

        } catch (\Exception $e) {
            Log::error('AIAgentChatService error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'answer' => 'معافی چاہتا ہوں، کچھ خرابی ہوئی۔',
                'error' => $e->getMessage(),
                'conversation_id' => $conversationId,
            ];
        }
    }

    /**
     * Stream message through AI service
     */
    public function streamMessage(string $message, string $language = 'en', string $conversationId = null): \Generator
    {
        try {
            $context = [
                'user_id' => auth()->id() ?? null,
                'user_role' => auth()->user()->role ?? null,
                'branch_id' => auth()->user()->branch_id ?? 1,
                'conversation_id' => $conversationId,
            ];

            // Stream from Advanced AI Service
            $stream = $this->aiService->streamMessage($message, $language, function ($type, $content) {
                // callback handled in generator
            });

            foreach ($stream as $chunk) {
                yield $chunk;
            }

        } catch (\Exception $e) {
            Log::error('Stream message error: ' . $e->getMessage());
            yield ['type' => 'error', 'content' => 'Streaming error: ' . $e->getMessage()];
        }
    }

    /**
     * Get conversation history
     */
    public function getHistory(string $conversationId): array
    {
        try {
            $messages = []; // Would come from database if Conversation model existed
            return [
                'status' => 'success',
                'data' => [
                    'conversation_id' => $conversationId,
                    'messages' => $messages,
                    'count' => count($messages),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to load history: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get list of conversations
     */
    public function getConversations(): array
    {
        try {
            // Would come from database if Conversation model existed
            return [
                'status' => 'success',
                'data' => ['conversations' => []],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to load conversations: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Clear a conversation
     */
    public function clearConversation(string $conversationId): bool
    {
        try {
            // Would delete from database if Conversation model existed
            return true;
        } catch (\Exception $e) {
            Log::error('Clear conversation error: ' . $e->getMessage());
            return false;
        }
    }
}
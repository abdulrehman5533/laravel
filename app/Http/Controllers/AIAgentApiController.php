<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Employee;
use App\Models\Sale;

class AIAgentApiController extends Controller
{
    /**
     * Get analytics data for AI Agent dashboard
     */
    public function analytics()
    {
        try {
            $totalProducts = Product::count();
            $totalEmployees = Employee::count();
            $totalSalaryCost = DB::table('payrolls')->sum('net_salary') ?? 0;

            // Today's sales
            $todaySales = Sale::whereDate('created_at', today())->sum('total_amount') ?? 0;
            $todayOrders = Sale::whereDate('created_at', today())->count() ?? 0;

            // This week's sales
            $weekSales = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('total_amount') ?? 0;

            // This month's sales
            $monthSales = Sale::whereMonth('created_at', now()->month)
                ->sum('total_amount') ?? 0;

            // Recent sales trend (last 7 days)
            $dailySales = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                $dailySales[] = Sale::whereDate('created_at', $date)->sum('total_amount') ?? 0;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_products' => $totalProducts,
                    'total_employees' => $totalEmployees,
                    'total_salary_cost' => (float)$totalSalaryCost,
                    'today_sales' => (float)$todaySales,
                    'today_orders' => $todayOrders,
                    'week_sales' => (float)$weekSales,
                    'month_sales' => (float)$monthSales,
                    'daily_sales' => $dailySales,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload file for AI Agent
     */
    public function uploadFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg'
            ]);

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('ai-agent-uploads', $filename, 'public');

            return response()->json([
                'status' => 'success',
                'message' => 'File uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's sales summary
     */
    public function todaySales()
    {
        try {
            $sales = Sale::whereDate('created_at', today())
                ->with('customer')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $totalAmount = $sales->sum('total_amount');
            $totalOrders = $sales->count();
            $totalItems = $sales->sum(function($sale) {
                return $sale->items->sum('quantity') ?? 0;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'sales' => $sales,
                    'total_amount' => (float)$totalAmount,
                    'total_orders' => $totalOrders,
                    'total_items' => $totalItems
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Voice to Chat - transcribe audio and process
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

            // Store audio temporarily
            $filename = 'voice_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('ai-voice-temp', $filename, 'public');

            // Forward to AI agent for transcription and processing
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->attach('audio', file_get_contents($file->path()), $file->getClientOriginalName())
                ->post(env('PYTHON_AGENT_URL', 'http://127.0.0.1:8001') . '/api/chat/voice', [
                    'language' => $language
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $transcript = $data['transcript'] ?? $data['text'] ?? '';

                // Process the transcript through the chat service
                if ($transcript) {
                    $chatService = new \App\Services\AIAgentChatService();
                    $result = $chatService->processMessage($transcript, $language);

                    return response()->json([
                        'status' => 'success',
                        'transcript' => $transcript,
                        'response' => $result['answer'] ?? '',
                        'action' => $result['intent'] ?? null,
                        'action_taken' => $result['action_taken'] ?? false,
                        'data' => $result['data'] ?? null
                    ]);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'No transcription received'
                ], 500);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Voice processing failed'
            ], 500);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Voice to chat error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Voice processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Text to Speech synthesis
     */
    public function synthesizeSpeech(Request $request)
    {
        try {
            $request->validate([
                'text' => 'required|string|max:5000',
                'language' => 'nullable|in:ur,en,hi,pa',
                'voice_gender' => 'nullable|in:MALE,FEMALE'
            ]);

            // Forward to Python agent for TTS
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->asForm()
                ->post(env('PYTHON_AGENT_URL', 'http://127.0.0.1:8001') . '/api/synthesize', [
                    'text' => $request->text,
                    'language' => $request->input('language', 'en'),
                    'voice_gender' => $request->input('voice_gender', 'MALE')
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'status' => 'success',
                    'audio_url' => $data['audio_url'] ?? $data['url'] ?? null,
                    'audio_base64' => $data['audio_base64'] ?? null,
                    'duration' => $data['duration'] ?? null
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Speech synthesis failed'
            ], 500);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('TTS error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Speech synthesis failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

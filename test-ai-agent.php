#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AdvancedAIAgentService;

$aiService = app(AdvancedAIAgentService::class);

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         AI Agent Test Script - اردو میں ٹیسٹ کریں          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$testMessages = [
    "Ring کا اسٹاک کتنا ہے؟",
    "آج کی فروخت کتنی ہے؟",
    "ملازمین کی فہرست دکھائیں",
    "کاروباری خلاصہ دیں",
    "سونے کی قیمت کیا ہے؟",
    "Hello, how are you?",
];

foreach ($testMessages as $index => $message) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Test #" . ($index + 1) . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "📝 Message: {$message}\n";
    echo "⏳ Processing...\n\n";
    
    try {
        $result = $aiService->processMessage($message, 'ur');
        
        if ($result['status'] === 'success') {
            echo "✅ Status: SUCCESS\n";
            echo "💬 Response:\n";
            echo "   " . str_replace("\n", "\n   ", $result['data']['answer']) . "\n";
        } else {
            echo "❌ Status: ERROR\n";
            echo "   " . $result['message'] . "\n";
        }
    } catch (\Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                    ✅ Test Complete!                       ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

# 🔗 AI Agent - Laravel Integration Guide

## Complete Integration Setup

---

## 📋 Step 1: Verify Controller Exists

Check if controller is created:
```bash
# File should exist at:
app/Http/Controllers/AIAgentController.php
```

---

## 📋 Step 2: Update Routes

Your routes are already updated in `routes/web.php`:

```php
Route::prefix('ai-agent')->name('ai-agent.')->group(function () {
    Route::post('/voice', [AIAgentController::class, 'processVoice'])->name('voice');
    Route::post('/chat', [AIAgentController::class, 'processChat'])->name('chat');
    Route::post('/voice-to-chat', [AIAgentController::class, 'voiceToChat'])->name('voice-to-chat');
    Route::post('/synthesize', [AIAgentController::class, 'synthesizeSpeech'])->name('synthesize');
    Route::post('/voice-to-voice', [AIAgentController::class, 'voiceToVoiceAutomation'])->name('voice-to-voice');
    Route::get('/status', [AIAgentController::class, 'getAgentStatus'])->name('status');
    Route::get('/languages', [AIAgentController::class, 'getSupportedLanguages'])->name('languages');
});
```

---

## 📋 Step 3: Create Blade Template

Create the web interface:

```bash
# File location:
resources/views/ai-agent/index.blade.php
```

This is already created! ✅

---

## 📋 Step 4: Add Menu Item (Optional)

Add to your navigation menu in `resources/views/layouts/app.blade.php`:

```blade
<li class="nav-item">
    <a class="nav-link" href="{{ route('ai-agent.index') }}">
        <i class="fas fa-robot"></i> AI Agent
    </a>
</li>
```

---

## 📋 Step 5: Create Route for Web UI

Add this to `routes/web.php` in the protected routes section:

```php
Route::get('/ai-agent', function () {
    return view('ai-agent.index');
})->name('ai-agent.index');
```

---

## 📋 Step 6: Update .env (Laravel)

Add Python agent URL to your Laravel `.env`:

```env
PYTHON_AI_AGENT_URL=http://localhost:8000/api
```

---

## 📋 Step 7: Test Integration

### Test 1: Check Routes
```bash
php artisan route:list | grep ai-agent
```

### Test 2: Access Web UI
```
http://localhost:8080/ai-agent
```

### Test 3: Test Chat Endpoint
```bash
curl -X POST http://localhost:8080/ai-agent/chat \
  -H "X-CSRF-TOKEN: your_csrf_token" \
  -d "message=Stock update karo&language=ur"
```

---

## 🎯 Usage Examples

### Example 1: From Blade Template

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>AI Chat Assistant</h1>
    
    <form action="{{ route('ai-agent.chat') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Message</label>
            <input type="text" name="message" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Language</label>
            <select name="language" class="form-control">
                <option value="ur">Urdu</option>
                <option value="en">English</option>
                <option value="hi">Hindi</option>
                <option value="pa">Punjabi</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
    
    @if(isset($response))
        <div class="alert alert-info mt-3">
            <strong>Response:</strong>
            {{ $response['data']['answer'] ?? $response['data']['extracted'] }}
        </div>
    @endif
</div>
@endsection
```

### Example 2: From Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->input('message');
        $language = $request->input('language', 'ur');
        
        $response = Http::post(env('PYTHON_AI_AGENT_URL') . '/chat/message', [
            'message' => $message,
            'language' => $language
        ]);
        
        return response()->json($response->json());
    }
}
```

### Example 3: From AJAX

```javascript
// Send chat message via AJAX
document.getElementById('sendBtn').addEventListener('click', async () => {
    const message = document.getElementById('chatInput').value;
    const language = document.getElementById('language').value;
    
    const response = await fetch('{{ route("ai-agent.chat") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message, language })
    });
    
    const data = await response.json();
    console.log(data);
});
```

---

## 🔐 Security Considerations

### 1. CSRF Protection
All routes are protected with CSRF middleware. Always include:
```blade
@csrf
```

### 2. Rate Limiting (Optional)
Add to `routes/web.php`:
```php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/ai-agent/chat', [AIAgentController::class, 'processChat']);
});
```

### 3. Authentication
Add to routes if needed:
```php
Route::middleware('auth')->group(function () {
    Route::post('/ai-agent/chat', [AIAgentController::class, 'processChat']);
});
```

---

## 🧪 Testing Integration

### Test 1: Direct API Call
```bash
curl -X POST http://localhost:8000/api/chat/message \
  -H "Content-Type: application/json" \
  -d "{\"message\": \"Stock update karo\", \"language\": \"ur\"}"
```

### Test 2: Through Laravel
```bash
curl -X POST http://localhost:8080/ai-agent/chat \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "message=Stock update karo&language=ur&_token=YOUR_CSRF_TOKEN"
```

### Test 3: From Tinker
```bash
php artisan tinker

$response = Http::post('http://localhost:8000/api/chat/message', [
    'message' => 'Stock update karo',
    'language' => 'ur'
]);

$response->json();
```

---

## 📊 Integration Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Laravel App                          │
│  (routes/web.php, AIAgentController, Blade templates)  │
└────────────────────┬────────────────────────────────────┘
                     │ HTTP Requests
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Python FastAPI Agent                       │
│  (http://localhost:8000/api)                           │
├─────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Chat Agent   │  │ Voice Agent  │  │ TTS Agent    │  │
│  │ (Groq API)   │  │ (Google)     │  │ (Google)     │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
```

---

## 🚀 Deployment Checklist

- [ ] Python agent running on port 8000
- [ ] Laravel routes configured
- [ ] Controller created
- [ ] Blade template created
- [ ] .env configured with agent URL
- [ ] CSRF protection enabled
- [ ] Authentication configured (if needed)
- [ ] Rate limiting configured (if needed)
- [ ] Error handling implemented
- [ ] Logging configured

---

## 🐛 Troubleshooting

### Issue: "Connection refused"
```
Solution: Make sure Python agent is running
python main.py
```

### Issue: "CSRF token mismatch"
```
Solution: Include @csrf in form or X-CSRF-TOKEN in headers
```

### Issue: "404 Not Found"
```
Solution: Clear Laravel cache
php artisan cache:clear
php artisan route:cache
```

### Issue: "Timeout"
```
Solution: Increase timeout in AIAgentController
Http::timeout(30)->post(...)
```

---

## 📈 Performance Optimization

### 1. Cache Responses
```php
$response = Cache::remember('ai_response_' . md5($message), 3600, function () use ($message) {
    return Http::post(env('PYTHON_AI_AGENT_URL') . '/chat/message', [
        'message' => $message
    ]);
});
```

### 2. Queue Jobs
```php
dispatch(new ProcessAIMessage($message, $language));
```

### 3. Use Async
```php
$response = Http::async()->post(env('PYTHON_AI_AGENT_URL') . '/chat/message', [
    'message' => $message
]);
```

---

## 📚 Additional Resources

- **Laravel HTTP Client**: https://laravel.com/docs/http-client
- **CSRF Protection**: https://laravel.com/docs/csrf
- **Authentication**: https://laravel.com/docs/authentication
- **Rate Limiting**: https://laravel.com/docs/rate-limiting

---

## ✅ Integration Checklist

- [x] Controller created
- [x] Routes configured
- [x] Blade template created
- [x] .env template created
- [ ] Menu item added (optional)
- [ ] Web UI route added
- [ ] Testing completed
- [ ] Documentation completed

---

## 🎉 You're Ready!

Your AI agent is fully integrated with Laravel!

### Quick Test:
```bash
# 1. Start Python agent
cd python-ai-agent
python main.py

# 2. In new terminal, start Laravel
php artisan serve

# 3. Open browser
http://localhost:8000/ai-agent
```

---

**Version**: 1.0.0
**Status**: Ready for Integration
**Last Updated**: 2024

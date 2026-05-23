# Python AI Agent - Complete Setup Guide

## 📋 Overview

Aapke jewellery management system mein ek **complete Python-based AI agent** integrate ho gaya jo:

✅ **Multi-language voice support** (Urdu, English, Hindi, Punjabi)
✅ **Chat automation** (text-based AI responses)
✅ **Full voice automation** (Voice → Chat → Voice)
✅ **Intent detection** (automatic action recognition)
✅ **REST API** (FastAPI with Swagger docs)

---

## 🚀 Quick Start (5 Minutes)

### Step 1: Install Python
```bash
# Download from https://www.python.org (3.8+)
# Or use Windows installer
```

### Step 2: Navigate to Agent Directory
```bash
cd c:\xampp1\htdocs\jewellery-management-system\python-ai-agent
```

### Step 3: Run Start Script
```bash
start.bat
```

### Step 4: Get API Keys

**Google Cloud (Free tier available):**
1. Go to https://console.cloud.google.com
2. Create project
3. Enable: Speech-to-Text API + Text-to-Speech API
4. Create Service Account
5. Download JSON credentials
6. Copy path to `.env`

**Groq API (Free tier):**
1. Go to https://console.groq.com
2. Create API key
3. Copy to `.env`

### Step 5: Configure .env
```env
GOOGLE_APPLICATION_CREDENTIALS=C:\path\to\credentials.json
GROQ_API_KEY=your_groq_key_here
LARAVEL_API_URL=http://localhost:8080/api
LARAVEL_API_KEY=your_laravel_key
DEFAULT_LANGUAGE=ur
```

### Step 6: Start Agent
```bash
python main.py
```

Server will run at: **http://localhost:8000**

---

## 📁 Project Structure

```
python-ai-agent/
├── main.py                 # FastAPI application
├── config.py              # Configuration settings
├── requirements.txt       # Python dependencies
├── .env.example          # Environment template
├── README.md             # Documentation
├── start.bat             # Quick start script
│
├── agents/
│   ├── voice_agent.py    # Speech-to-text
│   ├── chat_agent.py     # Chat processing
│   └── tts_agent.py      # Text-to-speech
│
└── utils/
    ├── language_detector.py    # Language detection
    └── response_formatter.py   # Response formatting
```

---

## 🔌 API Endpoints

### 1. Voice-to-Text
```bash
POST /api/voice/transcribe
Content-Type: multipart/form-data

file: <audio_file>
language: ur (optional)
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "transcript": "Stock update karo",
    "confidence": 0.95,
    "language": "ur"
  }
}
```

### 2. Chat Processing
```bash
POST /api/chat/message
Content-Type: application/json

{
  "message": "Stock update karo",
  "language": "ur",
  "context": {}
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "answer": "Stock update ho gaya",
    "intent": "stock"
  }
}
```

### 3. Text-to-Speech
```bash
POST /api/tts/synthesize
Content-Type: application/json

{
  "text": "Salam, kya haal hai?",
  "language": "ur",
  "voice_gender": "FEMALE"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "filename": "response_1234567890.mp3",
    "filepath": "voice_outputs/response_1234567890.mp3",
    "language": "ur"
  }
}
```

### 4. Full Automation (Voice → Chat → Voice)
```bash
POST /api/automation/voice-to-voice
Content-Type: multipart/form-data

file: <audio_file>
language: ur (optional)
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "input_transcript": "Stock update karo",
    "input_language": "ur",
    "response_text": "Stock update ho gaya",
    "audio_file": "response_1234567890.mp3",
    "audio_url": "/api/tts/download/response_1234567890.mp3"
  }
}
```

### 5. Agent Status
```bash
GET /api/info/status
```

### 6. Supported Languages
```bash
GET /api/info/languages
```

---

## 🔗 Laravel Integration

### Routes Added

```php
// In routes/web.php
Route::prefix('ai-agent')->name('ai-agent.')->group(function () {
    Route::post('/voice', 'AIAgentController@processVoice')->name('voice');
    Route::post('/chat', 'AIAgentController@processChat')->name('chat');
    Route::post('/voice-to-chat', 'AIAgentController@voiceToChat')->name('voice-to-chat');
    Route::post('/synthesize', 'AIAgentController@synthesizeSpeech')->name('synthesize');
    Route::post('/voice-to-voice', 'AIAgentController@voiceToVoiceAutomation')->name('voice-to-voice');
    Route::get('/status', 'AIAgentController@getAgentStatus')->name('status');
    Route::get('/languages', 'AIAgentController@getSupportedLanguages')->name('languages');
});
```

### Controller Usage

```php
// app/Http/Controllers/AIAgentController.php

public function processVoice(Request $request)
{
    $file = $request->file('audio');
    $language = $request->input('language', 'ur');
    
    $response = Http::attach(
        'file',
        file_get_contents($file->path()),
        $file->getClientOriginalName()
    )->post('http://localhost:8000/api/voice/transcribe', [
        'language' => $language
    ]);
    
    return response()->json($response->json());
}
```

### Blade Template

```blade
<!-- resources/views/ai-agent/index.blade.php -->

<!-- Chat Interface -->
<div class="chat-container">
    <div id="chatMessages"></div>
    <input type="text" id="chatInput" placeholder="Type message...">
    <button id="sendBtn">Send</button>
</div>

<!-- Voice Interface -->
<div class="voice-container">
    <button id="recordBtn">Start Recording</button>
    <button id="stopBtn" style="display:none;">Stop Recording</button>
    <audio id="audioPlayer" controls></audio>
</div>

<script>
    // Chat functionality
    document.getElementById('sendBtn').addEventListener('click', async () => {
        const message = document.getElementById('chatInput').value;
        const response = await fetch('{{ route("ai-agent.chat") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message, language: 'ur' })
        });
        const data = await response.json();
        console.log(data);
    });
</script>
```

---

## 🎯 Supported Intents

| Intent | Keywords | Action |
|--------|----------|--------|
| stock | stock, inventory, aaya, add, update | Stock management |
| employee | employee, worker, naya, hire | Employee operations |
| salary | salary, wage, pay, bonus | Salary calculations |
| check | check, kitna, available, status | Status queries |
| general | any other | AI response |

---

## 🌍 Language Support

| Code | Language | Voice | Example |
|------|----------|-------|---------|
| ur | Urdu | ur-PK-Neural2-A | "Stock update karo" |
| en | English | en-US-Neural2-C | "Update stock" |
| hi | Hindi | hi-IN-Neural2-A | "स्टॉक अपडेट करो" |
| pa | Punjabi | pa-IN-Neural2-A | "ਸਟਾਕ ਅਪਡੇਟ ਕਰੋ" |

---

## 📊 Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Laravel App                          │
│  (routes/web.php, AIAgentController)                   │
└────────────────────┬────────────────────────────────────┘
                     │ HTTP Requests
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Python FastAPI Agent                       │
│  (main.py - http://localhost:8000)                     │
├─────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Voice Agent  │  │ Chat Agent   │  │ TTS Agent    │  │
│  │ (STT)        │  │ (Groq API)   │  │ (Google)     │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
├─────────────────────────────────────────────────────────┤
│  ┌──────────────────────────────────────────────────┐   │
│  │ Utils: Language Detection, Response Formatting  │   │
│  └──────────────────────────────────────────────────┘   │
└────────────────────┬────────────────────────────────────┘
                     │
        ┌────────────┼────────────┐
        ▼            ▼            ▼
   Google Cloud  Groq API    Database
   (Speech-to-   (LLM)       (Logging)
    Text, TTS)
```

---

## 🔐 Security

1. **API Authentication**: All endpoints require CSRF token
2. **Audio Processing**: Files are processed and deleted
3. **No Data Storage**: Audio not permanently stored
4. **CORS**: Configured for development
5. **Rate Limiting**: Implement in production

---

## 🐛 Troubleshooting

### Issue: "Google credentials not found"
```bash
# Solution: Set environment variable
set GOOGLE_APPLICATION_CREDENTIALS=C:\path\to\credentials.json
```

### Issue: "Groq API key invalid"
```bash
# Solution: Verify key in .env
GROQ_API_KEY=gsk_xxxxxxxxxxxxx
```

### Issue: "Port 8000 already in use"
```bash
# Solution: Use different port
python main.py --port 8001
```

### Issue: "Audio format not supported"
```bash
# Solution: Use WAV format with 16000 Hz sample rate
ffmpeg -i input.mp3 -acodec pcm_s16le -ar 16000 output.wav
```

---

## 📈 Performance Tips

1. **Use WAV format** for better accuracy
2. **Keep audio < 25MB** for faster processing
3. **Cache responses** for repeated queries
4. **Use default language** for faster detection
5. **Batch process** multiple requests

---

## 🚀 Production Deployment

### Using Gunicorn
```bash
pip install gunicorn
gunicorn -w 4 -b 0.0.0.0:8000 main:app
```

### Using Docker
```dockerfile
FROM python:3.9
WORKDIR /app
COPY requirements.txt .
RUN pip install -r requirements.txt
COPY . .
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
```

### Environment Variables (Production)
```env
DEBUG=False
HOST=0.0.0.0
PORT=8000
GOOGLE_APPLICATION_CREDENTIALS=/secrets/credentials.json
GROQ_API_KEY=your_production_key
LARAVEL_API_URL=https://your-domain.com/api
```

---

## 📞 Support & Documentation

- **API Docs**: http://localhost:8000/docs
- **ReDoc**: http://localhost:8000/redoc
- **GitHub**: [Your repo]
- **Issues**: Report in GitHub Issues

---

## 📝 Next Steps

1. ✅ Install Python dependencies
2. ✅ Get Google Cloud credentials
3. ✅ Get Groq API key
4. ✅ Configure .env file
5. ✅ Start Python agent
6. ✅ Test endpoints
7. ✅ Integrate with Laravel
8. ✅ Deploy to production

---

## 📄 License

Proprietary - Jewellery Management System

---

**Created**: 2024
**Version**: 1.0.0
**Status**: Production Ready

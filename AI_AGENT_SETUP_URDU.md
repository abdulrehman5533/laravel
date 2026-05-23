# AI Agent Setup Guide - مکمل رہنمائی

## 🚀 فوری شروعات (5 منٹ میں)

### Step 1: Groq API Key حاصل کریں (FREE)

1. یہاں جائیں: https://console.groq.com/keys
2. Sign up کریں (یا login کریں)
3. API Key کاپی کریں
4. اپنے `.env` فائل میں شامل کریں:

```
GROQ_API_KEY=gsk_your_key_here
```

### Step 2: Database میں ضروری ڈیٹا شامل کریں

```bash
php artisan migrate
php artisan db:seed --class=DatabaseSeeder
```

### Step 3: AI Agent کو ٹیسٹ کریں

براؤزر میں جائیں:
```
http://localhost:8000/ai-agent/chat
```

---

## 📋 AI Agent کی خصوصیات

### 1. **Chat Processing** 💬
- اردو اور انگریزی میں سوالات کے جوابات
- Groq API استعمال کرتے ہوئے (بہت تیز!)
- مختصر اور عملی جوابات

### 2. **Inventory Management** 📦
```
صارف: "Ring کا اسٹاک کتنا ہے؟"
AI: "Ring: 5 units موجود ہیں"

صارف: "Gold Wedding Ring میں 10 units شامل کریں"
AI: "✓ اسٹاک اپڈیٹ ہو گیا! کل: 15 units"
```

### 3. **Sales Tracking** 💰
```
صارف: "آج کی فروخت کتنی ہے؟"
AI: "آج کی فروخت: Rs. 52,000"
```

### 4. **Employee Management** 👥
```
صارف: "ملازمین کی فہرست دکھائیں"
AI: "ملازمین:
- Rajesh Kumar (Manager) - Rs. 50,000
- Priya Sharma (Sales) - Rs. 30,000"
```

### 5. **Reports** 📊
```
صارف: "کاروباری خلاصہ دیں"
AI: "کل گاہک: 2
کل مصنوعات: 1
کل فروخت: Rs. 181,000
کل ملازمین: 0"
```

### 6. **Gold Rate** 🏆
```
صارف: "سونے کی قیمت کیا ہے؟"
AI: "22K: Rs. 5,500
24K: Rs. 6,000
18K: Rs. 4,500"
```

---

## 🎤 Voice Features (اختیاری)

### Voice-to-Text (Google Speech-to-Text)
1. Google Cloud Console میں جائیں
2. Speech-to-Text API enable کریں
3. Service Account بنائیں
4. JSON key ڈاؤن لوڈ کریں
5. `.env` میں شامل کریں:

```
GOOGLE_APPLICATION_CREDENTIALS=/path/to/service-account-key.json
```

### Text-to-Speech (Google Text-to-Speech)
- اسی Google Cloud account میں Text-to-Speech API enable کریں
- خودکار طور پر کام کرے گا

---

## 🔧 Advanced Configuration

### Custom Intents شامل کریں

`app/Services/AdvancedAIAgentService.php` میں `detectIntent()` method میں:

```php
// نیا intent شامل کریں
if (preg_match('/(your_keyword|دوسری_کلید)/i', $msg)) {
    return ['type' => 'your_intent', 'action' => 'your_action'];
}

// پھر handler شامل کریں
case 'your_intent':
    return $this->handleYourIntent($message);
```

### Custom Handler بنائیں

```php
private function handleYourIntent($message)
{
    // آپ کی logic یہاں
    return [
        'status' => 'success',
        'data' => [
            'answer' => 'آپ کا جواب'
        ]
    ];
}
```

---

## 🌐 Multi-Language Support

موجودہ زبانیں:
- 🇵🇰 اردو (ur)
- 🇬🇧 English (en)
- 🇮🇳 हिंदी (hi)
- 🇮🇳 ਪੰਜਾਬੀ (pa)

نئی زبان شامل کرنے کے لیے:

1. `resources/views/ai-agent/chat.blade.php` میں language button شامل کریں
2. `AIAgentChatController` میں language validation میں شامل کریں
3. `AdvancedAIAgentService` میں Groq prompt میں ترجمہ شامل کریں

---

## 📱 API Endpoints

### Chat Message
```
POST /ai-agent/chat/message
Content-Type: application/json

{
    "message": "آپ کا سوال",
    "language": "ur"
}
```

### Voice to Chat
```
POST /ai-agent/chat/voice-to-chat
Content-Type: multipart/form-data

file: audio.wav
language: ur
```

### Voice to Voice (مکمل automation)
```
POST /ai-agent/chat/voice-to-voice
Content-Type: multipart/form-data

file: audio.wav
language: ur
```

### Agent Status
```
GET /ai-agent/chat/status
```

### Supported Languages
```
GET /ai-agent/chat/languages
```

---

## 🐛 Troubleshooting

### مسئلہ: "GROQ_API_KEY not set"
**حل:** 
```bash
# .env میں شامل کریں
GROQ_API_KEY=gsk_your_key_here

# Cache صاف کریں
php artisan config:cache
```

### مسئلہ: "Connection timeout"
**حل:**
- Groq API status چیک کریں: https://status.groq.com
- اپنا internet connection چیک کریں
- Firewall settings چیک کریں

### مسئلہ: "No response from AI"
**حل:**
- API rate limit چیک کریں (30 requests/minute)
- Message length چیک کریں (max 1000 characters)
- Server logs دیکھیں: `storage/logs/laravel.log`

---

## 📊 Performance Tips

1. **Caching شامل کریں:**
```php
$result = Cache::remember("ai_response_{$message}", 3600, function() {
    return $this->aiService->processMessage($message);
});
```

2. **Queue استعمال کریں:**
```php
dispatch(new ProcessAIMessage($message))->onQueue('ai');
```

3. **Rate Limiting:**
```php
Route::middleware('throttle:30,1')->post('/ai-agent/chat/message', ...);
```

---

## 🔐 Security

1. **API Key محفوظ رکھیں:**
   - `.env` فائل کو git میں commit نہ کریں
   - Production میں environment variables استعمال کریں

2. **Input Validation:**
   - تمام inputs validate کریں
   - SQL injection سے بچیں

3. **Rate Limiting:**
   - API calls کو محدود کریں
   - Abuse سے بچیں

---

## 📞 Support

مسائل کے لیے:
1. Logs دیکھیں: `storage/logs/laravel.log`
2. Groq API documentation: https://console.groq.com/docs
3. GitHub issues: اپنا issue report کریں

---

## 🎯 اگلے قدم

1. ✅ Groq API key حاصل کریں
2. ✅ `.env` میں شامل کریں
3. ✅ `/ai-agent/chat` پر جائیں
4. ✅ سوال پوچھیں اور جواب دیکھیں!

**خوش قسمتی! 🚀**

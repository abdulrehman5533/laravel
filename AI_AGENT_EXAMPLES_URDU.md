# AI Agent - استعمال کی مثالیں

## 1️⃣ سادہ Chat

### صارف کا سوال:
```
"Ring کا اسٹاک کتنا ہے؟"
```

### AI کا جواب:
```
Gold Wedding Ring
موجودہ اسٹاک: 5 units
قیمت: Rs. 52,000.00
```

---

## 2️⃣ اسٹاک اپڈیٹ

### صارف کا سوال:
```
"Gold Wedding Ring میں 10 units شامل کریں"
```

### AI کا جواب:
```
✓ اسٹاک اپڈیٹ ہو گیا!

Gold Wedding Ring
شامل کیے گئے: 10 units
کل: 15 units
```

---

## 3️⃣ فروخت کی معلومات

### صارف کا سوال:
```
"آج کی فروخت کتنی ہے؟"
```

### AI کا جواب:
```
فروخت کی معلومات:

آج کی فروخت: Rs. 52,000.00
کل فروخت: Rs. 181,000.00
کل لین دین: 3
```

---

## 4️⃣ ملازمین کی معلومات

### صارف کا سوال:
```
"ملازمین کی فہرست دکھائیں"
```

### AI کا جواب:
```
ملازمین:

Rajesh Kumar (Manager) - Rs. 50,000.00
Priya Sharma (Sales) - Rs. 30,000.00
```

---

## 5️⃣ کاروباری خلاصہ

### صارف کا سوال:
```
"کاروباری خلاصہ دیں"
```

### AI کا جواب:
```
کاروباری خلاصہ:

کل گاہک: 2
کل مصنوعات: 1
کل فروخت: Rs. 181,000.00
کل ملازمین: 0
```

---

## 6️⃣ سونے کی قیمت

### صارف کا سوال:
```
"سونے کی موجودہ قیمت کیا ہے؟"
```

### AI کا جواب:
```
سونے کی موجودہ قیمت:

22K: Rs. 5,500.00
24K: Rs. 6,000.00
18K: Rs. 4,500.00
```

---

## 7️⃣ عام سوالات

### صارف کا سوال:
```
"جوہری کاروبار میں کامیابی کے لیے کیا ضروری ہے؟"
```

### AI کا جواب (Groq API سے):
```
جوہری کاروبار میں کامیابی کے لیے:

1. معیار کی مصنوعات
2. صارفین کا اعتماد
3. منصفانہ قیمتیں
4. اچھی خدمت
5. مختلف ڈیزائن
```

---

## 🎤 Voice Features

### Voice Input (اردو میں بولیں):
```
صارف: "Ring کا اسٹاک کتنا ہے؟" (آواز میں)
```

### AI کا جواب (متن میں):
```
Gold Wedding Ring
موجودہ اسٹاک: 5 units
```

### Voice Output (اختیاری):
```
AI: "گولڈ ویڈنگ رنگ میں پانچ یونٹس موجود ہیں" (آواز میں)
```

---

## 💻 API استعمال

### JavaScript میں:
```javascript
// Chat message بھیجیں
fetch('/ai-agent/chat/message', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        message: "Ring کا اسٹاک کتنا ہے؟",
        language: "ur"
    })
})
.then(response => response.json())
.then(data => {
    console.log(data.data.answer);
});
```

### cURL میں:
```bash
curl -X POST http://localhost:8000/ai-agent/chat/message \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Ring کا اسٹاک کتنا ہے؟",
    "language": "ur"
  }'
```

### PHP میں:
```php
$aiService = app(\App\Services\AdvancedAIAgentService::class);

$result = $aiService->processMessage(
    "Ring کا اسٹاک کتنا ہے؟",
    "ur"
);

echo $result['data']['answer'];
```

---

## 🔄 Workflow

```
صارف کا سوال
    ↓
Intent Detection (کیا چاہتے ہو؟)
    ↓
Specific Handler یا General Query
    ↓
Database سے ڈیٹا لیں (اگر ضروری ہو)
    ↓
Groq API سے جواب لیں (اگر ضروری ہو)
    ↓
جواب دیں
```

---

## 🎯 Supported Intents

| Intent | Keywords | مثال |
|--------|----------|------|
| Inventory | stock, product, item, quantity | "Ring کا اسٹاک؟" |
| Sales | sale, sell, customer, purchase | "آج کی فروخت؟" |
| Employee | employee, worker, salary | "ملازمین کی فہرست" |
| Reports | report, summary, total | "کاروباری خلاصہ" |
| Gold Rate | gold, rate, price | "سونے کی قیمت؟" |
| General | کوئی بھی سوال | "کیسے ہو؟" |

---

## ⚙️ Configuration

### `.env` میں:
```
GROQ_API_KEY=gsk_your_key_here
```

### Routes (`routes/web.php`):
```php
Route::prefix('ai-agent')->name('ai-agent.')->group(function () {
    Route::get('/chat', [AIAgentChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/message', [AIAgentChatController::class, 'sendMessage'])->name('chat.message');
    Route::post('/chat/voice-to-chat', [AIAgentChatController::class, 'voiceToChat'])->name('chat.voice-to-chat');
    Route::get('/chat/status', [AIAgentChatController::class, 'getAgentStatus'])->name('chat.status');
    Route::get('/chat/languages', [AIAgentChatController::class, 'getSupportedLanguages'])->name('chat.languages');
});
```

---

## 🚀 شروع کریں

1. **Groq API Key حاصل کریں:**
   - https://console.groq.com/keys

2. **`.env` میں شامل کریں:**
   ```
   GROQ_API_KEY=gsk_your_key_here
   ```

3. **براؤزر میں کھولیں:**
   ```
   http://localhost:8000/ai-agent/chat
   ```

4. **سوال پوچھیں اور جواب دیکھیں!** 🎉

---

## 📝 نوٹس

- تمام جوابات اردو/انگریزی میں ہیں
- Groq API بہت تیز ہے (< 1 second)
- 30 requests/minute کی حد ہے
- Database سے براہ راست ڈیٹا لیا جاتا ہے
- کوئی بھی سوال پوچھ سکتے ہو!

---

**خوش قسمتی! 🎊**

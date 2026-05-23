# 🚀 AI Agent - فوری شروعات (5 منٹ)

## Step 1: Groq API Key حاصل کریں (FREE)

```
1. یہاں جائیں: https://console.groq.com/keys
2. Sign up کریں
3. API Key کاپی کریں
```

**مثال:**
```
gsk_abcdef123456789xyz
```

---

## Step 2: `.env` فائل میں شامل کریں

فائل کھولیں: `c:\xampp1\htdocs\jewellery-management-system\.env`

یہ لائن تلاش کریں:
```
GROQ_API_KEY=
```

اس میں اپنی key شامل کریں:
```
GROQ_API_KEY=gsk_abcdef123456789xyz
```

---

## Step 3: Cache صاف کریں

Command Prompt میں:
```bash
cd c:\xampp1\htdocs\jewellery-management-system
php artisan config:cache
```

---

## Step 4: AI Agent کھولیں

براؤزر میں جائیں:
```
http://localhost:8000/ai-agent/chat
```

---

## Step 5: سوال پوچھیں! 🎉

### مثالیں:

**اردو میں:**
```
"Ring کا اسٹاک کتنا ہے؟"
"آج کی فروخت کتنی ہے؟"
"ملازمین کی فہرست دکھائیں"
"کاروباری خلاصہ دیں"
"سونے کی قیمت کیا ہے؟"
```

**انگریزی میں:**
```
"What is the stock of Ring?"
"How much sales today?"
"Show employee list"
"Business summary"
"What is gold price?"
```

---

## ✅ کیا کام کرتا ہے؟

✅ Chat (سوالات کے جوابات)
✅ Inventory (اسٹاک معلومات)
✅ Sales (فروخت کی معلومات)
✅ Employees (ملازمین کی معلومات)
✅ Reports (کاروباری خلاصہ)
✅ Gold Rate (سونے کی قیمت)
✅ Multi-language (اردو، انگریزی، ہندی، پنجابی)

---

## 🎤 Voice Features (اختیاری)

### Microphone بٹن دبائیں:
1. 🎤 بٹن دبائیں
2. اپنی آواز میں سوال پوچھیں
3. 🛑 بٹن دبائیں
4. AI جواب دے گا

---

## 🔧 اگر کام نہ کرے

### مسئلہ 1: "GROQ_API_KEY not set"
```bash
# .env میں key شامل کریں
# پھر cache صاف کریں
php artisan config:cache
```

### مسئلہ 2: "Connection timeout"
```
- Internet connection چیک کریں
- Groq API status چیک کریں: https://status.groq.com
```

### مسئلہ 3: "No response"
```
- Logs دیکھیں: storage/logs/laravel.log
- API rate limit چیک کریں (30/minute)
```

---

## 📞 مزید معلومات

- Setup Guide: `AI_AGENT_SETUP_URDU.md`
- Examples: `AI_AGENT_EXAMPLES_URDU.md`
- Test Script: `php test-ai-agent.php`

---

## 🎯 اگلے قدم

1. ✅ Groq API key حاصل کریں
2. ✅ `.env` میں شامل کریں
3. ✅ `php artisan config:cache` چلائیں
4. ✅ `/ai-agent/chat` پر جائیں
5. ✅ سوال پوچھیں!

---

**بس! اب آپ کا AI Agent تیار ہے! 🚀**

کوئی سوال ہو تو پوچھیں۔

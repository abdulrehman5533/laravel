# 🤖 Advanced AI Agent Setup Guide

## مکمل AI System - اردو میں

یہ ایک مکمل AI system ہے جو آپ کی jewellery management app کو intelligent بناتا ہے۔

---

## 🚀 Quick Start

### Step 1: API Key حاصل کریں

#### Option A: Google Gemini (FREE - سفارش کی جاتی ہے)

1. یہاں جائیں: https://makersuite.google.com/app/apikey
2. "Create API Key" پر کلک کریں
3. API key کو کاپی کریں

#### Option B: OpenAI (PAID)

1. یہاں جائیں: https://platform.openai.com/api-keys
2. نیا API key بنائیں
3. API key کو کاپی کریں

### Step 2: .env میں شامل کریں

```bash
# Google Gemini (FREE)
AI_PROVIDER=gemini
GEMINI_API_KEY=your_api_key_here

# یا OpenAI (PAID)
AI_PROVIDER=openai
OPENAI_API_KEY=your_api_key_here
```

### Step 3: Application چلائیں

```bash
php artisan serve
```

### Step 4: AI Agent کھولیں

```
http://localhost:8000/ai-agent/chat
```

---

## 💡 استعمال کی مثالیں

### ملازمین کے ساتھ

```
نیا ملازم علی شامل کریں، سیلری 50000
تمام ملازمین دیکھیں
علی کی سیلری 60000 کریں
علی کو ہٹائیں
```

### پروڈکٹس کے ساتھ

```
سونے کی انگوٹھی شامل کریں، وزن 10، قیمت 50000
تمام پروڈکٹس دیکھیں
انگوٹھی کی قیمت 55000 کریں
انگوٹھی کو ہٹائیں
```

### کسٹمرز کے ساتھ

```
احمد کو کسٹمر کے طور پر شامل کریں
تمام کسٹمرز دیکھیں
احمد کا فون نمبر بدلیں
احمد کو ہٹائیں
```

### سیلز اور تجزیہ

```
100000 کا سیل بنائیں
آج کے سیلز دیکھیں
مجھے تجزیہ دیں
سونے کی قیمت کیا ہے
```

---

## 🎯 Features

✅ **Natural Language Processing** - اردو میں سمجھتا ہے
✅ **Database Integration** - براہ راست database میں save کرتا ہے
✅ **Real-time Updates** - فوری نتائج
✅ **Smart Intent Detection** - سمجھ جاتا ہے کہ آپ کیا چاہتے ہیں
✅ **Multi-language Support** - اردو اور انگریزی
✅ **Error Handling** - غلطیوں کو سنبھالتا ہے
✅ **Analytics** - مکمل تجزیہ

---

## 🔧 Configuration

### AI Provider کو تبدیل کریں

```bash
# .env میں
AI_PROVIDER=gemini  # یا openai
```

### Language تبدیل کریں

```javascript
// chat.blade.php میں
fetch('{{ route("ai-agent.chat.message") }}', {
    body: JSON.stringify({
        message: message,
        language: 'ur'  // 'ur' یا 'en'
    })
})
```

---

## 📊 Database Operations

AI یہ کام کر سکتا ہے:

### Employees
- ✅ شامل کریں
- ✅ حذف کریں
- ✅ اپڈیٹ کریں
- ✅ فہرست دیکھیں

### Products
- ✅ شامل کریں
- ✅ حذف کریں
- ✅ اپڈیٹ کریں
- ✅ فہرست دیکھیں

### Customers
- ✅ شامل کریں
- ✅ حذف کریں
- ✅ اپڈیٹ کریں
- ✅ فہرست دیکھیں

### Sales
- ✅ بنائیں
- ✅ ٹریک کریں
- ✅ تجزیہ دیں

---

## 🛠️ Troubleshooting

### API Key کام نہیں کر رہی

```bash
# .env میں چیک کریں
GEMINI_API_KEY=your_key_here

# یا
OPENAI_API_KEY=your_key_here
```

### Messages نہیں بھیج رہے

```bash
# CSRF token چیک کریں
# Laravel session چیک کریں
php artisan cache:clear
php artisan config:clear
```

### Database میں save نہیں ہو رہا

```bash
# Database connection چیک کریں
php artisan migrate
php artisan db:seed
```

---

## 📝 API Endpoints

```
POST /ai-agent/chat/message
GET /ai-agent/chat/status
GET /ai-agent/chat/languages
```

---

## 🔐 Security

- ✅ CSRF Protection
- ✅ Authentication Required
- ✅ Rate Limiting
- ✅ Input Validation
- ✅ SQL Injection Prevention

---

## 📞 Support

اگر کوئی مسئلہ ہو تو:

1. `.env` میں API key چیک کریں
2. Database connection چیک کریں
3. Laravel logs دیکھیں: `storage/logs/laravel.log`
4. Browser console میں errors دیکھیں

---

## 🎉 مبارک ہو!

آپ کا AI Agent تیار ہے۔ اب اسے استعمال کریں اور اپنے business کو automate کریں!

**Happy AI-ing! 🚀**

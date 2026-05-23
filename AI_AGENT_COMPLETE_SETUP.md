# 🚀 AI Agent - Complete Setup Guide

> **AI Agent کو مکمل طور پر کام کرانے کے لیے**

---

## 📋 Setup Steps

### Step 1: Database Migrations چلائیں

```bash
php artisan migrate
```

یہ دو tables بنائے گا:
- `notification_histories`
- `ai_agent_logs`

### Step 2: Service Providers Register کریں

`config/app.php` میں یہ شامل کریں (اگر ضروری ہو):

```php
'providers' => [
    // ... existing providers
    App\Providers\AIAgentServiceProvider::class,
],
```

### Step 3: Environment Variables شامل کریں

`.env` میں یہ شامل کریں:

```
# Groq API
GROQ_API_KEY=gsk_your_key_here

# Google Cloud
GOOGLE_CLOUD_API_KEY=your_google_key

# SMS Service
SMS_API_KEY=your_sms_key

# WhatsApp
WHATSAPP_API_KEY=your_whatsapp_key

# Caching
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Rate Limiting
AI_AGENT_RATE_LIMIT=60
AI_AGENT_RATE_LIMIT_DECAY=1

# Security
AI_AGENT_SECRET_KEY=your_secret_key
AI_AGENT_IP_WHITELIST=127.0.0.1
```

### Step 4: Cache صاف کریں

```bash
php artisan config:cache
php artisan route:cache
php artisan cache:clear
```

### Step 5: Routes Verify کریں

```bash
php artisan route:list | grep ai-agent
```

یہ تمام AI Agent routes دکھائے گا۔

---

## 🧪 Testing

### Test 1: Chat Message

```bash
curl -X POST http://localhost:8000/api/v1/ai-agent/message \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Ring کا اسٹاک کتنا ہے؟",
    "language": "ur"
  }'
```

### Test 2: Analytics

```bash
curl -X GET http://localhost:8000/api/v1/ai-agent/analytics/sales \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Test 3: Notifications

```bash
curl -X POST http://localhost:8000/api/v1/ai-agent/notifications/in-app \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "title": "Test Alert",
    "message": "یہ ایک ٹیسٹ ہے",
    "type": "info"
  }'
```

### Test 4: Voice

```bash
curl -X POST http://localhost:8000/api/v1/ai-agent/voice/transcribe \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "audio=@audio.wav" \
  -F "language=ur"
```

---

## 🔍 Troubleshooting

### مسئلہ 1: "Class not found" Error

**حل:**
```bash
composer dump-autoload
php artisan cache:clear
```

### مسئلہ 2: "Route not found" Error

**حل:**
```bash
php artisan route:cache
php artisan route:clear
```

### مسئلہ 3: "Database table not found" Error

**حل:**
```bash
php artisan migrate
php artisan migrate:refresh
```

### مسئلہ 4: "Rate limit exceeded" Error

**حل:**
```bash
# .env میں بڑھائیں
AI_AGENT_RATE_LIMIT=100
```

### مسئلہ 5: "API Key not set" Error

**حل:**
```bash
# .env میں شامل کریں
GROQ_API_KEY=gsk_your_key_here

# Cache صاف کریں
php artisan config:cache
```

---

## 📊 Monitoring

### Logs دیکھیں

```bash
tail -f storage/logs/laravel.log
```

### Database Logs دیکھیں

```bash
php artisan tinker
> \App\Models\AIAgentLog::latest()->limit(10)->get();
```

### Statistics دیکھیں

```bash
php artisan tinker
> \App\Services\AIAgentLoggingService::getStatistics(30);
```

---

## 🎯 API Endpoints

### Chat
```
POST /api/v1/ai-agent/message
```

### Analytics
```
GET /api/v1/ai-agent/analytics/sales
GET /api/v1/ai-agent/analytics/inventory
GET /api/v1/ai-agent/analytics/customers
GET /api/v1/ai-agent/analytics/employees
GET /api/v1/ai-agent/analytics/purchases
GET /api/v1/ai-agent/analytics/services
GET /api/v1/ai-agent/analytics/financial
GET /api/v1/ai-agent/analytics/dashboard
GET /api/v1/ai-agent/analytics/trends
```

### Notifications
```
POST /api/v1/ai-agent/notifications/email
POST /api/v1/ai-agent/notifications/sms
POST /api/v1/ai-agent/notifications/in-app
POST /api/v1/ai-agent/notifications/whatsapp
POST /api/v1/ai-agent/notifications/bulk
GET  /api/v1/ai-agent/notifications/user
POST /api/v1/ai-agent/notifications/mark-read
```

### Voice
```
POST /api/v1/ai-agent/voice/transcribe
POST /api/v1/ai-agent/voice/synthesize
POST /api/v1/ai-agent/voice/detect-language
```

### Status
```
GET /api/v1/ai-agent/chat/status
GET /api/v1/ai-agent/chat/languages
```

---

## 🔐 Security

### Rate Limiting
- 60 requests per minute per user
- Configurable in .env

### Input Validation
- تمام inputs validate ہوتے ہیں
- Malicious content detect ہوتا ہے

### Data Encryption
- Sensitive data encrypt ہوتا ہے
- API tokens secure ہوتے ہیں

---

## ⚡ Performance

### Caching
- Analytics data cache ہوتا ہے (30 minutes)
- Notifications cache ہوتے ہیں (10 minutes)
- Intent detection cache ہوتی ہے (1 hour)

### Database Optimization
- Indexes موجود ہیں
- Queries optimized ہیں
- Pagination implemented ہے

---

## 📝 Logging

### Activity Logging
- تمام requests log ہوتی ہیں
- Processing time track ہوتا ہے
- Errors log ہوتی ہیں

### Statistics
- Success rate track ہوتی ہے
- Most used intents track ہوتے ہیں
- Language distribution track ہوتی ہے

---

## 🚀 Production Deployment

### Before Deployment
- [ ] تمام migrations run کریں
- [ ] API keys configure کریں
- [ ] Cache configure کریں
- [ ] Security headers set کریں
- [ ] SSL certificate install کریں

### After Deployment
- [ ] تمام endpoints test کریں
- [ ] Monitoring setup کریں
- [ ] Backups configure کریں
- [ ] Logs monitor کریں

---

## 📞 Support

### Documentation Files
- `AI_AGENT_COMPLETE_IMPLEMENTATION.md` - تمام features
- `DEPLOYMENT_GUIDE.md` - deployment guide
- `AI_AGENT_SETUP_URDU.md` - اردو میں setup

### Logs
```
storage/logs/laravel.log
```

### Database
```
notification_histories table
ai_agent_logs table
```

---

## ✅ Checklist

- [ ] Migrations run ہو گئے
- [ ] Environment variables set ہو گئے
- [ ] Routes registered ہو گئے
- [ ] Services working ہیں
- [ ] API endpoints working ہیں
- [ ] Caching working ہے
- [ ] Logging working ہے
- [ ] Security enabled ہے
- [ ] Tests passing ہیں
- [ ] Monitoring active ہے

---

**🎉 AI Agent اب مکمل طور پر کام کر رہا ہے!**

**خوش قسمتی! 🚀**

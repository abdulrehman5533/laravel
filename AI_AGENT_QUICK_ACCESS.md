# 🎯 AI Agent - Quick Access Guide

## 🚀 Dashboard URLs

### Laravel Web Interface
```
http://localhost:8080/ai-agent
```
**Features:**
- Chat Assistant (Multi-language)
- Voice Assistant (Recording)
- Full Automation (Voice → Chat → Voice)
- Real-time agent status

### Python API Server
```
http://localhost:8000
```

### Swagger API Documentation
```
http://localhost:8000/docs
```

### ReDoc Documentation
```
http://localhost:8000/redoc
```

---

## 📡 API Endpoints (Direct Access)

### Chat Endpoint
```
POST http://localhost:8000/api/chat/message
```

**Example:**
```bash
curl -X POST http://localhost:8000/api/chat/message \
  -H "Content-Type: application/json" \
  -d "{\"message\": \"Stock update karo\", \"language\": \"ur\"}"
```

### Agent Status
```
GET http://localhost:8000/api/info/status
```

### Supported Languages
```
GET http://localhost:8000/api/info/languages
```

---

## 🔗 Laravel Routes

### Web Interface
```
GET /ai-agent
```

### API Routes
```
POST /ai-agent/chat              # Chat processing
POST /ai-agent/voice             # Voice processing
POST /ai-agent/voice-to-chat     # Voice to chat
POST /ai-agent/synthesize        # Text to speech
POST /ai-agent/voice-to-voice    # Full automation
GET  /ai-agent/status            # Agent status
GET  /ai-agent/languages         # Languages
```

---

## 💬 Chat Examples

### Urdu
```
"Stock update karo"
"Employee add karo"
"Salary calculate karo"
"Kitna stock hai?"
```

### English
```
"Update stock"
"Add new employee"
"Calculate salary"
"How much stock?"
```

### Hindi
```
"स्टॉक अपडेट करो"
"नया कर्मचारी जोड़ो"
"वेतन की गणना करो"
```

### Punjabi
```
"ਸਟਾਕ ਅਪਡੇਟ ਕਰੋ"
"ਨਵਾ ਕਰਮਚਾਰੀ ਜੋੜੋ"
"ਤਨਖਾਹ ਦੀ ਗਣਨਾ ਕਰੋ"
```

---

## 🎯 Features Available

### ✅ Chat Interface
- Multi-language support
- Real-time responses
- Intent detection
- Language auto-detection

### ✅ Voice Interface (Optional)
- Voice recording
- Speech-to-text
- Text-to-speech
- Full automation

### ✅ Agent Status
- Real-time status indicator
- Online/Offline detection
- Health check

---

## 🔧 Configuration

### Python Agent (.env)
```
GROQ_API_KEY=caa03cea90a12630e4c6a5493e408f5a
PYTHON_AI_AGENT_URL=http://localhost:8000/api
DEFAULT_LANGUAGE=ur
```

### Laravel (.env)
```
PYTHON_AI_AGENT_URL=http://localhost:8000/api
```

---

## 📊 Dashboard Components

### 1. Chat Assistant
- **Location:** Left panel
- **Features:** Text input, language selection, message history
- **Languages:** Urdu, English, Hindi, Punjabi

### 2. Voice Assistant
- **Location:** Right panel
- **Features:** Record button, transcription display, audio playback
- **Status:** Real-time recording indicator

### 3. Full Automation
- **Location:** Bottom panel
- **Features:** File upload, language selection, result display
- **Process:** Voice → Chat → Voice

### 4. Agent Status
- **Location:** Top right
- **Indicator:** Green (Online) / Red (Offline)
- **Updates:** Every 30 seconds

---

## 🚀 Quick Start

### 1. Start Python Agent
```bash
cd python-ai-agent
python main.py
```

### 2. Access Dashboard
```
http://localhost:8080/ai-agent
```

### 3. Test Chat
- Type message in chat box
- Select language
- Click send button

### 4. Test Voice (Optional)
- Click "Start Recording"
- Speak your command
- Click "Stop Recording"
- Wait for response

---

## 🧪 Testing

### Test Chat via API
```bash
curl -X POST http://localhost:8000/api/chat/message \
  -H "Content-Type: application/json" \
  -d "{\"message\": \"Stock update karo\", \"language\": \"ur\"}"
```

### Test Status
```bash
curl http://localhost:8000/api/info/status
```

### Test Languages
```bash
curl http://localhost:8000/api/info/languages
```

---

## 📱 Mobile Friendly

The dashboard is fully responsive:
- Desktop: Full layout with all features
- Tablet: Stacked layout
- Mobile: Single column layout

---

## 🔐 Security

- CSRF protection enabled
- API key secured in .env
- Input validation enabled
- Error handling implemented

---

## 📞 Support

- **API Docs:** http://localhost:8000/docs
- **Dashboard:** http://localhost:8080/ai-agent
- **Status Check:** http://localhost:8000/api/info/status

---

## ✨ Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| Enter | Send chat message |
| Ctrl+L | Clear chat |
| Ctrl+R | Record voice |
| Esc | Stop recording |

---

## 🎉 You're All Set!

Your AI Agent dashboard is ready to use:

1. **Chat** - Type messages in any language
2. **Voice** - Record and process voice commands
3. **Automation** - Full voice-to-voice processing
4. **Status** - Real-time agent monitoring

**Start using it now!**

```
http://localhost:8080/ai-agent
```

---

**Version:** 1.0.0
**Status:** ✅ Ready
**Last Updated:** 2024

# 🤖 AI AGENT - QUICK REFERENCE

## 🎯 VOICE COMMANDS (Urdu/English Mix)

### Stock Management
- "Mujhe 100 units gold aaya hai"
- "Stock update karo - 50 pieces silver"
- "Inventory mein 200 units add karo"
- "Kitna gold stock hai?"
- "Silver inventory check karo"

### Employee Management
- "Naya employee add karo - Ahmed, Manager, 50000 salary"
- "Employee add karo - Fatima, Designer, 40000"
- "New staff - Ali, Karigar, 35000"
- "Ahmed ka salary calculate karo"
- "Fatima ko 5000 bonus do"

### Salary & Payroll
- "Salary calculate karo - Ahmed"
- "Payroll generate karo"
- "Bonus add karo - 10000"
- "Tax calculate karo"

### Inventory Queries
- "Sab products ka stock batao"
- "Low stock items check karo"
- "Warehouse inventory check karo"
- "Product availability check karo"

---

## 💬 CHAT COMMANDS

Same as voice commands but type them instead of speaking.

---

## 🔑 FREE API KEYS NEEDED

1. **Groq API** (BEST)
   - https://console.groq.com/keys
   - 30 requests/minute FREE

2. **Google Speech-to-Text**
   - https://cloud.google.com/speech-to-text
   - 60 minutes/month FREE

3. **AssemblyAI** (Backup)
   - https://www.assemblyai.com/
   - 100 minutes/month FREE

---

## ⚙️ SETUP IN 3 STEPS

1. Get API keys from above links
2. Add to `.env` file:
   ```
   GROQ_API_KEY=your_key_here
   GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json
   ASSEMBLYAI_API_KEY=your_key_here
   ```

3. Access: http://127.0.0.1:8000/ai-agent

---

## 🚀 WHAT IT DOES

✅ Listens to voice (Urdu/English)
✅ Understands commands
✅ Updates stock automatically
✅ Adds employees automatically
✅ Calculates salary automatically
✅ Checks inventory automatically
✅ Responds in voice (optional)
✅ Works on mobile too

---

## 📊 RESPONSE EXAMPLES

**Input:** "Naya employee add karo - Ahmed, Manager, 50000"

**Output:**
```
✓ Employee add ho gaya!
Ahmed (Manager) - ID: 1
Salary: Rs. 50,000
```

**Input:** "Mujhe 100 units gold aaya hai"

**Output:**
```
✓ Stock update ho gaya!
Gold - 100 units add kiye gaye
Total: 500 units
```

---

## 🎤 HOW TO USE VOICE

1. Click "Start Recording" button
2. Speak in Urdu or English
3. Click "Stop Recording"
4. Agent processes automatically
5. See response below

---

## 💡 TIPS

- Mix Urdu and English (Hinglish)
- Be clear with numbers
- Mention product names clearly
- Include salary amounts
- Specify employee positions

---

## ❌ TROUBLESHOOTING

**Voice not working?**
- Allow microphone permission
- Use Chrome/Edge browser
- Check internet connection

**API errors?**
- Verify API keys in .env
- Check rate limits
- Restart Laravel server

**Database errors?**
- Run: php artisan migrate:fresh --force
- Check MySQL is running

---

## 📞 SUPPORT

Check logs: `storage/logs/laravel.log`

---

**Ready to automate? Go to:** http://127.0.0.1:8000/ai-agent 🚀

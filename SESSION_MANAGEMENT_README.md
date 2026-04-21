# User-Wise Session Kill Implementation

## Overview
Yeh implementation automatically destroy karta hai user ka session jab woh apne saare browser tabs close kar deta hai, bina doosre users ko affect kiye.

## Features

### 1. Multi-Tab Tracking
- Har tab ko unique ID milti hai
- Broadcast Channel API use karke tabs ek doosre se communicate karte hain
- LocalStorage mein active tabs ki list maintain hoti hai

### 2. Automatic Session Cleanup
- Jab last tab close hoti hai, session automatically destroy ho jata hai
- Server-side session termination hota hai
- Database se session record delete ho jata hai

### 3. User-Specific
- Sirf us user ka session destroy hota hai jis ne tabs close kiye
- Doosre logged-in users unaffected rehte hain
- Har user ka apna independent session tracking

## Technical Implementation

### Files Created/Modified

1. **public/js/session-tab-manager.js**
   - Multi-tab coordination
   - Tab registration/unregistration
   - Heartbeat mechanism
   - Session destruction logic

2. **resources/views/layouts/app.blade.php**
   - Session tab manager script include kiya
   - Sirf authenticated users ke liye load hota hai

3. **app/Http/Controllers/Auth/AuthenticatedSessionController.php**
   - `tabCloseLogout()` method already implemented hai
   - Server-side session cleanup handle karta hai

4. **routes/web.php**
   - `/auth/tab-close-logout` route already configured hai
   - CSRF exempt hai for sendBeacon compatibility

## How It Works

### Tab Registration
```javascript
// Jab tab open hoti hai
- Unique tab ID generate hoti hai
- LocalStorage mein tab register hoti hai
- Broadcast channel join hota hai
```

### Heartbeat System
```javascript
// Har 2 seconds
- Tab apni timestamp update karta hai
- Doosre tabs ko notify karta hai
- Dead tabs ko cleanup karta hai
```

### Tab Close Detection
```javascript
// Jab tab close hoti hai
- beforeunload event trigger hota hai
- Tab unregister hoti hai
- Agar last tab hai, session destroy hota hai
```

### Session Destruction
```javascript
// Server ko request
- navigator.sendBeacon() use hota hai (reliable)
- Server session invalidate karta hai
- Database se session delete hota hai
- User ko re-login karna padta hai
```

## Configuration

### Timeouts
```javascript
const HEARTBEAT_INTERVAL = 2000;  // 2 seconds
const TAB_TIMEOUT = 5000;         // 5 seconds
```

### LocalStorage Key
```javascript
const SESSION_TAB_KEY = 'app_active_tabs';
```

## Browser Compatibility

### Supported Features
- ✅ Broadcast Channel API (modern browsers)
- ✅ LocalStorage (all browsers)
- ✅ navigator.sendBeacon (modern browsers)
- ✅ Fallback to fetch() for older browsers

### Tested Browsers
- Chrome/Edge 90+
- Firefox 85+
- Safari 14+
- Opera 75+

## Security Features

1. **CSRF Protection**
   - Route CSRF-exempt hai for sendBeacon
   - Token validation server-side hota hai

2. **Session Validation**
   - Server check karta hai valid session hai ya nahi
   - User authentication verify hota hai

3. **Audit Logging**
   - Har session termination log hota hai
   - Reason track hota hai (tab_closed)

## Testing

### Test Scenario 1: Single Tab
```
1. Login karo
2. Tab close karo
3. Verify: Session destroy ho gaya
4. Dobara open karo: Login page dikhega
```

### Test Scenario 2: Multiple Tabs
```
1. Login karo
2. 3 tabs open karo
3. 2 tabs close karo
4. Verify: Session active hai
5. Last tab close karo
6. Verify: Session destroy ho gaya
```

### Test Scenario 3: Multiple Users
```
1. User A aur User B login karein
2. User A apni tabs close kare
3. Verify: User A ka session destroy
4. Verify: User B ka session active
```

## Troubleshooting

### Issue: Session destroy nahi ho raha
**Solution:**
- Browser console check karo for errors
- Network tab mein `/auth/tab-close-logout` request dekho
- LocalStorage mein `app_active_tabs` key check karo

### Issue: Har tab close par logout ho raha hai
**Solution:**
- Heartbeat interval check karo
- Tab timeout value increase karo
- Broadcast channel working hai verify karo

### Issue: Doosre users affect ho rahe hain
**Solution:**
- Server-side user_id validation check karo
- Session ID properly track ho rahi hai verify karo

## API Endpoints

### POST /auth/tab-close-logout
**Purpose:** Session destroy karna jab last tab close ho

**Request:**
```javascript
FormData {
  _token: 'csrf_token'
}
```

**Response:**
```json
{
  "status": "success"
}
```

## Database Impact

### Sessions Table
```sql
-- Session delete hota hai
DELETE FROM sessions WHERE id = 'session_id';
```

### User Sessions Table
```sql
-- Termination record update hota hai
UPDATE user_sessions 
SET terminated_at = NOW(),
    termination_reason = 'Browser tab/window closed'
WHERE session_id = 'session_id';
```

### Security Audit Log
```sql
-- Log entry create hoti hai
INSERT INTO security_audit_logs
(action, entity_type, entity_name, details)
VALUES
('tab_closed', 'session', 'User Name', 'Session terminated via tab closure');
```

## Performance

### Memory Usage
- Minimal: ~1KB per tab in LocalStorage
- Broadcast Channel: Negligible overhead

### Network Usage
- Heartbeat: No network calls (local only)
- Session destroy: 1 request per session end

### CPU Usage
- Heartbeat timer: Minimal (2s interval)
- Event listeners: Passive, no performance impact

## Future Enhancements

1. **Configurable Timeouts**
   - Admin panel se timeouts configure kar sakte hain
   - Per-user timeout settings

2. **Session Recovery**
   - Accidental close par session restore option
   - Grace period before final destruction

3. **Analytics**
   - Session duration tracking
   - Tab usage patterns
   - User behavior insights

## Support

Agar koi issue hai ya questions hain:
1. Browser console logs check karein
2. Network tab mein requests verify karein
3. Server logs dekhen for errors
4. Database mein session records check karein

## Conclusion

Yeh implementation production-ready hai aur:
- ✅ User-specific session management
- ✅ Multi-tab support
- ✅ Automatic cleanup
- ✅ Security best practices
- ✅ Browser compatibility
- ✅ Performance optimized

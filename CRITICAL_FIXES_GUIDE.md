# 🔧 MAGIA LUPOS - CRITICAL FIXES GUIDE

## FIX #1: Secure .env Configuration

### Step 1: Create .env.example
```bash
# Copy current .env to .env.example and remove sensitive values
cp .env .env.example
```

### Step 2: Update .env.example
```env
APP_NAME="MAGIA LUPOS Jewellery Management System (JMS)"
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Karachi

SHOP_NAME="MAGIA LUPOS Jewellery Management System (JMS)"
SHOP_NTN="1234567-8"
SHOP_GST="12-34-5678-901-23"
SHOP_ADDRESS="Your Address"
SHOP_PHONE="+92 300 1234567"
SHOP_EMAIL="info@yourdomain.com"

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jewellery_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=false
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
SESSION_PATH=/
SESSION_DOMAIN=
SESSION_TABLE=sessions

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

VITE_APP_NAME="${APP_NAME}"

# API Keys - Use environment variables in production
OPENAI_API_KEY=
DEEPSEEK_API_KEY=
```

### Step 3: Update .gitignore
```bash
# Add to .gitignore if not present
echo ".env" >> .gitignore
echo ".env.local" >> .gitignore
echo ".env.*.local" >> .gitignore
```

---

## FIX #2: Enable Security Middleware

### File: `app/Http/Kernel.php`

Replace the disabled middleware section:

```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // SECURITY MIDDLEWARE - MUST BE ENABLED
        \App\Http\Middleware\EnforceActiveSession::class,
        \App\Http\Middleware\EnforceIdleTimeout::class,
        \App\Http\Middleware\ValidateSessionInDatabase::class,
    ],
    // ... rest of middleware groups
];
```

---

## FIX #3: Fix CSRF Protection on Logout

### File: `routes/web.php`

Replace:
```php
Route::post('api/tab-close-logout', [\\App\\Http\\Controllers\\Auth\\AuthenticatedSessionController::class, 'tabCloseLogout'])
    ->name('logout.tab-close')
    ->withoutMiddleware([\\Illuminate\\Foundation\\Http\\Middleware\\VerifyCsrfToken::class]);
```

With:
```php
Route::post('api/tab-close-logout', [\\App\\Http\\Controllers\\Auth\\AuthenticatedSessionController::class, 'tabCloseLogout'])
    ->name('logout.tab-close')
    ->middleware('auth');
    // CSRF protection is automatically applied
```

---

## FIX #4: Secure Session Configuration

### File: `.env`

Change:
```env
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=false
```

To:
```env
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict
```

---

## FIX #5: Disable Debug Mode

### File: `.env`

Change:
```env
APP_ENV=local
APP_DEBUG=true
```

To:
```env
APP_ENV=production
APP_DEBUG=false
```

---

## FIX #6: Add Input Validation

### Create: `app/Http/Requests/ValidatedRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidatedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Sanitize all inputs
        $this->merge([
            'email' => strtolower(trim($this->email ?? '')),
            'phone' => preg_replace('/[^0-9+\-\s]/', '', $this->phone ?? ''),
        ]);
    }
}
```

### Update: `app/Http/Controllers/Auth/RegisteredUserController.php`

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
        'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users'],
        'password' => [
            'required',
            'string',
            'min:12',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]+$/',
            'confirmed',
        ],
    ], [
        'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
    ]);

    // ... rest of code
}
```

---

## FIX #7: Add Rate Limiting

### File: `routes/auth.php`

```php
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:5,1') // 5 attempts per minute
    ->name('login');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('throttle:3,1') // 3 attempts per minute
    ->name('register');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('throttle:3,1') // 3 attempts per minute
    ->name('password.email');
```

### File: `routes/api.php`

```php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('branches', [BranchController::class, 'store']);
    Route::get('gold-rate', [DashboardController::class, 'getGoldRateApi']);
});
```

---

## FIX #8: Protect Unprotected Routes

### File: `routes/web.php`

Replace:
```php
Route::get('/test-bi', function () {
    try {
        return (new \\App\\Services\\BusinessIntelligenceService())->getProfitMargins();
    } catch (\\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
    }
});
```

With:
```php
Route::get('/test-bi', function () {
    try {
        return (new \\App\\Services\\BusinessIntelligenceService())->getProfitMargins();
    } catch (\\Exception $e) {
        return [
            'error' => 'An error occurred',
            'message' => config('app.debug') ? $e->getMessage() : 'Contact support',
        ];
    }
})->middleware(['auth', 'permission:admin.view']);
```

---

## FIX #9: Add File Upload Validation

### Create: `app/Http/Requests/FileUploadRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'File size must not exceed 10MB',
            'file.mimes' => 'Only PDF, DOC, XLS, and image files are allowed',
        ];
    }
}
```

---

## FIX #10: Add Comprehensive Error Handling

### Create: `app/Exceptions/Handler.php`

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        // Log all exceptions
        \Log::error('Exception: ' . $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'user_id' => auth()->id(),
            'url' => $request->url(),
        ]);

        // Don't expose sensitive information in production
        if (config('app.debug') === false) {
            if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['error' => 'Resource not found'], 404);
            }

            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json(['errors' => $exception->errors()], 422);
            }

            return response()->json(['error' => 'An error occurred'], 500);
        }

        return parent::render($request, $exception);
    }
}
```

---

## FIX #11: Add Backup Strategy

### Create: `app/Console/Commands/BackupDatabase.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'Backup the database';

    public function handle()
    {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('backups/' . $filename);

        // Create backups directory if it doesn't exist
        if (!is_dir(storage_path('backups'))) {
            mkdir(storage_path('backups'), 0755, true);
        }

        $command = sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database'),
            $path
        );

        exec($command);

        $this->info('Database backed up to: ' . $path);
    }
}
```

### Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Daily backup at 2 AM
    $schedule->command('backup:database')->dailyAt('02:00');
    
    // Clean old backups (keep only 30 days)
    $schedule->call(function () {
        $files = glob(storage_path('backups/*'));
        foreach ($files as $file) {
            if (time() - filemtime($file) > 30 * 24 * 60 * 60) {
                unlink($file);
            }
        }
    })->daily();
}
```

---

## FIX #12: Add Audit Logging

### Create: `app/Traits/LogsActivity.php`

```php
<?php

namespace App\Traits;

use App\Models\AuditLog;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'model' => class_basename($model),
                'model_id' => $model->id,
                'action' => 'created',
                'changes' => $model->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function ($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'model' => class_basename($model),
                'model_id' => $model->id,
                'action' => 'updated',
                'changes' => $model->getChanges(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::deleted(function ($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'model' => class_basename($model),
                'model_id' => $model->id,
                'action' => 'deleted',
                'changes' => $model->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}
```

---

## FIX #13: Add Health Check Endpoint

### Create: `app/Http/Controllers/HealthCheckController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function check()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'permissions' => $this->checkPermissions(),
        ];

        $status = collect($checks)->every(fn($check) => $check['status'] === 'ok') ? 200 : 500;

        return response()->json([
            'status' => $status === 200 ? 'healthy' : 'unhealthy',
            'checks' => $checks,
        ], $status);
    }

    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Database connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkCache()
    {
        try {
            cache()->put('health_check', true, 1);
            return ['status' => 'ok', 'message' => 'Cache working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkStorage()
    {
        try {
            $path = storage_path('health_check.txt');
            file_put_contents($path, 'ok');
            unlink($path);
            return ['status' => 'ok', 'message' => 'Storage writable'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkPermissions()
    {
        $dirs = [
            storage_path(),
            storage_path('logs'),
            storage_path('framework'),
            bootstrap_path('cache'),
        ];

        foreach ($dirs as $dir) {
            if (!is_writable($dir)) {
                return ['status' => 'error', 'message' => "Directory not writable: $dir"];
            }
        }

        return ['status' => 'ok', 'message' => 'All directories writable'];
    }
}
```

### Add to `routes/web.php`:

```php
Route::get('/health', [HealthCheckController::class, 'check'])->withoutMiddleware('auth');
```

---

## FIX #14: Add Security Headers

### Create: `app/Http/Middleware/SecurityHeaders.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
```

### Register in `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ... other middleware
    \App\Http\Middleware\SecurityHeaders::class,
];
```

---

## IMPLEMENTATION ORDER

1. **Day 1 - Critical Security:**
   - [ ] Fix .env configuration
   - [ ] Enable security middleware
   - [ ] Disable debug mode
   - [ ] Enable session encryption

2. **Day 2 - Input Validation:**
   - [ ] Add input validation
   - [ ] Add rate limiting
   - [ ] Fix CSRF protection
   - [ ] Add file upload validation

3. **Day 3 - Monitoring:**
   - [ ] Add error handling
   - [ ] Add audit logging
   - [ ] Add health check
   - [ ] Add security headers

4. **Day 4 - Testing:**
   - [ ] Test all fixes
   - [ ] Security testing
   - [ ] Performance testing
   - [ ] User acceptance testing

---

## VERIFICATION CHECKLIST

After applying fixes, verify:

```
SECURITY:
- [ ] .env has no credentials
- [ ] APP_DEBUG=false
- [ ] SESSION_ENCRYPT=true
- [ ] All middleware enabled
- [ ] CSRF protection on all forms
- [ ] Rate limiting working
- [ ] Security headers present

FUNCTIONALITY:
- [ ] Login works
- [ ] All routes accessible
- [ ] No error messages expose sensitive info
- [ ] File uploads validated
- [ ] All calculations correct

PERFORMANCE:
- [ ] Page load < 2 seconds
- [ ] API response < 1 second
- [ ] No N+1 queries
- [ ] Database indexes present
```

---

**Status:** Ready to implement  
**Estimated Time:** 4 days  
**Risk Level:** Low (fixes are non-breaking)

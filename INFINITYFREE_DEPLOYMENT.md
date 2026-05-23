# InfinityFree Deployment Guide for MAGIA LUPOS Jewellery Management System

## 📋 Prerequisites

- InfinityFree Account: https://www.infinityfree.net/
- Domain setup on InfinityFree
- FTP Client (FileZilla recommended)
- MySQL Database (created via InfinityFree control panel)
- PHP 8.0 or 8.1 (InfinityFree supported versions)

## ⚠️ InfinityFree Limitations

**Important:** InfinityFree has strict limitations:
- ❌ No SSH access
- ❌ No Composer support
- ❌ No Node.js/NPM
- ❌ No queue workers
- ❌ No cron jobs (limited)
- ❌ No Python support (AI agent won't work)
- ❌ Max file size: 10MB
- ❌ Max database: MySQL only
- ❌ Limited disk space: ~5GB
- ❌ No custom PHP extensions

## 🚀 Step-by-Step Deployment

### Step 1: Prepare Your Application Locally

#### 1.1 Install Dependencies Locally
```bash
cd C:\xampp\htdocs\jewellery-management-system

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Build frontend assets (if you have Node.js)
npm install
npm run build
```

#### 1.2 Create Production .env File
Create a new `.env` file for production:

```env
APP_NAME="MAGIA LUPOS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.infinityfreeapp.com
APP_KEY=base64:YOUR_GENERATED_KEY_HERE

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=sql123.infinityfree.com
DB_PORT=3306
DB_DATABASE=epiz_12345678_your_database
DB_USERNAME=epiz_12345678
DB_PASSWORD=your_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### 1.3 Generate App Key
```bash
php artisan key:generate
```

#### 1.4 Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 2: Create Database on InfinityFree

1. Login to InfinityFree Control Panel (cPanel)
2. Go to "MySQL Databases"
3. Create new database
4. Note down:
   - Database Name (e.g., `epiz_12345678_yourdb`)
   - Database Username
   - Database Password
   - Database Host (e.g., `sql123.infinityfree.com`)

### Step 3: Upload Files via FTP

#### 3.1 FTP Connection Details
- **Host**: `ftpupload.net` or provided by InfinityFree
- **Username**: Your InfinityFree username (e.g., `epiz_12345678`)
- **Password**: Your InfinityFree account password
- **Port**: 21

#### 3.2 Upload Structure
Upload files to `htdocs` folder:

```
htdocs/
├── .env (create manually on server)
├── .htaccess (already included)
├── index.php (from public/ folder)
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── css/
│   ├── js/
│   ├── Img/
│   └── .htaccess
├── resources/
├── routes/
├── storage/
└── vendor/
```

**IMPORTANT:** Upload the contents of `public/` folder to `htdocs/` root, OR restructure:

**Option A: Upload to subfolder (Recommended)**
1. Create folder: `htdocs/jewellery/`
2. Upload ALL project files there
3. Copy contents of `public/` to `htdocs/`
4. Edit `htdocs/index.php`:

```php
require __DIR__.'/jewellery/vendor/autoload.php';
$app = require_once __DIR__.'/jewellery/bootstrap/app.php';
```

**Option B: Root installation**
1. Upload `public/` contents to `htdocs/`
2. Upload all other folders to `htdocs/`
3. Edit `htdocs/index.php` paths if needed

### Step 4: Set Permissions

Via FTP or File Manager, set these permissions:

```bash
storage/          → 755 or 777
storage/logs/     → 755 or 777
storage/framework/ → 755 or 777
bootstrap/cache/  → 755 or 777
public/storage/   → 755 or 777
.env              → 644
```

### Step 5: Create .env on Server

1. Go to `htdocs/` via FTP
2. Create new file: `.env`
3. Paste your production `.env` configuration
4. Update database credentials

### Step 6: Run Migrations

Since InfinityFree has no SSH, use one of these methods:

#### Method 1: Create Migration Route (Temporary)
Add to `routes/web.php`:

```php
Route::get('/run-migrations', function () {
    if (app()->environment('production')) {
        abort(403, 'Not allowed in production');
    }
    
    Artisan::call('migrate:fresh', ['--force' => true]);
    return 'Migrations completed!';
})->middleware('auth'); // Protect with authentication!
```

**⚠️ SECURITY WARNING:** Remove this route immediately after running migrations!

#### Method 2: Local Export + Import
```bash
# Export from local database
mysqldump -u root -p jewellery_db > backup.sql

# Import via phpMyAdmin on InfinityFree
# 1. Open phpMyAdmin from cPanel
# 2. Select your database
# 3. Click "Import"
# 4. Upload backup.sql
```

### Step 7: Create Storage Link

Since no SSH, manually create symbolic link or copy files:

#### Option 1: Manual Copy
Copy `storage/app/public/` contents to `public/storage/`

#### Option 2: Create Route (Temporary)
```php
Route::get('/create-storage-link', function () {
    if (app()->environment('production')) {
        abort(403, 'Not allowed');
    }
    
    try {
        Artisan::call('storage:link');
        return 'Storage link created!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
})->middleware('auth');
```

### Step 8: Create Admin User

Since no Tinker access, create a seeder or route:

```php
Route::get('/create-admin', function () {
    if (app()->environment('production')) {
        abort(403, 'Not allowed');
    }
    
    $user = \App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@yourdomain.com',
        'password' => bcrypt('YourSecurePassword123!'),
        'role' => 'admin',
        'is_super_admin' => true,
    ]);
    
    return 'Admin created: ' . $user->email;
})->middleware('auth');
```

**⚠️ SECURITY WARNING:** Remove this route immediately after use!

### Step 9: Test Your Application

1. Visit: `https://your-domain.infinityfreeapp.com`
2. Test login
3. Check database connection
4. Verify all modules work
5. Check error logs if issues occur

### Step 10: Security Hardening

#### 10.1 Remove Development Routes
Remove these temporary routes from `routes/web.php`:
- `/run-migrations`
- `/create-storage-link`
- `/create-admin`

#### 10.2 Clear Caches
```bash
# Run locally and re-upload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

#### 10.3 Verify .htaccess
Ensure `.htaccess` is uploaded and working:
- Forces HTTPS
- Protects `.env` file
- Enables URL rewriting

## 🔧 Troubleshooting

### Issue: 500 Internal Server Error
**Solutions:**
1. Check `.env` file exists and is correct
2. Verify file permissions (storage: 755/777)
3. Check PHP version (must be 8.0+)
4. Enable error display temporarily:
   ```env
   APP_DEBUG=true
   ```

### Issue: Database Connection Error
**Solutions:**
1. Verify database credentials in `.env`
2. Check database host (usually `sql123.infinityfree.com`)
3. Ensure database exists in cPanel
4. Test connection via phpMyAdmin

### Issue: Files Not Uploading
**Solutions:**
1. Check InfinityFree file size limit (10MB max)
2. Verify disk space quota
3. Use FTP binary mode for uploads
4. Upload in smaller batches

### Issue: Styles/JS Not Loading
**Solutions:**
1. Ensure assets are in `public/` folder
2. Check `.htaccess` rewrite rules
3. Clear browser cache
4. Verify file paths in blade templates

### Issue: Session Not Working
**Solutions:**
1. Set `SESSION_DRIVER=file` in `.env`
2. Ensure `storage/framework/sessions/` is writable
3. Check cookie domain settings

## 📊 Post-Deployment Checklist

- [ ] Application loads without errors
- [ ] Database connection working
- [ ] Admin user can login
- [ ] All modules accessible
- [ ] File uploads working
- [ ] Email configuration (if needed)
- [ ] HTTPS enabled
- [ ] `.env` file protected
- [ ] Debug mode disabled
- [ ] Error logging enabled
- [ ] Temporary routes removed
- [ ] File permissions correct
- [ ] Storage accessible
- [ ] Cache working

## 🚫 Features That Won't Work on InfinityFree

Due to platform limitations:
- ❌ AI Agent (Python-based)
- ❌ Queue Workers
- ❌ Real-time notifications
- ❌ Cron jobs (scheduled tasks)
- ❌ Custom PHP extensions
- ❌ Advanced caching (Redis)
- ❌ WebSockets
- ❌ Email sending (limited)

## ✅ Recommended Alternatives

For full functionality, consider:
1. **Railway.app** - Already configured (`railway.toml`)
2. **Render.com** - Already configured (`render.yaml`)
3. **Heroku** - Easy Laravel deployment
4. **DigitalOcean** - More control
5. **VPS** - Full control (DigitalOcean, Linode, Vultr)

## 📞 Support

If you encounter issues:
1. Check InfinityFree forums: https://forum.infinityfree.com/
2. Review error logs in `storage/logs/`
3. Enable `APP_DEBUG=true` temporarily
4. Check PHP error logs in cPanel

## 🎉 Deployment Complete!

Once everything is working:
1. Remove all temporary routes
2. Disable debug mode: `APP_DEBUG=false`
3. Monitor error logs regularly
4. Keep backups of your database
5. Update dependencies regularly

---

**Note:** InfinityFree is suitable for testing/demo purposes only. For production use, consider upgrading to a paid hosting plan or using recommended alternatives.

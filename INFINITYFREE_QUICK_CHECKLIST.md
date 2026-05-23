# InfinityFree Deployment Quick Checklist

## ✅ Pre-Deployment (Local Computer)

### 1. Install Dependencies
```bash
cd C:\xampp\htdocs\jewellery-management-system
composer install --no-dev --optimize-autoloader
```

### 2. Generate App Key
```bash
php artisan key:generate
```
Copy the generated key for later use.

### 3. Build Assets (If you have Node.js)
```bash
npm install
npm run build
```

### 4. Create Production .env
- Copy `.env.infinityfree` to `.env`
- Update database credentials
- Set `APP_KEY=base64:YOUR_GENERATED_KEY`
- Set `APP_URL=https://your-domain.infinityfreeapp.com`

### 5. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 6. Export Database
```bash
mysqldump -u root -p jewellery_db > backup.sql
```

---

## ✅ InfinityFree Setup

### 1. Create Account
- [ ] Sign up at https://www.infinityfree.net/
- [ ] Create a new domain (your-domain.infinityfreeapp.com)

### 2. Create Database
- [ ] Login to cPanel (via Account Control Panel)
- [ ] Go to "MySQL Databases"
- [ ] Create new database
- [ ] Save credentials:
  - [ ] Database Host: `sql###.infinityfree.com`
  - [ ] Database Name: `epiz_########_yourdb`
  - [ ] Username: `epiz_########`
  - [ ] Password: (save securely)

### 3. Import Database
- [ ] Open phpMyAdmin from cPanel
- [ ] Select your database
- [ ] Click "Import" tab
- [ ] Choose `backup.sql` file
- [ ] Click "Go"
- [ ] Wait for completion

---

## ✅ FTP Upload

### 1. Connect via FTP
- [ ] Download FileZilla: https://filezilla-project.org/
- [ ] Connect with:
  - Host: `ftpupload.net` (or provided)
  - Username: `epiz_########`
  - Password: Your InfinityFree password
  - Port: 21

### 2. Upload Files
Choose ONE option:

**Option A: Root Installation (Recommended for main domain)**
- [ ] Delete default `htdocs/index2.html`
- [ ] Upload ALL project files to `htdocs/`
- [ ] Move contents of `public/` folder to `htdocs/` root
- [ ] Edit `htdocs/index.php`:
  ```php
  require __DIR__.'/vendor/autoload.php';
  $app = require_once __DIR__.'/bootstrap/app.php';
  ```

**Option B: Subfolder Installation**
- [ ] Create folder: `htdocs/jewellery/`
- [ ] Upload ALL project files to `htdocs/jewellery/`
- [ ] Copy contents of `public/` to `htdocs/`
- [ ] Edit `htdocs/index.php`:
  ```php
  require __DIR__.'/jewellery/vendor/autoload.php';
  $app = require_once __DIR__.'/jewellery/bootstrap/app.php';
  ```

### 3. Create .env on Server
- [ ] Navigate to `htdocs/` via FTP
- [ ] Create new file: `.env`
- [ ] Upload `.env.infinityfree` content
- [ ] Update with actual database credentials
- [ ] Set correct `APP_KEY`

### 4. Set Permissions
Via FTP, right-click and set permissions:
- [ ] `storage/` → 755
- [ ] `storage/logs/` → 755
- [ ] `storage/framework/` → 755
- [ ] `storage/framework/cache/` → 755
- [ ] `storage/framework/sessions/` → 755
- [ ] `storage/framework/views/` → 755
- [ ] `bootstrap/cache/` → 755
- [ ] `public/storage/` → 755 (if exists)

---

## ✅ Post-Upload Setup

### 1. Create Storage Link
Since no SSH, create a temporary route in `routes/web.php`:

```php
Route::get('/setup-storage', function () {
    if (app()->environment('production')) {
        abort(403, 'Unauthorized');
    }
    
    try {
        $target = storage_path('app/public');
        $link = public_path('storage');
        
        if (!file_exists($link)) {
            symlink($target, $link);
            return 'Storage link created successfully!';
        }
        return 'Storage link already exists.';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});
```

- [ ] Upload updated `routes/web.php`
- [ ] Visit: `https://your-domain.infinityfreeapp.com/setup-storage`
- [ ] Verify success message
- [ ] **REMOVE THIS ROUTE** from `routes/web.php` after use

### 2. Create Admin User
Add temporary route in `routes/web.php`:

```php
Route::get('/setup-admin', function () {
    if (app()->environment('production')) {
        abort(403, 'Unauthorized');
    }
    
    $user = \App\Models\User::updateOrCreate(
        ['email' => 'admin@yourdomain.com'],
        [
            'name' => 'Admin',
            'password' => bcrypt('YourSecurePassword123!'),
            'role' => 'admin',
            'is_super_admin' => true,
        ]
    );
    
    return 'Admin user created: ' . $user->email;
});
```

- [ ] Upload updated `routes/web.php`
- [ ] Visit: `https://your-domain.infinityfreeapp.com/setup-admin`
- [ ] Verify admin created
- [ ] **REMOVE THIS ROUTE** from `routes/web.php` after use

### 3. Test Application
- [ ] Visit: `https://your-domain.infinityfreeapp.com`
- [ ] Login with admin credentials
- [ ] Check dashboard loads
- [ ] Test database connection
- [ ] Check a few modules work

---

## ✅ Security Hardening

### 1. Remove Temporary Routes
- [ ] Remove `/setup-storage` route
- [ ] Remove `/setup-admin` route
- [ ] Remove any `/run-migrations` route
- [ ] Upload updated `routes/web.php`

### 2. Verify .env Protection
- [ ] Try accessing: `https://your-domain.infinityfreeapp.com/.env`
- [ ] Should show "403 Forbidden" or "404 Not Found"
- [ ] If accessible, check `.htaccess` file exists

### 3. Disable Debug Mode
- [ ] Verify `.env` has: `APP_DEBUG=false`
- [ ] Test that errors don't show details

### 4. Clear Caches (Optional)
If you make changes later:
```bash
# Run locally and re-upload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## ✅ Final Verification

### 1. Check All Modules
- [ ] Dashboard
- [ ] Inventory
- [ ] Sales/POS
- [ ] Customers
- [ ] Accounting
- [ ] Reports
- [ ] User Management

### 2. Check Features
- [ ] Login/Logout works
- [ ] Database queries work
- [ ] File uploads work
- [ ] Forms submit correctly
- [ ] Data saves properly

### 3. Check Performance
- [ ] Pages load within timeout (60 seconds)
- [ ] No memory limit errors
- [ ] Images load correctly
- [ ] CSS/JS files load

---

## ⚠️ Important Notes

### Features NOT Available on InfinityFree:
- ❌ AI Agent (Python-based)
- ❌ Queue Workers
- ❌ Cron Jobs
- ❌ Redis/Memcached
- ❌ WebSockets
- ❌ Email Sending (limited)
- ❌ SSH Access
- ❌ Composer on Server

### Workarounds:
- Use `QUEUE_CONNECTION=sync` (already set)
- Use `CACHE_DRIVER=file` (already set)
- Use `SESSION_DRIVER=file` (already set)
- Manual database backups via phpMyAdmin
- Manual file backups via FTP

---

## 🆘 Troubleshooting

### 500 Internal Server Error
1. Check `.env` file exists and is correct
2. Verify file permissions (storage: 755)
3. Check PHP version in cPanel (must be 8.0+)
4. Temporarily set `APP_DEBUG=true` to see error

### Database Connection Error
1. Verify database credentials in `.env`
2. Check database host (usually `sql###.infinityfree.com`)
3. Test connection via phpMyAdmin
4. Ensure database was imported successfully

### Styles/JS Not Loading
1. Check assets are in correct location
2. Verify `.htaccess` file exists
3. Clear browser cache (Ctrl+F5)
4. Check file paths in blade templates

### Session Issues
1. Verify `SESSION_DRIVER=file` in `.env`
2. Check `storage/framework/sessions/` is writable
3. Clear browser cookies
4. Check cookie domain settings

---

## 📞 Support Resources

- InfinityFree Forum: https://forum.infinityfree.com/
- InfinityFree Knowledge Base: https://infinityfree.net/support
- Laravel Documentation: https://laravel.com/docs
- Project Documentation: See INFINITYFREE_DEPLOYMENT.md

---

## 🎉 Deployment Complete!

If all items are checked, your application is live!

**Next Steps:**
1. Monitor error logs regularly
2. Keep regular backups
3. Test all features periodically
4. Consider upgrading to paid hosting for production use
5. Remove any temporary routes or debug code

---

**Remember:** InfinityFree is for testing/demo purposes. For production, use Railway, Render, or paid hosting.

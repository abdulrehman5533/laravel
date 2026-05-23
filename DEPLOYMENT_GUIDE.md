# 🚀 AI Agent - Deployment Guide

> **Production میں deploy کرنے کے لیے مکمل گائیڈ**

---

## 📋 Pre-Deployment Checklist

### ✅ Code
- [ ] تمام files موجود ہیں
- [ ] تمام migrations تیار ہیں
- [ ] تمام routes شامل ہیں
- [ ] تمام services registered ہیں

### ✅ Configuration
- [ ] .env file configured ہے
- [ ] API keys موجود ہیں
- [ ] Database configured ہے
- [ ] Cache configured ہے

### ✅ Security
- [ ] HTTPS enabled ہے
- [ ] CORS configured ہے
- [ ] Rate limiting enabled ہے
- [ ] Input validation active ہے

### ✅ Database
- [ ] Migrations run ہو چکے ہیں
- [ ] Tables created ہیں
- [ ] Indexes created ہیں
- [ ] Backups taken ہیں

---

## 🔧 Step-by-Step Deployment

### Step 1: Code Deployment
```bash
# Repository سے latest code pull کریں
git pull origin main

# Dependencies install کریں
composer install --no-dev

# Node dependencies (اگر ضروری ہو)
npm install --production
```

### Step 2: Environment Setup
```bash
# .env file copy کریں
cp .env.example .env

# Application key generate کریں
php artisan key:generate

# Configure کریں:
# - Database credentials
# - API keys (Groq, Google, SMS, WhatsApp)
# - Cache driver
# - Mail configuration
```

### Step 3: Database Setup
```bash
# Migrations run کریں
php artisan migrate --force

# Seeders run کریں (اگر ضروری ہو)
php artisan db:seed

# Database optimize کریں
php artisan optimize
```

### Step 4: Cache & Config
```bash
# Config cache کریں
php artisan config:cache

# Route cache کریں
php artisan route:cache

# View cache کریں
php artisan view:cache

# Cache warm up کریں
php artisan cache:clear
```

### Step 5: File Permissions
```bash
# Storage directory permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Public directory permissions
chmod -R 755 public
```

### Step 6: Web Server Setup

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache Configuration
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/public

    <Directory /path/to/public>
        AllowOverride All
        Require all granted
    </Directory>

    <IfModule mod_rewrite.c>
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^ index.php [QSA,L]
    </IfModule>
</VirtualHost>
```

### Step 7: SSL Certificate
```bash
# Let's Encrypt سے certificate حاصل کریں
sudo certbot certonly --webroot -w /path/to/public -d your-domain.com

# Nginx میں SSL configure کریں
listen 443 ssl http2;
ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
```

### Step 8: Monitoring & Logging
```bash
# Log rotation setup
sudo nano /etc/logrotate.d/laravel

# Supervisor configuration (for queue workers)
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

---

## 📊 Production Configuration

### .env Production Settings
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# API Keys
GROQ_API_KEY=your_groq_key
GOOGLE_CLOUD_API_KEY=your_google_key
SMS_API_KEY=your_sms_key
WHATSAPP_API_KEY=your_whatsapp_key

# Security
AI_AGENT_SECRET_KEY=your_secret_key
AI_AGENT_IP_WHITELIST=127.0.0.1
```

---

## 🔒 Security Hardening

### 1. Firewall Configuration
```bash
# UFW firewall setup
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 2. SSH Security
```bash
# SSH key-based authentication
ssh-keygen -t rsa -b 4096

# Disable password authentication
sudo nano /etc/ssh/sshd_config
# PasswordAuthentication no
# PermitRootLogin no

sudo systemctl restart ssh
```

### 3. Application Security
```bash
# Hide Laravel version
# In .env: APP_DEBUG=false

# Set secure headers
# Already configured in nginx/apache

# Enable CSRF protection
# Already enabled in Laravel

# Rate limiting
# Already configured in routes
```

### 4. Database Security
```bash
# Create database user with limited privileges
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON database_name.* TO 'app_user'@'localhost';
FLUSH PRIVILEGES;

# Backup database regularly
mysqldump -u root -p database_name > backup.sql
```

---

## 📈 Performance Optimization

### 1. Database Optimization
```bash
# Add indexes
php artisan tinker
> DB::statement('ALTER TABLE ai_agent_logs ADD INDEX idx_user_id (user_id)');
> DB::statement('ALTER TABLE notification_histories ADD INDEX idx_user_id (user_id)');
```

### 2. Query Optimization
```php
// Use eager loading
$logs = AIAgentLog::with('user')->get();

// Use select specific columns
$logs = AIAgentLog::select('id', 'message', 'intent')->get();

// Use pagination
$logs = AIAgentLog::paginate(50);
```

### 3. Caching Strategy
```bash
# Redis configuration
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Cache warming
php artisan cache:clear
php artisan config:cache
```

### 4. CDN Setup
```bash
# Configure CDN for static assets
# Update APP_URL to CDN URL for assets
```

---

## 🔍 Monitoring & Maintenance

### 1. Health Checks
```bash
# Create health check endpoint
php artisan make:command HealthCheck

# Run periodic health checks
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

### 2. Log Monitoring
```bash
# Monitor logs
tail -f storage/logs/laravel.log

# Log rotation
sudo logrotate -f /etc/logrotate.d/laravel
```

### 3. Database Maintenance
```bash
# Regular backups
0 2 * * * mysqldump -u root -p database_name > /backups/backup_$(date +\%Y\%m\%d).sql

# Optimize tables
OPTIMIZE TABLE ai_agent_logs;
OPTIMIZE TABLE notification_histories;
```

### 4. Performance Monitoring
```bash
# Monitor server resources
top
htop
df -h
free -h

# Monitor application
php artisan tinker
> \App\Services\AIAgentLoggingService::getStatistics(30);
```

---

## 🚨 Troubleshooting

### Issue: 500 Error
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
chmod -R 775 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Issue: Database Connection Error
```bash
# Check database credentials
php artisan tinker
> DB::connection()->getPdo();

# Check database server
mysql -u user -p -h host
```

### Issue: API Rate Limiting
```bash
# Check rate limit configuration
php artisan tinker
> config('app.rate_limit');

# Adjust if needed
RATE_LIMIT=100
```

### Issue: Cache Not Working
```bash
# Check Redis connection
redis-cli ping

# Restart Redis
sudo systemctl restart redis-server

# Clear cache
php artisan cache:clear
```

---

## 📋 Post-Deployment Checklist

- [ ] تمام endpoints کام کر رہے ہیں
- [ ] Database migrations successful ہیں
- [ ] Caching working ہے
- [ ] Logging working ہے
- [ ] Security headers set ہیں
- [ ] SSL certificate installed ہے
- [ ] Backups configured ہیں
- [ ] Monitoring active ہے
- [ ] Performance acceptable ہے
- [ ] Users can access application

---

## 🎯 Maintenance Schedule

### Daily
- [ ] Check logs
- [ ] Monitor performance
- [ ] Check disk space

### Weekly
- [ ] Database backup
- [ ] Security updates
- [ ] Performance review

### Monthly
- [ ] Full system audit
- [ ] Database optimization
- [ ] Cache cleanup

### Quarterly
- [ ] Security assessment
- [ ] Performance tuning
- [ ] Capacity planning

---

## 📞 Support

### Emergency Contacts
- Database Admin: [contact]
- Server Admin: [contact]
- Security Team: [contact]

### Documentation
- API Documentation: /docs/api
- Deployment Guide: This file
- Troubleshooting: /docs/troubleshooting

---

**🚀 Deployment Complete!**

**آپ کا AI Agent اب Production میں ہے! 🎉**

**خوش قسمتی! 🎊**

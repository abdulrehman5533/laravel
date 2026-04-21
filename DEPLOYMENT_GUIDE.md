# **Jewelry Management System: Technical & Deployment Guide (A to Z)**

This guide provides a comprehensive overview of the technical setup, library management, authentication architecture, and deployment procedures for the **Jewelry Management System (MAGIA LUPOS)**.

---

## **1. Library & Package Management**

### **Backend (Composer)**
The backend uses **Composer** to manage PHP libraries.

| **Package** | **Command to Install** | **Purpose** |
| :--- | :--- | :--- |
| **Laravel Framework** | `composer install` | Core application framework. |
| **Spatie Permissions** | `composer require spatie/laravel-permission` | Role-Based Access Control (RBAC). |
| **Laravel DOMPDF** | `composer require barryvdh/laravel-dompdf` | PDF generation for invoices/reports. |
| **Laravel Excel** | `composer require maatwebsite/excel` | Excel/CSV export and import. |
| **Laravel Tinker** | `composer require laravel/tinker` | Interactive shell for debugging. |

### **Frontend (NPM)**
The frontend uses **NPM/Vite** for asset compilation.

| **Package** | **Command to Install** | **Purpose** |
| :--- | :--- | :--- |
| **Bootstrap 5** | `npm install bootstrap @popperjs/core` | UI Framework. |
| **Tailwind CSS** | `npm install tailwindcss @tailwindcss/vite` | Modern utility-first CSS. |
| **Chart.js** | `npm install chart.js` | Interactive data charts. |
| **Axios** | `npm install axios` | HTTP requests for AJAX/APIs. |

---

## **2. Authentication & Session Architecture**

### **Authentication (Auth)**
- **Type**: Laravel Breeze (Session-based Eloquent authentication).
- **Guard**: `web` (Uses session driver).
- **Provider**: `users` (Eloquent model: `App\Models\User`).
- **Security**: 
  - **BCRYPT**: Passwords are hashed using BCRYPT with 12 rounds.
  - **CSRF**: All POST requests are protected by Cross-Site Request Forgery tokens.

### **Session Management**
- **Driver**: `database` (Sessions are stored in the `sessions` table for persistence and better control).
- **Lifetime**: 120 minutes (configurable via `.env`).
- **Encryption**: Optional (defaults to `false`).
- **Security Flags**:
  - `http_only`: `true` (Prevents JS access to session cookies).
  - `expire_on_close`: `true` (Sessions expire when the browser is closed).
  - `same_site`: `lax` (Prevents CSRF while maintaining usability).

---

## **3. Complete Command Reference**

### **Project Setup Commands**
```bash
# Clone the repository
git clone <repository_url>

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Generate Application Key
php artisan key:generate

# Build frontend assets
npm run build
```

### **Database Commands**
```bash
# Run all migrations
php artisan migrate

# Refresh database and run all seeders (Fresh start)
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### **Development & Maintenance**
```bash
# Clear all caches (Config, Route, View, Cache)
php artisan optimize:clear

# Link storage directory for file uploads
php artisan storage:link

# Start development server
php artisan serve

# Enter interactive shell
php artisan tinker
```

---

## **4. A to Z Deployment Guide**

### **Prerequisites**
- PHP 8.2 or higher
- MySQL 8.0 or MariaDB 10.4+
- Web Server (Apache/Nginx)
- SSL Certificate (Recommended for security)

### **Step 1: Server Preparation**
Ensure the following PHP extensions are installed:
`bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `json`, `mbstring`, `openssl`, `pcre`, `pdo`, `tokenizer`, `xml`.

### **Step 2: Upload and Permissions**
1.  Upload the project to the server (e.g., `/var/www/html/jewellery`).
2.  Set directory permissions:
    ```bash
    chown -R www-data:www-data /var/www/html/jewellery
    chmod -R 775 /var/www/html/jewellery/storage
    chmod -R 775 /var/www/html/jewellery/bootstrap/cache
    ```

### **Step 3: Environment Configuration**
Edit the `.env` file on the server:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### **Step 4: Production Optimization**
Run these commands on the production server:    
```bash
# Install production dependencies
composer install --optimize-autoloader --no-dev

# Cache configurations and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compile production assets
npm install
npm run build
```

### **Step 5: Web Server Configuration (Nginx Example)**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/html/jewellery/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

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

---

## **5. Initial Data (Login Details)**
After running `php artisan migrate --seed`, use the following default credentials to login:
- **Email**: `sariapratab@gmail.com`
- **Password**: `Sicl@3241`

---
*Guide Version: 1.0.0*  
*Last Updated: January 22, 2026*

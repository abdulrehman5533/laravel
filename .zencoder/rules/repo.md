---
description: Repository Information Overview
alwaysApply: true
---

# Jewellery Management System Information

## Summary
A professional, market-ready Jewelry POS and ERP system built with Laravel 11. It handles Gold, Diamond, Silver, and Gems management, featuring modules for POS, Inventory, Accounting (ERP level), CRM, and Service/Repair tracking. The system follows a clean MVC/Service/Repository architecture.

## Structure
- **app/**: Core application logic (Controllers, Models, Services, Providers).
  - **Http/Controllers/**: Organized by modules (Accounts, POS, CRM, Inventory, Reports, Service).
  - **Services/**: Business logic layer for Cashbook, Inventory, Ledger, etc.
- **config/**: System configuration files.
- **database/**: Migrations, factories, and seeders for 30+ tables.
- **public/**: Web entry point and compiled assets.
- **resources/**: Frontend source files (Blade templates, CSS, JS).
- **routes/**: Web and API route definitions (web.php, auth.php).
- **tests/**: Feature and Unit tests using PHPUnit.

## Language & Runtime
**Language**: PHP  
**Version**: ^8.2  
**Framework**: Laravel ^11.0  
**Build System**: Vite ^7.0  
**Package Manager**: Composer & NPM

## Dependencies
**Main Dependencies**:
- **laravel/framework**: ^11.0
- **spatie/laravel-permission**: ^6.0 (RBAC)
- **barryvdh/laravel-dompdf**: ^2.0 (PDF Generation)
- **maatwebsite/excel**: ^3.1 (Excel Export)
- **bootstrap**: ^5.3.8
- **tailwindcss**: ^4.0.0
- **chart.js**: ^4.5.1 (Dashboards)

**Development Dependencies**:
- **phpunit/phpunit**: ^10.1
- **laravel/sail**: ^1.26 (Docker environment)
- **laravel/pint**: ^1.13 (Code styling)

## Build & Installation
```bash
# Install PHP dependencies
composer install

# Install Frontend dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed --class=AccountsSeeder

# Frontend build
npm run dev # or npm run build
```

## Docker
**Configuration**: Integrated via **Laravel Sail**.
**Usage**: Can be started using `./vendor/bin/sail up`.

## Testing
**Framework**: PHPUnit
**Test Location**: `tests/Feature` and `tests/Unit`
**Naming Convention**: `*Test.php`
**Run Command**:
```bash
php artisan test
```

## Main Files & Resources
- **Entry Point**: `public/index.php`
- **Routes**: `routes/web.php` (Main application routes)
- **Primary Controllers**: 
  - `app/Http/Controllers/POS/SaleController.php`
  - `app/Http/Controllers/Accounts/AccountingDashboardController.php`
  - `app/Http/Controllers/Inventory/InventoryProductController.php`
- **Config**: `vite.config.js`, `phpunit.xml`, `composer.json`

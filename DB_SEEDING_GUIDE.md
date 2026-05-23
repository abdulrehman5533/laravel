╔════════════════════════════════════════════════════════════════════════════╗
║                                                                            ║
║                    🌱 DATABASE SEEDING GUIDE 🌱                           ║
║                                                                            ║
║                    Jewellery Management System                            ║
║                                                                            ║
╚════════════════════════════════════════════════════════════════════════════╝

═══════════════════════════════════════════════════════════════════════════════
🚀 QUICK COMMANDS
═══════════════════════════════════════════════════════════════════════════════

Navigate to Laravel directory:
  cd c:\xampp1\htdocs\jewellery-management-system

Run all seeders:
  php artisan db:seed

Or run specific seeders:
  php artisan db:seed --class=DatabaseSeeder
  php artisan db:seed --class=RolesAndPermissionsSeeder
  php artisan db:seed --class=HRSeeder
  php artisan db:seed --class=InventorySeeder
  php artisan db:seed --class=POSSeeder

═══════════════════════════════════════════════════════════════════════════════
📋 AVAILABLE SEEDERS
═══════════════════════════════════════════════════════════════════════════════

Core Seeders:
  ✅ DatabaseSeeder ..................... Main seeder (runs all)
  ✅ RolesAndPermissionsSeeder .......... Roles & permissions
  ✅ DashboardPermissionsSeeder ......... Dashboard permissions

Module Seeders:
  ✅ HRSeeder ........................... HR & Employees
  ✅ InventorySeeder ................... Inventory & Products
  ✅ POSSeeder .......................... POS & Sales
  ✅ AccountsSeeder .................... Accounting
  ✅ BankAccountSeeder ................. Bank accounts
  ✅ ChartOfAccountSeeder .............. Chart of accounts
  ✅ ExpenseCategorySeeder ............. Expense categories
  ✅ ProductCategorySeeder ............. Product categories
  ✅ RateApiProviderSeeder ............. Rate API providers
  ✅ TenantSeeder ...................... Tenants
  ✅ TwoFactorAuthSeeder ............... 2FA settings
  ✅ EmailCampaignSeeder ............... Email campaigns
  ✅ GirviSettingsSeeder ............... Girvi settings
  ✅ BudgetSeeder ...................... Budget data
  ✅ InstallmentPlanSeeder ............. Installment plans
  ✅ PlanSeeder ........................ Plans
  ✅ BarcodeSeeder ..................... Barcode settings
  ✅ JewelryManagementSeeder ........... Jewelry data

═══════════════════════════════════════════════════════════════════════════════
⚡ STEP-BY-STEP SEEDING
═══════════════════════════════════════════════════════════════════════════════

STEP 1: Open Command Prompt
────────────────────────────
Press: Windows Key + R
Type: cmd
Press: Enter


STEP 2: Navigate to Laravel Directory
──────────────────────────────────────
cd c:\xampp1\htdocs\jewellery-management-system


STEP 3: Run Database Seeding
─────────────────────────────
php artisan db:seed

This will:
  ✅ Create roles and permissions
  ✅ Create users
  ✅ Create employees
  ✅ Create products
  ✅ Create customers
  ✅ Create suppliers
  ✅ Create sample data
  ✅ Setup all modules

Time: 2-5 minutes


STEP 4: Verify Seeding
──────────────────────
php artisan tinker

Then run:
  >>> User::count()
  >>> Employee::count()
  >>> InventoryProduct::count()

Press: exit


STEP 5: Clear Cache
───────────────────
php artisan cache:clear
php artisan config:cache

═══════════════════════════════════════════════════════════════════════════════
🎯 COMPLETE COMMAND SEQUENCE
═══════════════════════════════════════════════════════════════════════════════

Copy and paste these commands one by one:

cd c:\xampp1\htdocs\jewellery-management-system

php artisan migrate

php artisan db:seed

php artisan cache:clear

php artisan config:cache

php artisan serve

═══════════════════════════════════════════════════════════════════════════════
📊 WHAT GETS SEEDED
═══════════════════════════════════════════════════════════════════════════════

Users & Roles:
  ✅ Admin user
  ✅ Manager user
  ✅ Employee user
  ✅ Roles (Admin, Manager, Employee, etc.)
  ✅ Permissions

HR Module:
  ✅ Employees
  ✅ Departments
  ✅ Designations
  ✅ Salary structures
  ✅ Attendance rules

Inventory:
  ✅ Products
  ✅ Categories
  ✅ Stock levels
  ✅ Warehouses
  ✅ Stock locations

Sales & POS:
  ✅ Customers
  ✅ Sample sales
  ✅ POS transactions
  ✅ Pricing tiers

Accounting:
  ✅ Chart of accounts
  ✅ Bank accounts
  ✅ Expense categories
  ✅ Tax configurations

Suppliers:
  ✅ Suppliers
  ✅ Vendor data
  ✅ Payment terms

═══════════════════════════════════════════════════════════════════════════════
✅ VERIFICATION
═══════════════════════════════════════════════════════════════════════════════

After seeding, check:

1. Login to application:
   URL: http://localhost:8000
   Email: admin@example.com
   Password: password

2. Check database:
   php artisan tinker
   >>> User::all()
   >>> Employee::all()
   >>> InventoryProduct::all()

3. Check tables:
   mysql> USE jewellery_db;
   mysql> SHOW TABLES;
   mysql> SELECT COUNT(*) FROM users;

═══════════════════════════════════════════════════════════════════════════════
🆘 TROUBLESHOOTING
═══════════════════════════════════════════════════════════════════════════════

If seeding fails:

1. "Table doesn't exist"
   → Run migrations first: php artisan migrate

2. "Foreign key constraint fails"
   → Run: php artisan migrate:refresh
   → Then: php artisan db:seed

3. "Seeder not found"
   → Check seeder file exists in database/seeders/
   → Run: composer dump-autoload

4. "Permission denied"
   → Make sure MySQL is running
   → Check database credentials in .env

═══════════════════════════════════════════════════════════════════════════════
🔄 REFRESH DATABASE (Start Fresh)
═══════════════════════════════════════════════════════════════════════════════

To delete all data and reseed:

php artisan migrate:refresh --seed

This will:
  ✅ Drop all tables
  ✅ Run all migrations
  ✅ Run all seeders
  ✅ Fresh database with sample data

⚠️ WARNING: This deletes all data!

═══════════════════════════════════════════════════════════════════════════════
📝 SEEDING FOR AI AGENT
═══════════════════════════════════════════════════════════════════════════════

After seeding, your AI Agent can:

✅ Query employees
✅ Check inventory
✅ Process sales
✅ Calculate salaries
✅ Manage stock

Example voice commands:
  "Ahmed ki salary calculate karo"
  "Gold ka stock kitna hai?"
  "50 units silver add karo"

═══════════════════════════════════════════════════════════════════════════════
🎉 COMPLETE WORKFLOW
═══════════════════════════════════════════════════════════════════════════════

1. Run migrations:
   php artisan migrate

2. Seed database:
   php artisan db:seed

3. Clear cache:
   php artisan cache:clear

4. Start Laravel:
   php artisan serve

5. Setup AI Agent:
   cd python-ai-agent
   setup-minimal.bat

6. Start AI Agent:
   python production_agent.py

7. Access:
   Laravel: http://localhost:8000
   AI Agent: http://localhost:8001/docs

═══════════════════════════════════════════════════════════════════════════════

Version: 1.0.0
Status: ✅ Ready to Seed
Next: php artisan db:seed

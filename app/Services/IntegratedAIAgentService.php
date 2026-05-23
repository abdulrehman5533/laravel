<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\InventoryProduct;
use App\Models\Customer;
use App\Models\PosSale;
use App\Models\Branch;
use App\Models\GoldRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntegratedAIAgentService
{
    /**
     * Process natural language commands
     */
    public function processCommand($message, $language = 'ur')
    {
        try {
            $message = strtolower(trim($message));
            
            // Detect intent
            $intent = $this->detectIntent($message);
            
            // Route to appropriate handler
            $response = match($intent['type']) {
                'add_employee' => $this->handleAddEmployee($message, $intent),
                'list_employees' => $this->handleListEmployees($message),
                'update_employee' => $this->handleUpdateEmployee($message, $intent),
                'delete_employee' => $this->handleDeleteEmployee($message, $intent),
                'add_product' => $this->handleAddProduct($message, $intent),
                'list_products' => $this->handleListProducts($message),
                'update_product' => $this->handleUpdateProduct($message, $intent),
                'delete_product' => $this->handleDeleteProduct($message, $intent),
                'add_customer' => $this->handleAddCustomer($message, $intent),
                'list_customers' => $this->handleListCustomers($message),
                'update_customer' => $this->handleUpdateCustomer($message, $intent),
                'delete_customer' => $this->handleDeleteCustomer($message, $intent),
                'create_sale' => $this->handleCreateSale($message, $intent),
                'list_sales' => $this->handleListSales($message),
                'get_analytics' => $this->handleGetAnalytics($message),
                'get_gold_rate' => $this->handleGetGoldRate($message),
                default => $this->handleGeneralQuery($message, $language)
            };
            
            return [
                'status' => 'success',
                'intent' => $intent['type'],
                'answer' => $response['answer'],
                'data' => $response['data'] ?? null,
                'action_taken' => $response['action_taken'] ?? false
            ];
        } catch (\Exception $e) {
            Log::error('AI Agent error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'answer' => 'معافی چاہتا ہوں، کچھ خرابی ہوئی۔',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Detect intent from message
     */
    private function detectIntent($message)
    {
        $keywords = [
            'add_employee' => ['نیا ملازم', 'ملازم شامل', 'employee add', 'نیا کارمند'],
            'list_employees' => ['ملازمین کی فہرست', 'تمام ملازمین', 'employees list', 'کتنے ملازمین'],
            'update_employee' => ['ملازم کی معلومات بدلو', 'update employee', 'ملازم کو اپڈیٹ'],
            'delete_employee' => ['ملازم کو ہٹاؤ', 'delete employee', 'ملازم کو ختم کرو'],
            'add_product' => ['نیا پروڈکٹ', 'product add', 'نیا سامان', 'inventory میں شامل'],
            'list_products' => ['پروڈکٹس کی فہرست', 'تمام سامان', 'inventory list', 'کتنے پروڈکٹس'],
            'update_product' => ['پروڈکٹ کو بدلو', 'update product', 'سامان کو اپڈیٹ'],
            'delete_product' => ['پروڈکٹ کو ہٹاؤ', 'delete product', 'سامان کو ختم'],
            'add_customer' => ['نیا کسٹمر', 'customer add', 'نیا گاہک', 'کسٹمر شامل'],
            'list_customers' => ['کسٹمرز کی فہرست', 'تمام گاہک', 'customers list'],
            'update_customer' => ['کسٹمر کو بدلو', 'update customer', 'گاہک کو اپڈیٹ'],
            'delete_customer' => ['کسٹمر کو ہٹاؤ', 'delete customer', 'گاہک کو ختم'],
            'create_sale' => ['نیا سیل', 'sale create', 'فروخت کریں', 'بکری کریں'],
            'list_sales' => ['سیلز کی فہرست', 'تمام فروخت', 'sales list'],
            'get_analytics' => ['تجزیہ', 'analytics', 'رپورٹ', 'statistics'],
            'get_gold_rate' => ['سونے کی قیمت', 'gold rate', 'قیمت کیا ہے'],
        ];

        foreach ($keywords as $intent => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($message, strtolower($pattern)) !== false) {
                    return ['type' => $intent, 'confidence' => 0.9];
                }
            }
        }

        return ['type' => 'general_query', 'confidence' => 0.5];
    }

    /**
     * Handle add employee
     */
    private function handleAddEmployee($message, $intent)
    {
        // Extract data from message
        $data = $this->extractEmployeeData($message);
        
        if (empty($data['first_name'])) {
            return ['answer' => 'براہ کرم ملازم کا نام بتائیں۔'];
        }

        $employee = Employee::create([
            'employee_code' => 'EMP' . str_pad(Employee::count() + 1, 4, '0', STR_PAD_LEFT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? '',
            'email' => $data['email'] ?? 'emp' . time() . '@company.com',
            'phone' => $data['phone'] ?? '',
            'base_salary' => $data['salary'] ?? 0,
            'department' => $data['department'] ?? 'General',
            'designation' => $data['designation'] ?? 'Staff',
            'joining_date' => now()->toDateString(),
            'status' => 'active',
            'branch_id' => auth()->user()->branch_id ?? 1,
        ]);

        return [
            'answer' => "ملازم {$employee->first_name} کامیابی سے شامل ہو گیا۔ کوڈ: {$employee->employee_code}",
            'data' => $employee,
            'action_taken' => true
        ];
    }

    /**
     * Handle list employees
     */
    private function handleListEmployees($message)
    {
        $employees = Employee::where('branch_id', auth()->user()->branch_id ?? 1)
            ->where('status', 'active')
            ->get();

        $count = $employees->count();
        $answer = "کل {$count} ملازمین ہیں:\n\n";
        
        foreach ($employees as $emp) {
            $answer .= "• {$emp->first_name} {$emp->last_name} - {$emp->designation} (سیلری: {$emp->base_salary})\n";
        }

        return [
            'answer' => $answer,
            'data' => $employees,
            'action_taken' => false
        ];
    }

    /**
     * Handle update employee
     */
    private function handleUpdateEmployee($message, $intent)
    {
        $data = $this->extractEmployeeData($message);
        
        // Find employee by name or code
        $employee = Employee::where('first_name', 'like', '%' . ($data['first_name'] ?? '') . '%')
            ->first();

        if (!$employee) {
            return ['answer' => 'ملازم نہیں ملا۔'];
        }

        $employee->update($data);

        return [
            'answer' => "ملازم {$employee->first_name} کی معلومات اپڈیٹ ہو گئیں۔",
            'data' => $employee,
            'action_taken' => true
        ];
    }

    /**
     * Handle delete employee
     */
    private function handleDeleteEmployee($message, $intent)
    {
        $data = $this->extractEmployeeData($message);
        
        $employee = Employee::where('first_name', 'like', '%' . ($data['first_name'] ?? '') . '%')
            ->first();

        if (!$employee) {
            return ['answer' => 'ملازم نہیں ملا۔'];
        }

        $name = $employee->first_name;
        $employee->delete();

        return [
            'answer' => "ملازم {$name} کو ہٹا دیا گیا۔",
            'action_taken' => true
        ];
    }

    /**
     * Handle add product
     */
    private function handleAddProduct($message, $intent)
    {
        $data = $this->extractProductData($message);

        if (empty($data['name'])) {
            return ['answer' => 'براہ کرم پروڈکٹ کا نام بتائیں۔'];
        }

        $product = InventoryProduct::create([
            'sku' => 'SKU' . str_pad(InventoryProduct::count() + 1, 5, '0', STR_PAD_LEFT),
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'category_id' => $data['category_id'] ?? 1,
            'purity_id' => $data['purity_id'] ?? 1,
            'weight' => $data['weight'] ?? 0,
            'cost_price' => $data['cost_price'] ?? 0,
            'selling_price' => $data['selling_price'] ?? 0,
            'current_stock' => $data['stock'] ?? 0,
            'status' => 'active',
            'branch_id' => auth()->user()->branch_id ?? 1,
            'created_by' => auth()->id(),
        ]);

        return [
            'answer' => "پروڈکٹ {$product->name} کامیابی سے شامل ہو گیا۔ SKU: {$product->sku}",
            'data' => $product,
            'action_taken' => true
        ];
    }

    /**
     * Handle list products
     */
    private function handleListProducts($message)
    {
        $products = InventoryProduct::where('branch_id', auth()->user()->branch_id ?? 1)
            ->where('status', 'active')
            ->get();

        $count = $products->count();
        $answer = "کل {$count} پروڈکٹس ہیں:\n\n";
        
        foreach ($products as $prod) {
            $answer .= "• {$prod->name} - وزن: {$prod->weight}g, قیمت: {$prod->selling_price}, اسٹاک: {$prod->current_stock}\n";
        }

        return [
            'answer' => $answer,
            'data' => $products,
            'action_taken' => false
        ];
    }

    /**
     * Handle update product
     */
    private function handleUpdateProduct($message, $intent)
    {
        $data = $this->extractProductData($message);
        
        $product = InventoryProduct::where('name', 'like', '%' . ($data['name'] ?? '') . '%')
            ->first();

        if (!$product) {
            return ['answer' => 'پروڈکٹ نہیں ملا۔'];
        }

        $product->update($data);

        return [
            'answer' => "پروڈکٹ {$product->name} اپڈیٹ ہو گیا۔",
            'data' => $product,
            'action_taken' => true
        ];
    }

    /**
     * Handle delete product
     */
    private function handleDeleteProduct($message, $intent)
    {
        $data = $this->extractProductData($message);
        
        $product = InventoryProduct::where('name', 'like', '%' . ($data['name'] ?? '') . '%')
            ->first();

        if (!$product) {
            return ['answer' => 'پروڈکٹ نہیں ملا۔'];
        }

        $name = $product->name;
        $product->delete();

        return [
            'answer' => "پروڈکٹ {$name} کو ہٹا دیا گیا۔",
            'action_taken' => true
        ];
    }

    /**
     * Handle add customer
     */
    private function handleAddCustomer($message, $intent)
    {
        $data = $this->extractCustomerData($message);

        if (empty($data['name'])) {
            return ['answer' => 'براہ کرم کسٹمر کا نام بتائیں۔'];
        }

        $customer = Customer::create([
            'customer_code' => 'CUST' . str_pad(Customer::count() + 1, 4, '0', STR_PAD_LEFT),
            'name' => $data['name'],
            'first_name' => explode(' ', $data['name'])[0],
            'last_name' => implode(' ', array_slice(explode(' ', $data['name']), 1)),
            'email' => $data['email'] ?? 'cust' . time() . '@customer.com',
            'phone' => $data['phone'] ?? '',
            'mobile' => $data['phone'] ?? '',
            'customer_type' => 'individual',
            'branch_id' => auth()->user()->branch_id ?? 1,
            'is_active' => true,
        ]);

        return [
            'answer' => "کسٹمر {$customer->name} کامیابی سے شامل ہو گیا۔ کوڈ: {$customer->customer_code}",
            'data' => $customer,
            'action_taken' => true
        ];
    }

    /**
     * Handle list customers
     */
    private function handleListCustomers($message)
    {
        $customers = Customer::where('branch_id', auth()->user()->branch_id ?? 1)
            ->where('is_active', true)
            ->get();

        $count = $customers->count();
        $answer = "کل {$count} کسٹمرز ہیں:\n\n";
        
        foreach ($customers as $cust) {
            $answer .= "• {$cust->name} - فون: {$cust->phone}, کل خریداری: {$cust->total_purchases}\n";
        }

        return [
            'answer' => $answer,
            'data' => $customers,
            'action_taken' => false
        ];
    }

    /**
     * Handle update customer
     */
    private function handleUpdateCustomer($message, $intent)
    {
        $data = $this->extractCustomerData($message);
        
        $customer = Customer::where('name', 'like', '%' . ($data['name'] ?? '') . '%')
            ->first();

        if (!$customer) {
            return ['answer' => 'کسٹمر نہیں ملا۔'];
        }

        $customer->update($data);

        return [
            'answer' => "کسٹمر {$customer->name} کی معلومات اپڈیٹ ہو گئیں۔",
            'data' => $customer,
            'action_taken' => true
        ];
    }

    /**
     * Handle delete customer
     */
    private function handleDeleteCustomer($message, $intent)
    {
        $data = $this->extractCustomerData($message);
        
        $customer = Customer::where('name', 'like', '%' . ($data['name'] ?? '') . '%')
            ->first();

        if (!$customer) {
            return ['answer' => 'کسٹمر نہیں ملا۔'];
        }

        $name = $customer->name;
        $customer->delete();

        return [
            'answer' => "کسٹمر {$name} کو ہٹا دیا گیا۔",
            'action_taken' => true
        ];
    }

    /**
     * Handle create sale
     */
    private function handleCreateSale($message, $intent)
    {
        $data = $this->extractSaleData($message);

        $sale = PosSale::create([
            'branch_id' => auth()->user()->branch_id ?? 1,
            'pos_customer_id' => $data['customer_id'] ?? null,
            'created_by' => auth()->id(),
            'sale_time' => now(),
            'subtotal' => $data['amount'] ?? 0,
            'total' => $data['amount'] ?? 0,
            'status' => 'completed',
            'payment_status' => 'paid',
            'stock_moved' => true,
        ]);

        return [
            'answer' => "سیل کامیابی سے بنایا گیا۔ انوائس: {$sale->invoice_no}",
            'data' => $sale,
            'action_taken' => true
        ];
    }

    /**
     * Handle list sales
     */
    private function handleListSales($message)
    {
        $sales = PosSale::where('branch_id', auth()->user()->branch_id ?? 1)
            ->latest()
            ->limit(10)
            ->get();

        $count = $sales->count();
        $total = $sales->sum('total');
        $answer = "آخری {$count} سیلز:\n\n";
        
        foreach ($sales as $sale) {
            $answer .= "• انوائس: {$sale->invoice_no} - رقم: {$sale->total} - وقت: {$sale->sale_time}\n";
        }
        
        $answer .= "\nکل رقم: {$total}";

        return [
            'answer' => $answer,
            'data' => $sales,
            'action_taken' => false
        ];
    }

    /**
     * Handle get analytics
     */
    private function handleGetAnalytics($message)
    {
        $totalSales = PosSale::where('branch_id', auth()->user()->branch_id ?? 1)->sum('total');
        $totalEmployees = Employee::where('branch_id', auth()->user()->branch_id ?? 1)->count();
        $totalProducts = InventoryProduct::where('branch_id', auth()->user()->branch_id ?? 1)->count();
        $totalCustomers = Customer::where('branch_id', auth()->user()->branch_id ?? 1)->count();

        $answer = "📊 تجزیہ:\n\n";
        $answer .= "💰 کل فروخت: {$totalSales}\n";
        $answer .= "👥 کل ملازمین: {$totalEmployees}\n";
        $answer .= "📦 کل پروڈکٹس: {$totalProducts}\n";
        $answer .= "🛍️ کل کسٹمرز: {$totalCustomers}";

        return [
            'answer' => $answer,
            'data' => [
                'total_sales' => $totalSales,
                'total_employees' => $totalEmployees,
                'total_products' => $totalProducts,
                'total_customers' => $totalCustomers
            ],
            'action_taken' => false
        ];
    }

    /**
     * Handle get gold rate
     */
    private function handleGetGoldRate($message)
    {
        $rate = GoldRate::latest()->first();

        if (!$rate) {
            return ['answer' => 'سونے کی قیمت دستیاب نہیں۔'];
        }

        $answer = "🏆 سونے کی قیمت:\n\n";
        $answer .= "22K: {$rate->rate_22k} روپے\n";
        $answer .= "24K: {$rate->rate_24k} روپے\n";
        $answer .= "18K: {$rate->rate_18k} روپے\n";
        $answer .= "چاندی: {$rate->silver_rate} روپے";

        return [
            'answer' => $answer,
            'data' => $rate,
            'action_taken' => false
        ];
    }

    /**
     * Handle general query
     */
    private function handleGeneralQuery($message, $language)
    {
        $responses = [
            'سلام' => 'السلام علیکم! میں آپ کی مدد کے لیے یہاں ہوں۔',
            'ہیلو' => 'ہیلو! کیا میں آپ کی مدد کر سکتا ہوں؟',
            'شکریہ' => 'خوش خدمت! کیا کچھ اور مدد چاہیے؟',
            'مدد' => 'میں آپ کو ملازمین، پروڈکٹس، کسٹمرز اور سیلز کے ساتھ مدد کر سکتا ہوں۔',
        ];

        foreach ($responses as $key => $response) {
            if (strpos($message, strtolower($key)) !== false) {
                return ['answer' => $response];
            }
        }

        return ['answer' => 'معافی چاہتا ہوں، میں یہ سمجھ نہیں سکا۔ براہ کرم دوبارہ کوشش کریں۔'];
    }

    /**
     * Extract employee data from message
     */
    private function extractEmployeeData($message)
    {
        $data = [];
        
        // Extract name
        preg_match('/نام[:\s]+([^,\n]+)/i', $message, $matches);
        if (!empty($matches[1])) {
            $names = explode(' ', trim($matches[1]));
            $data['first_name'] = $names[0];
            $data['last_name'] = implode(' ', array_slice($names, 1));
        }

        // Extract salary
        preg_match('/سیلری[:\s]+(\d+)/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['salary'] = (int)$matches[1];
        }

        // Extract email
        preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['email'] = $matches[1];
        }

        // Extract phone
        preg_match('/(\+92|0)\d{10}/i', $message, $matches);
        if (!empty($matches[0])) {
            $data['phone'] = $matches[0];
        }

        return $data;
    }

    /**
     * Extract product data from message
     */
    private function extractProductData($message)
    {
        $data = [];
        
        preg_match('/نام[:\s]+([^,\n]+)/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['name'] = trim($matches[1]);
        }

        preg_match('/وزن[:\s]+(\d+)/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['weight'] = (int)$matches[1];
        }

        preg_match('/قیمت[:\s]+(\d+)/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['selling_price'] = (int)$matches[1];
        }

        preg_match('/اسٹاک[:\s]+(\d+)/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['stock'] = (int)$matches[1];
        }

        return $data;
    }

    /**
     * Extract customer data from message
     */
    private function extractCustomerData($message)
    {
        $data = [];
        
        preg_match('/نام[:\s]+([^,\n]+)/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['name'] = trim($matches[1]);
        }

        preg_match('/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['email'] = $matches[1];
        }

        preg_match('/(\+92|0)\d{10}/i', $message, $matches);
        if (!empty($matches[0])) {
            $data['phone'] = $matches[0];
        }

        return $data;
    }

    /**
     * Extract sale data from message
     */
    private function extractSaleData($message)
    {
        $data = [];
        
        preg_match('/رقم[:\s]+(\d+)/i', $message, $matches);
        if (!empty($matches[1])) {
            $data['amount'] = (int)$matches[1];
        }

        return $data;
    }
}

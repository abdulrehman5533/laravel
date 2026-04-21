<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AIChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $question = trim($request->input('message', ''));
        $conversationId = $request->input('conversation_id', 'default');

        if (! $question) {
            return response()->json([
                'answer' => 'Please provide a message to chat about.',
                'error' => 'empty_message',
            ], 400);
        }

        // Advanced local intent matching and data retrieval
        $answer = $this->processQuery($question);

        return response()->json([
            'answer' => $answer,
            'provider' => 'MAGIA_NATIVE_INTEL_V2',
            'conversation_id' => $conversationId,
            'timestamp' => now()->toISOString(),
        ]);
    }

    private function processQuery(string $query): string
    {
        $q = strtolower($query);

        // Comprehensive intent keywords with weights
        $intents = [
            'sales_today' => ['today', 'sales', 'revenue', 'earned', 'sold', 'collection', 'today\'s'],
            'sales_recent' => ['recent', 'latest', 'last', 'previous', 'transactions', 'history', 'past'],
            'inventory_status' => ['inventory', 'stock', 'items', 'products', 'available', 'count', 'items'],
            'inventory_value' => ['value', 'worth', 'cost', 'price', 'valuation', 'money', 'capital'],
            'inventory_low' => ['low', 'shortage', 'empty', 'reorder', 'alert', 'minimum', 'out of stock'],
            'gold_rates' => ['gold', 'rate', 'price', 'gram', '22k', '24k', '18k', 'silver', 'metal'],
            'girvi_info' => ['girvi', 'pledge', 'loan', 'interest', 'mortgage', 'active girvi'],
            'customer_stats' => ['customer', 'client', 'member', 'people', 'users', 'crm'],
            'identity' => ['who', 'what', 'name', 'you', 'purpose', 'assistant', 'who are you'],
            'help' => ['help', 'can', 'do', 'capabilities', 'how', 'guide', 'features', 'help me'],
            'expenses' => ['expense', 'spending', 'spent', 'payment', 'payout', 'bill'],
        ];

        $scores = [];
        foreach ($intents as $intent => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (str_contains($q, $keyword)) {
                    $score += (str_word_count($keyword) > 1) ? 2 : 1;
                }
            }
            if ($score > 0) {
                $scores[$intent] = $score;
            }
        }

        arsort($scores);
        $topIntent = empty($scores) ? null : key($scores);

        // Specific pattern matching for "How to" and "Search"
        if (preg_match('/how.*(create|make|new).*sale/i', $q)) {
            return $this->getHowToCreateSale();
        }
        if (preg_match('/how.*(print|view|get).*invoice/i', $q)) {
            return $this->getHowToPrintInvoice();
        }
        if (preg_match('/how.*calculate.*gold/i', $q)) {
            return $this->getHowToCalculateGold();
        }

        // Product Search Pattern
        if (preg_match('/(price|stock|available).*of\s+(.+)/i', $q, $matches)) {
            return $this->searchProduct($matches[2]);
        }

        return match ($topIntent) {
            'sales_today' => $this->getTodaySales(),
            'sales_recent' => $this->getRecentSales(),
            'inventory_status' => $this->getInventoryStatus(),
            'inventory_value' => $this->getInventoryValue(),
            'inventory_low' => $this->getLowStockDetails(),
            'gold_rates' => $this->getGoldRates(),
            'girvi_info' => $this->getGirviInfo(),
            'customer_stats' => $this->getCustomerStats(),
            'identity' => $this->getIdentity(),
            'expenses' => $this->getExpenseStats(),
            'help' => $this->getHelp(),
            default => $this->getFallbackResponse($q),
        };
    }

    private function getTodaySales(): string
    {
        $sales = \App\Models\PosSale::whereDate('sale_time', Carbon::today())->where('status', 'completed')->sum('total');

        return "📊 **Financial Intelligence Update**:\n\nToday's total settled sales are **Rs. ".number_format($sales, 2).'**. This reflects all completed transactions from the POS terminal as of '.now()->format('h:i A').'.';
    }

    private function getRecentSales(): string
    {
        $recentSales = \App\Models\PosSale::with('customer')->where('status', 'completed')->latest()->take(3)->get();
        if ($recentSales->isEmpty()) {
            return "I couldn't find any completed sales in the system yet.";
        }

        $resp = "🕒 **Recent Transaction Audit**:\n";
        foreach ($recentSales as $sale) {
            $cust = $sale->customer->name ?? 'Walk-in';
            $resp .= "🔹 **Invoice #{$sale->invoice_no}**: Rs. ".number_format($sale->total, 2)." ($cust) — ".$sale->sale_time->diffForHumans()."\n";
        }

        return $resp;
    }

    private function getInventoryStatus(): string
    {
        $total = \App\Models\InventoryProduct::count();
        $cats = \App\Models\ProductCategory::where('is_active', true)->count();

        return "📦 **Inventory Snapshot**:\n\nYour warehouse currently manages **{$total} unique items** across **{$cats} active categories**. For a detailed breakdown, you can visit the **Inventory > Products** module.";
    }

    private function getLowStockDetails(): string
    {
        $lowItems = \App\Models\InventoryProduct::where('current_stock', '<=', DB::raw('reorder_level'))->take(5)->get();
        if ($lowItems->isEmpty()) {
            return '✅ **Stock Health**: All inventory levels are currently within safe operational ranges.';
        }

        $count = \App\Models\InventoryProduct::where('current_stock', '<=', DB::raw('reorder_level'))->count();
        $resp = "⚠️ **Procurement Alert**: There are **{$count} items** below minimum stock levels.\n\n**Top items needing reorder**:\n";
        foreach ($lowItems as $p) {
            $resp .= "🔸 {$p->name} (Current: {$p->current_stock} {$p->unit})\n";
        }
        $resp .= "\n*I recommend visiting the **Inventory > Low Stock Alert** module to generate purchase orders.*";

        return $resp;
    }

    private function getInventoryValue(): string
    {
        $value = \App\Models\InventoryProduct::sum(DB::raw('current_stock * selling_price'));

        return '💰 **Capital Valuation**: The estimated total retail value of your on-hand stock is **Rs. '.number_format($value, 2).'**. This is a real-time calculation based on current stock levels and selling prices.';
    }

    private function getGoldRates(): string
    {
        $rate = \App\Models\GoldRate::getTodayRate();
        if ($rate) {
            return "✨ **Live Market Intelligence** (Today):\n\n".
                   '🏆 **24K Gold**: Rs. '.number_format($rate->rate_24k, 2)." /g\n".
                   '🥇 **22K Gold**: Rs. '.number_format($rate->rate_22k, 2)." /g\n".
                   '🥈 **18K Gold**: Rs. '.number_format($rate->rate_18k, 2)." /g\n".
                   '⚪ **Silver**: Rs. '.number_format($rate->silver_rate, 2)." /g\n\n".
                   '*Last updated: '.$rate->updated_at->format('M d, H:i').'*';
        }

        return "The gold rates for today haven't been configured yet. Use the **Gold Rates** module to set the base price for calculations.";
    }

    private function getGirviInfo(): string
    {
        $active = \App\Models\Girvi::where('status', 'active')->count();
        $totalAmt = \App\Models\Girvi::where('status', 'active')->sum('loan_amount');

        return "💍 **Girvi (Pledge) Portfolio**:\n\n".
               "Currently, you have **{$active} active pledges** with a total principal exposure of **Rs. ".number_format($totalAmt, 2).'**. '.
               'The system is managing interest accruals and tracking security for these items.';
    }

    private function getCustomerStats(): string
    {
        $count = \App\Models\Customer::count();
        $new = \App\Models\Customer::whereMonth('created_at', now()->month)->count();

        return "👥 **CRM Insights**:\n\nYou have a database of **{$count} registered customers**. This month, **{$new} new clients** have been onboarded. Top tier customers are tracked based on their lifetime loyalty and purchase volume.";
    }

    private function getExpenseStats(): string
    {
        $thisMonth = \App\Models\Expense::whereMonth('date', now()->month)->where('status', 'approved')->sum('amount');

        return "💸 **Financial Outflow**:\n\nTotal approved expenses for ".now()->format('F Y').' amount to **Rs. '.number_format($thisMonth, 2).'**. Check the **Accounts > Expenses** section for detailed line items.';
    }

    private function getIdentity(): string
    {
        $appName = config('app.name', 'MAGIA LUPOS');

        return "I am **MAGIA NATIVE INTEL**, the specialized intelligence core of the **{$appName}**. Unlike general AI (like ChatGPT), I run locally on your server. This means I have **direct, secure access** to your business records, allowing me to provide instant insights without an internet connection or external API keys.";
    }

    private function getHelp(): string
    {
        return "I can help you monitor your business in real-time. Try asking:\n\n".
               "📈 'What are today's sales?'\n".
               "📦 'Show me low stock items'\n".
               "💰 'What is my total inventory value?'\n".
               "✨ 'Show current gold rates'\n".
               "💍 'Active Girvi portfolio'\n".
               "👥 'How many customers do we have?'\n\n".
               'I am designed to be your instant operational data assistant!';
    }

    private function getHowToCreateSale(): string
    {
        return "🛠️ **Sales Workflow Guide**:\n\n1. Open **POS Terminal** > **New Sale**.\n2. Select a customer or use Walk-in.\n3. Add items via search or barcode.\n4. Click **Complete & Pay** to settle the invoice and generate a professional A4 printout.";
    }

    private function getHowToPrintInvoice(): string
    {
        return "🖨️ **Invoice Management**:\n\nGo to **POS Terminal > Sales History**, click the 'View' icon on a transaction, and choose **Print A4** or **Download PDF**. You can also share invoices via WhatsApp directly from the dashboard.";
    }

    private function getHowToCalculateGold(): string
    {
        return "⚖️ **Gold Calculation Engine**:\n\nThe system calculates prices using: `(Weight × Daily Rate) + Making Charges + Stone Costs + Taxes`. Make sure to update Daily Rates in the **Gold Rate** module every morning.";
    }

    private function searchProduct(string $term): string
    {
        $p = \App\Models\InventoryProduct::where('name', 'like', "%{$term}%")
            ->orWhere('sku', 'like', "%{$term}%")
            ->first();

        if (! $p) {
            return "🔍 I searched for '{$term}' but couldn't find a matching item in the inventory.";
        }

        return "🔍 **Product Intelligence**:\n\n".
               "**Name**: {$p->name}\n".
               "**SKU**: {$p->sku}\n".
               '**Price**: Rs. '.number_format($p->selling_price, 2)."\n".
               "**Stock**: {$p->current_stock} {$p->unit}\n".
               '**Weight**: '.number_format($p->weight, 3).'g';
    }

    private function getFallbackResponse(string $q): string
    {
        $appName = config('app.name', 'MAGIA LUPOS');

        return "I'm not quite sure how to answer that specifically about '{$q}'. As your **{$appName} Native Intel**, I'm best at providing data about sales, inventory, and business metrics. Try asking 'Help' for a list of things I can do!";
    }

    public function clearConversation(Request $request)
    {
        return response()->json(['message' => 'Reset complete']);
    }
}

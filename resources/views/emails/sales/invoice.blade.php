<x-mail::message>
# Hello {{ $customerName }},

Thank you for shopping with **{{ config('app.name') }}**.

Your invoice **#{{ $invoiceNo }}** has been generated successfully.

**Order Summary:**
- **Invoice No:** {{ $invoiceNo }}
- **Total Amount:** {{ number_format($totalAmount, 2) }} {{ $currency }}

Please find the attached PDF copy of your invoice for your records.

<x-mail::button :url="config('app.url')">
Visit Our Store
</x-mail::button>

If you have any questions, feel free to contact us at {{ config('pos.shop_phone', 'our support') }}.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

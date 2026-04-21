@component('mail::message')
# Payment Reminder Notice

Dear {{ $customerName }},

We are writing to remind you about an **overdue installment** on your account.

@component('mail::panel')
## Installment Details

**Reference Number:** {{ $schedule->installment->reference_number }}
**Due Date:** {{ $schedule->due_date->format('M d, Y') }}
**Days Overdue:** {{ $daysOverdue }} days
**Amount Due:** Rs.{{ number_format($schedule->amount, 2) }}
**Outstanding Balance:** Rs.{{ number_format($schedule->installment->total_amount - $schedule->installment->paid_amount, 2) }}
@endcomponent

### Payment Details
- **Total Installments:** {{ $schedule->installment->installment_count }}
- **Remaining Installments:** {{ $schedule->installment->installment_count - $schedule->installment->paid_count }}
- **EMI Amount:** Rs.{{ number_format($schedule->amount, 2) }}

**Please arrange payment at your earliest convenience to avoid any inconvenience.**

@component('mail::button', ['url' => route('accounts.installment.show', $schedule->installment_id)])
View Installment Details
@endcomponent

### How to Pay
1. Visit your account portal and make payment
2. Contact our payment team for assistance
3. Bank transfer details are available on your previous invoices

If you have already made this payment, please disregard this notice and accept our thanks.

---

**Need Help?**
If you have any questions or need to discuss payment arrangements, please contact us:
- Email: {{ config('mail.from.address') }}
- Phone: [Your Company Phone Number]
- Hours: Monday - Friday, 9 AM - 6 PM

Thank you for your business!

Best regards,  
**{{ config('app.name') }}**  
Finance & Accounts Team

@endcomponent

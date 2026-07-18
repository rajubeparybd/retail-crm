<x-mail::message>
# Invoice for Order #{{ $sale->id }}

Hi {{ $sale->customer->name ?? 'Customer' }},

Thank you for your purchase. Here are the details of your invoice.

<x-mail::table>
| Product | Quantity | Unit Price | Subtotal |
| :--- | :---: | :---: | :---: |
@foreach ($sale->items as $item)
| {{ $item->product->name ?? 'Product ID: ' . $item->product_id }} | {{ $item->quantity }} | ${{ number_format((float) $item->unit_price, 2) }} | ${{ number_format((float) $item->subtotal, 2) }} |
@endforeach
</x-mail::table>

### **Total: ${{ number_format((float) $sale->total, 2) }}**

If you have any questions about this invoice, simply reply to this email or reach out to our support team for help.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

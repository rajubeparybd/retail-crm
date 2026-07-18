<x-mail::message>
# We Miss You, {{ $customer->name }}!

It's been a while since your last visit to **{{ config('app.name') }}**, and we truly miss having you as a customer.

To welcome you back, we'd like to offer you an **exclusive 10% discount** on your next purchase.

Simply use the promo code below at checkout:

<x-mail::panel>
**COMEBACK10**
</x-mail::panel>

<x-mail::button :url="config('app.url')">
Shop Now & Save 10%
</x-mail::button>

This offer is valid for **14 days** from the date of this email.

Here's why our customers keep coming back:

<x-mail::table>
| Benefit | Details |
|:--------|:--------|
| 🛍️ Wide Selection | Hundreds of products across all categories |
| 🚀 Fast Delivery | Orders dispatched within 24 hours |
| 💬 Expert Support | Friendly team ready to help |
| ♻️ Easy Returns | Hassle-free 30-day returns |
</x-mail::table>

We hope to see you soon. If you have any questions or need assistance, don't hesitate to reach out to our support team.

Warm regards,<br>
The {{ config('app.name') }} Team

---

<small>You are receiving this email because you are a valued customer of {{ config('app.name') }}.
If you no longer wish to receive promotional emails, please contact us to unsubscribe.</small>
</x-mail::message>

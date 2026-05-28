<x-mail::message>
# New customer review

**{{ $review->product?->icon }} {{ $review->product?->name }}** received a new review from **{{ $review->user?->name }}**.

**Rating:** {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}

@if ($review->title)
**"{{ $review->title }}"**

@endif
{{ $review->body }}

---

This review is **pending moderation**. Approve or hide it in your admin panel.

<x-mail::button :url="url('/admin/reviews')">
View in admin
</x-mail::button>

{{ store_name() }}
</x-mail::message>

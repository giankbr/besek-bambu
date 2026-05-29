<?php

namespace App\Exports;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class NewsletterSubscribersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected string $search = '',
        protected string $statusFilter = '',
    ) {}

    public function query(): Builder
    {
        return NewsletterSubscriber::query()
            ->with('coupon')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('email', 'like', "%{$this->search}%")
                        ->orWhereHas('coupon', fn ($c) => $c->where('code', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter === 'sent', fn ($q) => $q->whereNotNull('welcome_sent_at'))
            ->when($this->statusFilter === 'pending', fn ($q) => $q->whereNull('welcome_sent_at'))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'Email',
            'Locale',
            'Coupon code',
            'Welcome sent at',
            'Subscribed at',
        ];
    }

    /**
     * @param  NewsletterSubscriber  $subscriber
     */
    public function map($subscriber): array
    {
        return [
            $subscriber->email,
            $subscriber->locale ?? '',
            $subscriber->coupon?->code ?? '',
            optional($subscriber->welcome_sent_at)->format('Y-m-d H:i:s') ?? '',
            optional($subscriber->created_at)->format('Y-m-d H:i:s') ?? '',
        ];
    }
}

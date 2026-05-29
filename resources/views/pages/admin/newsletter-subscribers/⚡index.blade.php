<?php

use App\Exports\NewsletterSubscribersExport;
use App\Mail\NewsletterCustom;
use App\Models\NewsletterSubscriber;
use App\Services\NewsletterWelcomeService;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

new #[Title('Newsletter subscribers')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    public ?int $deletingId = null;

    public string $composeSubject = '';

    public string $composeBody = '';

    public ?int $composeSubscriberId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function subscribers()
    {
        return $this->filteredQuery()
            ->with('coupon')
            ->latest()
            ->paginate(20);
    }

    #[Computed]
    public function pendingWelcomeCount(): int
    {
        if ($this->statusFilter === 'sent') {
            return 0;
        }

        return $this->filteredQuery()->whereNull('welcome_sent_at')->count();
    }

    protected function filteredQuery()
    {
        return NewsletterSubscriber::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('email', 'like', "%{$this->search}%")
                        ->orWhereHas('coupon', fn ($c) => $c->where('code', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter === 'sent', fn ($q) => $q->whereNotNull('welcome_sent_at'))
            ->when($this->statusFilter === 'pending', fn ($q) => $q->whereNull('welcome_sent_at'));
    }

    #[Computed]
    public function composeRecipientSummary(): string
    {
        if ($this->composeSubscriberId) {
            $email = NewsletterSubscriber::query()
                ->whereKey($this->composeSubscriberId)
                ->value('email');

            return $email ?? '—';
        }

        $count = $this->pendingWelcomeCount;

        return trans_choice(
            ':count subscriber menunggu|:count subscriber menunggu',
            $count,
            ['count' => $count],
        );
    }

    public function openComposeModal(?int $subscriberId = null): void
    {
        $this->composeSubscriberId = $subscriberId;
        $this->composeSubject = '';
        $this->composeBody = '';
        $this->resetValidation();

        Flux::modal('compose-newsletter-email')->show();
    }

    public function applyWelcomeTemplate(): void
    {
        $service = app(NewsletterWelcomeService::class);

        $couponCode = null;
        if ($this->composeSubscriberId) {
            $subscriber = NewsletterSubscriber::find($this->composeSubscriberId);
            if ($subscriber) {
                $couponCode = $service->couponCodeForSubscriber($subscriber);
            }
        }

        $this->composeSubject = store_email_subject(__('Kode diskon 10% untuk Anda'));
        $this->composeBody = $service->welcomeTemplateBody($couponCode);
    }

    public function sendComposedEmail(): void
    {
        $this->validate([
            'composeSubject' => ['required', 'string', 'max:255'],
            'composeBody' => ['required', 'string', 'max:10000'],
        ]);

        $recipients = $this->composeRecipients();

        if ($recipients->isEmpty()) {
            Flux::toast(variant: 'warning', text: __('Tidak ada penerima untuk email ini.'));

            return;
        }

        $service = app(NewsletterWelcomeService::class);
        $sent = 0;
        $failed = 0;
        $lastError = null;

        foreach ($recipients as $subscriber) {
            try {
                $body = $this->personalizeBody($service, $subscriber, $this->composeBody);

                Mail::to($subscriber->email)->send(
                    new NewsletterCustom($subscriber, $this->composeSubject, $body),
                );

                $subscriber->refresh();
                if ($subscriber->welcome_sent_at === null && $subscriber->coupon_id) {
                    $subscriber->update(['welcome_sent_at' => now()]);
                }

                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $lastError = $e->getMessage();
                Log::warning('Newsletter custom email failed', [
                    'subscriber_id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Flux::modal('compose-newsletter-email')->close();
        $this->composeSubscriberId = null;
        unset($this->subscribers, $this->pendingWelcomeCount, $this->composeRecipientSummary);

        if ($sent > 0 && $failed === 0) {
            Flux::toast(
                variant: 'success',
                text: trans_choice(':count email terkirim.|:count email terkirim.', $sent, ['count' => $sent]),
            );
        } elseif ($sent > 0) {
            Flux::toast(
                variant: 'warning',
                heading: __('Sebagian gagal'),
                text: __(':sent terkirim, :failed gagal. Periksa konfigurasi mail.', ['sent' => $sent, 'failed' => $failed]),
            );
        } else {
            Flux::toast(
                variant: 'danger',
                heading: __('Gagal mengirim'),
                text: $lastError ?: __('Periksa konfigurasi mail di .env (MAIL_*).'),
            );
        }
    }

    protected function composeRecipients(): Collection
    {
        if ($this->composeSubscriberId) {
            return NewsletterSubscriber::query()
                ->whereKey($this->composeSubscriberId)
                ->get();
        }

        return $this->filteredQuery()
            ->whereNull('welcome_sent_at')
            ->orderBy('id')
            ->get();
    }

    protected function personalizeBody(NewsletterWelcomeService $service, NewsletterSubscriber $subscriber, string $body): string
    {
        if (! str_contains($body, '{KODE_KUPON}')) {
            return $body;
        }

        $code = $service->couponCodeForSubscriber($subscriber);

        return str_replace('{KODE_KUPON}', $code, $body);
    }

    public function exportXlsx()
    {
        $filename = 'newsletter-subscribers-'.Carbon::now()->format('Ymd-His').'.xlsx';

        return Excel::download(
            new NewsletterSubscribersExport($this->search, $this->statusFilter),
            $filename,
        );
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        Flux::modal('delete-newsletter-subscriber')->show();
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        try {
            NewsletterSubscriber::where('id', $this->deletingId)->delete();
            $this->deletingId = null;
            Flux::modal('delete-newsletter-subscriber')->close();
            Flux::toast(variant: 'success', text: __('Subscriber removed.'));
            unset($this->subscribers);
        } catch (\Throwable $e) {
            Flux::modal('delete-newsletter-subscriber')->close();
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to delete'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Newsletter subscribers') }}</flux:heading>
                <flux:subheading>{{ __('Emails from the homepage signup form, with welcome discount codes.') }}</flux:subheading>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <flux:button
                    type="button"
                    wire:click="openComposeModal"
                    variant="primary"
                    icon="envelope"
                    :disabled="$this->pendingWelcomeCount === 0"
                >
                    {{ __('Buat email') }}
                </flux:button>
                <flux:button wire:click="exportXlsx" variant="ghost" icon="arrow-down-tray">
                    {{ __('Export XLSX') }}
                </flux:button>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search email or coupon…') }}"
                class="max-w-sm"
            />
            <flux:select wire:model.live="statusFilter" class="max-w-xs">
                <flux:select.option value="">{{ __('All') }}</flux:select.option>
                <flux:select.option value="sent">{{ __('Welcome sent') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Welcome pending') }}</flux:select.option>
            </flux:select>
        </div>

        <div class="flex flex-col gap-2 md:hidden">
            @forelse ($this->subscribers as $subscriber)
                <div class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="font-medium">{{ $subscriber->email }}</div>
                    <div class="mt-1 flex flex-wrap gap-2 text-xs text-zinc-500">
                        @if ($subscriber->coupon)
                            <span class="font-mono">{{ $subscriber->coupon->code }}</span>
                        @endif
                        <span>{{ $subscriber->created_at?->format('d M Y H:i') }}</span>
                    </div>
                    <div class="mt-2">
                        @if ($subscriber->welcome_sent_at)
                            <flux:badge color="green" size="sm">{{ __('Welcome sent') }}</flux:badge>
                        @else
                            <flux:badge color="amber" size="sm">{{ __('Pending') }}</flux:badge>
                        @endif
                    </div>
                    <div class="mt-2 flex justify-end gap-1">
                        @if ($subscriber->welcome_sent_at)
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="paper-airplane"
                                wire:click="openComposeModal({{ $subscriber->id }})"
                            >
                                {{ __('Kirim ulang') }}
                            </flux:button>
                        @else
                            <flux:button
                                size="sm"
                                variant="primary"
                                icon="envelope"
                                wire:click="openComposeModal({{ $subscriber->id }})"
                            >
                                {{ __('Kirim email') }}
                            </flux:button>
                        @endif
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="confirmDelete({{ $subscriber->id }})" />
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-zinc-500">{{ __('No subscribers yet.') }}</p>
            @endforelse
            {{ $this->subscribers->links() }}
        </div>

        <div class="hidden md:block">
            <flux:table :paginate="$this->subscribers">
                <flux:table.columns>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Coupon') }}</flux:table.column>
                    <flux:table.column>{{ __('Welcome') }}</flux:table.column>
                    <flux:table.column>{{ __('Subscribed') }}</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->subscribers as $subscriber)
                        <flux:table.row :key="$subscriber->id">
                            <flux:table.cell>{{ $subscriber->email }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($subscriber->coupon)
                                    <span class="font-mono text-sm">{{ $subscriber->coupon->code }}</span>
                                @else
                                    —
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($subscriber->welcome_sent_at)
                                    <flux:badge color="green" size="sm">{{ __('Sent') }}</flux:badge>
                                    <flux:text size="sm" class="text-zinc-500">{{ $subscriber->welcome_sent_at->format('d M Y H:i') }}</flux:text>
                                @else
                                    <flux:badge color="amber" size="sm">{{ __('Pending') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $subscriber->created_at?->format('d M Y H:i') ?? '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center justify-end gap-1">
                                    @if ($subscriber->welcome_sent_at)
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="paper-airplane"
                                            wire:click="openComposeModal({{ $subscriber->id }})"
                                        >
                                            {{ __('Kirim ulang') }}
                                        </flux:button>
                                    @else
                                        <flux:button
                                            size="sm"
                                            variant="primary"
                                            icon="envelope"
                                            wire:click="openComposeModal({{ $subscriber->id }})"
                                        >
                                            {{ __('Kirim email') }}
                                        </flux:button>
                                    @endif
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="confirmDelete({{ $subscriber->id }})"
                                    />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500">
                                {{ __('No subscribers yet.') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <flux:modal name="compose-newsletter-email" class="md:w-2xl">
        <form wire:submit="sendComposedEmail" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Buat email') }}</flux:heading>
                <flux:subheading>{{ __('Tulis subjek dan isi email, lalu kirim ke subscriber.') }}</flux:subheading>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                <flux:text size="sm" class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Penerima') }}</flux:text>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">{{ $this->composeRecipientSummary }}</flux:text>
            </div>

            <flux:input
                wire:model="composeSubject"
                :label="__('Subjek')"
                placeholder="{{ __('Contoh: Promo spesial untuk Anda') }}"
            />

            <flux:textarea
                wire:model="composeBody"
                :label="__('Isi email')"
                rows="10"
                placeholder="{{ __('Tulis pesan untuk subscriber…') }}"
            />

            <div class="flex flex-wrap gap-2">
                <flux:button type="button" variant="ghost" size="sm" wire:click="applyWelcomeTemplate">
                    {{ __('Isi template welcome + kupon') }}
                </flux:button>
            </div>
            <flux:text size="sm" class="text-zinc-500">
                {{ __('Untuk kirim massal, gunakan {KODE_KUPON}. Kode unik dibuat otomatis per subscriber.') }}
            </flux:text>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button
                    type="submit"
                    variant="primary"
                    icon="paper-airplane"
                    wire:loading.attr="disabled"
                    wire:target="sendComposedEmail"
                >
                    <span wire:loading.remove wire:target="sendComposedEmail">{{ __('Kirim email') }}</span>
                    <span wire:loading wire:target="sendComposedEmail">{{ __('Mengirim…') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <x-admin.confirm-modal
        name="delete-newsletter-subscriber"
        :title="__('Remove this subscriber?')"
        :description="__('They can sign up again from the storefront. The coupon record is kept in Coupons.')"
        action="delete"
    />
</section>

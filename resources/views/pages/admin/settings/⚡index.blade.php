<?php

use App\Models\Setting;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Store settings')] class extends Component {
    use WithFileUploads;

    #[Url(as: 'tab')]
    public string $tab = 'general';

    public string $store_name = '';
    public string $store_tagline = '';
    public string $store_email = '';
    public string $store_phone = '';
    public string $store_address = '';
    public string $store_logo = '';
    public $logo_upload = null;

    public string $social_instagram = '';
    public string $social_facebook = '';
    public string $social_whatsapp = '';
    public string $social_tiktok = '';

    public array $shipping_zones = [];

    public string $tax_rate = '0';
    public bool $tax_inclusive = false;

    public bool $payment_midtrans = true;
    public bool $payment_manual_transfer = false;
    public bool $payment_cod = false;
    public string $payment_bank_info = '';

    public string $email_from_name = '';
    public string $email_from_address = '';

    public function mount(): void
    {
        $this->store_name = (string) Setting::get('store_name', config('app.name'));
        $this->store_tagline = (string) Setting::get('store_tagline', '');
        $this->store_email = (string) Setting::get('store_email', '');
        $this->store_phone = (string) Setting::get('store_phone', '');
        $this->store_address = (string) Setting::get('store_address', '');
        $this->store_logo = (string) Setting::get('store_logo', '');

        $this->social_instagram = (string) Setting::get('social_instagram', '');
        $this->social_facebook = (string) Setting::get('social_facebook', '');
        $this->social_whatsapp = (string) Setting::get('social_whatsapp', '');
        $this->social_tiktok = (string) Setting::get('social_tiktok', '');

        $zones = Setting::get('shipping_zones', []);
        $this->shipping_zones = is_array($zones) && count($zones) > 0
            ? array_values(array_map(fn ($z) => [
                'name' => (string) ($z['name'] ?? ''),
                'cost' => (string) ($z['cost'] ?? '0'),
            ], $zones))
            : [];

        $this->tax_rate = (string) Setting::get('tax_rate', '0');
        $this->tax_inclusive = (bool) Setting::get('tax_inclusive', false);

        $this->payment_midtrans = (bool) Setting::get('payment_midtrans', true);
        $this->payment_manual_transfer = (bool) Setting::get('payment_manual_transfer', false);
        $this->payment_cod = (bool) Setting::get('payment_cod', false);
        $this->payment_bank_info = (string) Setting::get('payment_bank_info', '');

        $this->email_from_name = (string) Setting::get('email_from_name', config('mail.from.name'));
        $this->email_from_address = (string) Setting::get('email_from_address', config('mail.from.address'));
    }

    public function addZone(): void
    {
        $this->shipping_zones[] = ['name' => '', 'cost' => '0'];
    }

    public function removeZone(int $index): void
    {
        unset($this->shipping_zones[$index]);
        $this->shipping_zones = array_values($this->shipping_zones);
    }

    public function saveGeneral(): void
    {
        try {
            $this->validate([
                'store_name' => ['required', 'string', 'max:120'],
                'store_tagline' => ['nullable', 'string', 'max:255'],
                'store_email' => ['nullable', 'email', 'max:255'],
                'store_phone' => ['nullable', 'string', 'max:50'],
                'store_address' => ['nullable', 'string', 'max:1000'],
                'logo_upload' => ['nullable', 'image', 'max:2048'],
            ]);

            if ($this->logo_upload) {
                $path = $this->logo_upload->store('settings', 'public');
                Setting::put('store_logo', $path);
                $this->store_logo = $path;
                $this->logo_upload = null;
            }

            Setting::put('store_name', $this->store_name);
            Setting::put('store_tagline', $this->store_tagline);
            Setting::put('store_email', $this->store_email);
            Setting::put('store_phone', $this->store_phone);
            Setting::put('store_address', $this->store_address);

            Setting::put('social_instagram', $this->social_instagram);
            Setting::put('social_facebook', $this->social_facebook);
            Setting::put('social_whatsapp', $this->social_whatsapp);
            Setting::put('social_tiktok', $this->social_tiktok);

            Flux::toast(variant: 'success', text: __('Settings saved.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to save'), text: $e->getMessage());
        }
    }

    public function clearLogo(): void
    {
        try {
            Setting::put('store_logo', '');
            $this->store_logo = '';
            Flux::toast(variant: 'success', text: __('Logo removed.'));
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed'), text: $e->getMessage());
        }
    }

    public function saveShipping(): void
    {
        try {
            $this->validate([
                'shipping_zones' => ['array'],
                'shipping_zones.*.name' => ['required_with:shipping_zones.*.cost', 'string', 'max:120'],
                'shipping_zones.*.cost' => ['required_with:shipping_zones.*.name', 'numeric', 'min:0'],
            ]);

            $clean = collect($this->shipping_zones)
                ->filter(fn ($z) => trim((string) ($z['name'] ?? '')) !== '')
                ->map(fn ($z) => [
                    'name' => trim((string) $z['name']),
                    'cost' => (float) $z['cost'],
                ])
                ->values()
                ->all();

            Setting::put('shipping_zones', $clean);
            $this->shipping_zones = array_map(fn ($z) => [
                'name' => $z['name'],
                'cost' => (string) $z['cost'],
            ], $clean);

            Flux::toast(variant: 'success', text: __('Shipping zones saved.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to save'), text: $e->getMessage());
        }
    }

    public function saveTax(): void
    {
        try {
            $this->validate([
                'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
                'tax_inclusive' => ['boolean'],
            ]);

            Setting::put('tax_rate', (float) $this->tax_rate);
            Setting::put('tax_inclusive', (bool) $this->tax_inclusive);

            Flux::toast(variant: 'success', text: __('Tax settings saved.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to save'), text: $e->getMessage());
        }
    }

    public function savePayment(): void
    {
        try {
            $this->validate([
                'payment_midtrans' => ['boolean'],
                'payment_manual_transfer' => ['boolean'],
                'payment_cod' => ['boolean'],
                'payment_bank_info' => ['nullable', 'string', 'max:2000'],
            ]);

            Setting::put('payment_midtrans', (bool) $this->payment_midtrans);
            Setting::put('payment_manual_transfer', (bool) $this->payment_manual_transfer);
            Setting::put('payment_cod', (bool) $this->payment_cod);
            Setting::put('payment_bank_info', $this->payment_bank_info);

            Flux::toast(variant: 'success', text: __('Payment settings saved.'));
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to save'), text: $e->getMessage());
        }
    }

    public function saveEmail(): void
    {
        try {
            $this->validate([
                'email_from_name' => ['nullable', 'string', 'max:120'],
                'email_from_address' => ['nullable', 'email', 'max:255'],
            ]);

            Setting::put('email_from_name', $this->email_from_name);
            Setting::put('email_from_address', $this->email_from_address);

            Flux::toast(variant: 'success', text: __('Email settings saved.'));
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to save'), text: $e->getMessage());
        }
    }

    public array $tabs = [
        'general' => 'General',
        'shipping' => 'Shipping',
        'tax' => 'Tax',
        'payment' => 'Payment',
        'email' => 'Email',
    ];
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Store settings') }}</flux:heading>
            <flux:subheading>{{ __('Configure how your store presents itself and operates.') }}</flux:subheading>
        </div>

        <nav class="flex flex-wrap items-center gap-1 border-b border-zinc-200 dark:border-zinc-700">
            @foreach ($tabs as $key => $label)
                @php
                    $active = $tab === $key
                @endphp
                <button
                    type="button"
                    wire:click="$set('tab', '{{ $key }}')"
                    @class([
                        '-mb-px px-4 py-2 text-sm font-medium border-b-2 transition-colors cursor-pointer',
                        'border-emerald-500 text-emerald-600 dark:text-emerald-400' => $active,
                        'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' => !$active,
                    ])
                >
                    {{ __($label) }}
                </button>
            @endforeach
        </nav>

        @if ($tab === 'general')
            <form wire:submit="saveGeneral" class="grid w-full gap-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="store_name" :label="__('Store name')" required />
                    <flux:input wire:model="store_tagline" :label="__('Tagline (optional)')" />
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="store_email" :label="__('Public email')" type="email" />
                    <flux:input wire:model="store_phone" :label="__('Phone / WhatsApp')" />
                </div>

                <flux:textarea wire:model="store_address" :label="__('Business address')" rows="3" />

                <div class="grid gap-3">
                    <flux:label>{{ __('Logo') }}</flux:label>
                    @if ($store_logo)
                        <div class="flex items-center gap-3">
                            <img src="{{ image_src($store_logo) }}" alt="{{ __('Logo') }}" class="h-16 w-16 rounded-lg border border-zinc-200 object-contain p-1 dark:border-zinc-700" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="clearLogo" wire:confirm="{{ __('Remove logo?') }}" type="button">
                                {{ __('Remove') }}
                            </flux:button>
                        </div>
                    @endif
                    <flux:input type="file" wire:model="logo_upload" accept="image/*" />
                    @if ($logo_upload)
                        <flux:text size="sm" class="text-zinc-500">{{ __('New file selected. Save to apply.') }}</flux:text>
                    @endif
                </div>

                <flux:separator />

                <flux:heading size="lg">{{ __('Social links') }}</flux:heading>
                <div class="grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="social_instagram" :label="__('Instagram URL')" placeholder="https://instagram.com/…" />
                    <flux:input wire:model="social_facebook" :label="__('Facebook URL')" placeholder="https://facebook.com/…" />
                    <flux:input wire:model="social_tiktok" :label="__('TikTok URL')" placeholder="https://tiktok.com/@…" />
                    <flux:input wire:model="social_whatsapp" :label="__('WhatsApp link')" placeholder="https://wa.me/62…" />
                </div>

                <div>
                    <flux:button type="submit" variant="primary">{{ __('Save general') }}</flux:button>
                </div>
            </form>
        @endif

        @if ($tab === 'shipping')
            <form wire:submit="saveShipping" class="grid w-full gap-5">
                <flux:text class="text-zinc-500">
                    {{ __('Define flat shipping costs per region. Use these names when configuring orders.') }}
                </flux:text>

                <div class="grid gap-3">
                    @forelse ($shipping_zones as $i => $zone)
                        <div class="grid items-end gap-3 md:grid-cols-[1fr_240px_auto]" wire:key="zone-{{ $i }}">
                            <flux:input wire:model="shipping_zones.{{ $i }}.name" :label="$i === 0 ? __('Region / zone') : ''" placeholder="{{ __('e.g. Jakarta') }}" />
                            <flux:input wire:model="shipping_zones.{{ $i }}.cost" :label="$i === 0 ? __('Cost (Rp)') : ''" type="number" min="0" step="1" />
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="removeZone({{ $i }})" type="button">
                                {{ __('Remove') }}
                            </flux:button>
                        </div>
                    @empty
                        <flux:text class="text-zinc-500">{{ __('No zones defined yet.') }}</flux:text>
                    @endforelse
                </div>

                <div class="flex items-center gap-3">
                    <flux:button variant="ghost" icon="plus" wire:click="addZone" type="button">{{ __('Add zone') }}</flux:button>
                </div>

                <div>
                    <flux:button type="submit" variant="primary">{{ __('Save shipping') }}</flux:button>
                </div>
            </form>
        @endif

        @if ($tab === 'tax')
            <form wire:submit="saveTax" class="grid w-full gap-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="tax_rate" :label="__('Tax rate (%)')" type="number" min="0" max="100" step="0.01" />
                </div>
                <flux:checkbox wire:model="tax_inclusive" :label="__('Prices already include tax')" />
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('When unchecked, tax is added on top of subtotal at checkout. When checked, listed prices are treated as gross.') }}
                </flux:text>
                <div>
                    <flux:button type="submit" variant="primary">{{ __('Save tax') }}</flux:button>
                </div>
            </form>
        @endif

        @if ($tab === 'payment')
            <form wire:submit="savePayment" class="grid w-full gap-5">
                <div class="flex flex-col gap-3">
                    <flux:checkbox wire:model="payment_midtrans" :label="__('Midtrans (online payment)')" />
                    <flux:checkbox wire:model="payment_manual_transfer" :label="__('Manual bank transfer')" />
                    <flux:checkbox wire:model="payment_cod" :label="__('Cash on delivery (COD)')" />
                </div>
                <flux:textarea
                    wire:model="payment_bank_info"
                    :label="__('Manual transfer instructions (shown to customer)')"
                    rows="4"
                    placeholder="BCA 1234567890 a/n Besek Bambu"
                />
                <div>
                    <flux:button type="submit" variant="primary">{{ __('Save payment') }}</flux:button>
                </div>
            </form>
        @endif

        @if ($tab === 'email')
            <form wire:submit="saveEmail" class="grid w-full gap-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <flux:input wire:model="email_from_name" :label="__('From name')" />
                    <flux:input wire:model="email_from_address" :label="__('From address')" type="email" />
                </div>
                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Used as the sender for transactional emails (order confirmations, password resets).') }}
                </flux:text>
                <div>
                    <flux:button type="submit" variant="primary">{{ __('Save email') }}</flux:button>
                </div>
            </form>
        @endif
    </div>
</section>

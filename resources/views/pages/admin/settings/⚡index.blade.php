<?php

use App\Models\Setting;
use App\Services\RajaOngkirClient;
use App\Services\ShippingService;
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

    public string $shipping_provider = ShippingService::PROVIDER_FLAT;
    public string $shipping_rajaongkir_api_key = '';
    public string $shipping_origin_city_id = '';
    public string $shipping_origin_label = '';
    public array $shipping_couriers = [];

    public string $origin_search = '';
    public array $origin_results = [];
    public string $origin_search_error = '';
    public bool $origin_searching = false;

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

        $this->shipping_provider = (string) Setting::get('shipping_provider', ShippingService::PROVIDER_FLAT);
        $this->shipping_rajaongkir_api_key = (string) Setting::get('shipping_rajaongkir_api_key', '');
        $this->shipping_origin_city_id = (string) Setting::get('shipping_origin_city_id', '');
        $this->shipping_origin_label = (string) Setting::get('shipping_origin_label', '');
        $couriers = Setting::get('shipping_couriers', []);
        $this->shipping_couriers = is_array($couriers) ? array_values($couriers) : [];

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
            $supportedCouriers = array_keys(RajaOngkirClient::SUPPORTED_COURIERS);

            $this->validate([
                'shipping_provider' => ['required', 'in:flat,rajaongkir'],
                'shipping_rajaongkir_api_key' => ['nullable', 'string', 'max:255'],
                'shipping_origin_city_id' => ['nullable', 'string', 'max:32'],
                'shipping_origin_label' => ['nullable', 'string', 'max:255'],
                'shipping_couriers' => ['array'],
                'shipping_couriers.*' => ['string', \Illuminate\Validation\Rule::in($supportedCouriers)],
                'shipping_zones' => ['array'],
                'shipping_zones.*.name' => ['required_with:shipping_zones.*.cost', 'string', 'max:120'],
                'shipping_zones.*.cost' => ['required_with:shipping_zones.*.name', 'numeric', 'min:0'],
            ]);

            if ($this->shipping_provider === ShippingService::PROVIDER_RAJAONGKIR) {
                if (! $this->shipping_rajaongkir_api_key) {
                    throw ValidationException::withMessages([
                        'shipping_rajaongkir_api_key' => __('API key is required when using RajaOngkir.'),
                    ]);
                }
                if (! $this->shipping_origin_city_id) {
                    throw ValidationException::withMessages([
                        'shipping_origin_city_id' => __('Search and pick your origin (warehouse) location.'),
                    ]);
                }
                if (empty($this->shipping_couriers)) {
                    throw ValidationException::withMessages([
                        'shipping_couriers' => __('Enable at least one courier.'),
                    ]);
                }
            }

            $clean = collect($this->shipping_zones)
                ->filter(fn ($z) => trim((string) ($z['name'] ?? '')) !== '')
                ->map(fn ($z) => [
                    'name' => trim((string) $z['name']),
                    'cost' => (float) $z['cost'],
                ])
                ->values()
                ->all();

            Setting::put('shipping_provider', $this->shipping_provider);
            Setting::put('shipping_rajaongkir_api_key', $this->shipping_rajaongkir_api_key);
            Setting::put('shipping_origin_city_id', $this->shipping_origin_city_id);
            Setting::put('shipping_origin_label', $this->shipping_origin_label);
            Setting::put('shipping_couriers', array_values($this->shipping_couriers));
            Setting::put('shipping_zones', $clean);

            $this->shipping_zones = array_map(fn ($z) => [
                'name' => $z['name'],
                'cost' => (string) $z['cost'],
            ], $clean);

            Flux::toast(variant: 'success', text: __('Shipping settings saved.'));
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

    public function testRajaOngkirKey(): void
    {
        try {
            if (! $this->shipping_rajaongkir_api_key) {
                throw new \DomainException(__('Enter an API key first.'));
            }

            $client = new RajaOngkirClient($this->shipping_rajaongkir_api_key);
            $results = $client->searchDestinations('jakarta', 1);

            if (empty($results)) {
                throw new \RuntimeException(__('Connected, but no results returned.'));
            }

            Flux::toast(
                variant: 'success',
                heading: __('Connection OK'),
                text: __('RajaOngkir V2 (Komerce) is reachable.'),
            );
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Connection failed'), text: $e->getMessage());
        }
    }

    public function clearOrigin(): void
    {
        $this->shipping_origin_city_id = '';
        $this->shipping_origin_label = '';
        $this->origin_search = '';
        $this->origin_results = [];
        $this->origin_search_error = '';
    }

    public function searchOrigin(): void
    {
        $this->origin_results = [];
        $this->origin_search_error = '';

        $term = trim($this->origin_search);

        if (mb_strlen($term) < 2) {
            return;
        }

        if (! $this->shipping_rajaongkir_api_key) {
            $this->origin_search_error = __('Enter and save your API key first.');

            return;
        }

        try {
            $this->origin_searching = true;
            $client = new RajaOngkirClient($this->shipping_rajaongkir_api_key);
            $this->origin_results = $client->searchDestinations($term, 15);

            if (empty($this->origin_results)) {
                $this->origin_search_error = __('No matches found. Try the city or postal code.');
            }
        } catch (\Throwable $e) {
            $this->origin_search_error = $e->getMessage();
        } finally {
            $this->origin_searching = false;
        }
    }

    public function pickOrigin(int $id, string $label): void
    {
        $this->shipping_origin_city_id = (string) $id;
        $this->shipping_origin_label = $label;
        $this->origin_results = [];
        $this->origin_search = '';
        $this->origin_search_error = '';
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
            <form wire:submit="saveShipping" class="grid w-full gap-6">
                <div class="grid gap-3">
                    <flux:label>{{ __('Shipping provider') }}</flux:label>
                    <flux:radio.group wire:model.live="shipping_provider" variant="segmented">
                        <flux:radio value="flat" :label="__('Flat-rate zones (manual)')" />
                        <flux:radio value="rajaongkir" :label="__('RajaOngkir V2 / Komerce (live rates)')" />
                    </flux:radio.group>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('RajaOngkir provides live shipping costs per courier. Flat-rate zones remain as a fallback if the API is unreachable.') }}
                    </flux:text>
                </div>

                @if ($shipping_provider === 'rajaongkir')
                    <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('RajaOngkir V2 (Komerce)') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">
                            {{ __('Get an API key at rajaongkir.com (powered by Komerce). The new endpoint is rajaongkir.komerce.id/api/v1.') }}
                        </flux:text>

                        <div class="mt-4 grid gap-5">
                            <flux:input
                                wire:model="shipping_rajaongkir_api_key"
                                :label="__('API key')"
                                type="password"
                                viewable
                                autocomplete="off"
                                placeholder="••••••••"
                            />

                            <div>
                                <flux:button
                                    type="button"
                                    variant="filled"
                                    icon="signal"
                                    wire:click="testRajaOngkirKey"
                                    wire:loading.attr="disabled"
                                    wire:target="testRajaOngkirKey"
                                >
                                    <span wire:loading.remove wire:target="testRajaOngkirKey">{{ __('Test connection') }}</span>
                                    <span wire:loading wire:target="testRajaOngkirKey">{{ __('Testing…') }}</span>
                                </flux:button>
                            </div>

                            <flux:separator />

                            <flux:heading size="md">{{ __('Origin (warehouse location)') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500">
                                {{ __('Search by district, city, or postal code (e.g. "Yogyakarta", "Sleman", "55281").') }}
                            </flux:text>

                            @if ($shipping_origin_city_id)
                                <div class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900 dark:bg-emerald-950/30">
                                    <div class="flex-1">
                                        <flux:text class="font-medium">{{ $shipping_origin_label ?: __('(no label)') }}</flux:text>
                                        <flux:text size="sm" class="text-zinc-500">ID: {{ $shipping_origin_city_id }}</flux:text>
                                    </div>
                                    <flux:button size="sm" variant="ghost" icon="x-mark" type="button" wire:click="clearOrigin">{{ __('Change') }}</flux:button>
                                </div>
                            @else
                                <div class="flex flex-col gap-2">
                                    <div class="flex gap-2">
                                        <flux:input
                                            wire:model.live.debounce.500ms="origin_search"
                                            wire:keydown.enter.prevent="searchOrigin"
                                            placeholder="{{ __('Type at least 2 characters then press Enter') }}"
                                            class="flex-1"
                                            autocomplete="off"
                                        />
                                        <flux:button
                                            type="button"
                                            variant="filled"
                                            icon="magnifying-glass"
                                            wire:click="searchOrigin"
                                            wire:loading.attr="disabled"
                                            wire:target="searchOrigin,origin_search"
                                        >
                                            <span wire:loading.remove wire:target="searchOrigin,origin_search">{{ __('Search') }}</span>
                                            <span wire:loading wire:target="searchOrigin,origin_search">{{ __('Searching…') }}</span>
                                        </flux:button>
                                    </div>

                                    @if ($origin_search_error)
                                        <flux:text class="text-rose-500 text-sm">{{ $origin_search_error }}</flux:text>
                                    @endif

                                    @if (! empty($origin_results))
                                        <div class="max-h-72 overflow-y-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                                            @foreach ($origin_results as $r)
                                                <button
                                                    type="button"
                                                    wire:click="pickOrigin({{ (int) $r['id'] }}, @js($r['label']))"
                                                    class="block w-full cursor-pointer border-b border-zinc-100 px-3 py-2 text-left text-sm last:border-b-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-700"
                                                >
                                                    <div class="font-medium">{{ $r['label'] }}</div>
                                                    <div class="text-xs text-zinc-500">ID: {{ $r['id'] }}</div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                @error('shipping_origin_city_id')<flux:text class="text-rose-500 text-sm">{{ $message }}</flux:text>@enderror
                            @endif

                            <flux:separator />

                            <flux:heading size="md">{{ __('Enabled couriers') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500">
                                {{ __('Pick which couriers to offer at checkout. Some require a paid tier or special account on RajaOngkir.') }}
                            </flux:text>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                @foreach (\App\Services\RajaOngkirClient::SUPPORTED_COURIERS as $code => $name)
                                    <flux:checkbox wire:model="shipping_couriers" value="{{ $code }}" label="{{ $name }}" />
                                @endforeach
                            </div>
                            @error('shipping_couriers')<flux:text class="text-rose-500 text-sm">{{ $message }}</flux:text>@enderror
                        </div>
                    </div>
                @endif

                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Flat-rate zones') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-500">
                        {{ __('Used when RajaOngkir is disabled, the API fails, or no destination is picked.') }}
                    </flux:text>

                    <div class="mt-4 grid gap-3">
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

                    <div class="mt-4 flex items-center gap-3">
                        <flux:button variant="ghost" icon="plus" wire:click="addZone" type="button">{{ __('Add zone') }}</flux:button>
                    </div>
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

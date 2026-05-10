<?php

use App\Models\Order;
use App\Services\ShippingService;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order detail')] class extends Component {
    public Order $order;

    public string $status = 'pending';
    public string $tracking_number = '';

    public array $tracking_data = [];
    public string $tracking_error = '';

    public function mount(Order $order): void
    {
        $this->order = $order->load('items');
        $this->status = $order->status;
        $this->tracking_number = (string) ($order->tracking_number ?? '');
    }

    public function updateStatus(): void
    {
        try {
            $this->validate([
                'status' => ['required', Rule::in(Order::STATUSES)],
            ]);

            $previous = $this->order->status;

            $patch = ['status' => $this->status];

            if ($this->status === 'shipped' && ! $this->order->shipped_at) {
                $patch['shipped_at'] = now();
            }

            if ($this->status === 'delivered' && ! $this->order->delivered_at) {
                $patch['delivered_at'] = now();
            }

            $this->order->update($patch);
            $this->order->refresh();

            $this->dispatchStatusMail($previous, $this->status);

            Flux::toast(variant: 'success', text: __('Status updated.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: collect($e->validator->errors()->all())->first() ?? __('Invalid status.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: $e->getMessage(),
            );
        }
    }

    public function saveTrackingNumber(): void
    {
        try {
            $this->validate([
                'tracking_number' => ['nullable', 'string', 'max:64'],
            ]);

            $tracking = trim($this->tracking_number);
            $this->order->update(['tracking_number' => $tracking ?: null]);
            $this->order->refresh();

            if ($tracking && $this->order->status === 'pending' || $tracking && $this->order->status === 'paid') {
                $this->status = 'shipped';
                $this->order->update([
                    'status' => 'shipped',
                    'shipped_at' => now(),
                ]);
                $this->order->refresh();
                $this->dispatchStatusMail('paid', 'shipped');
            }

            Flux::toast(variant: 'success', text: __('Tracking number saved.'));
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed to save'), text: $e->getMessage());
        }
    }

    public function trackPackage(ShippingService $shipping): void
    {
        $this->tracking_data = [];
        $this->tracking_error = '';

        try {
            if (! $this->order->tracking_number || ! $this->order->shipping_courier) {
                throw new \DomainException(__('Set the tracking number and courier first.'));
            }

            $client = $shipping->rajaOngkirClient();

            if (! $client->isConfigured()) {
                throw new \DomainException(__('RajaOngkir is not configured.'));
            }

            $this->tracking_data = $client->trackWaybill(
                $this->order->tracking_number,
                $this->order->shipping_courier,
                $this->order->customer_phone,
            );
        } catch (\Throwable $e) {
            $this->tracking_error = $e->getMessage();
        }
    }

    private function dispatchStatusMail(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        try {
            match ($to) {
                'shipped' => \Illuminate\Support\Facades\Mail::to($this->order->customer_email)
                    ->send(new \App\Mail\OrderShipped($this->order)),
                'delivered' => \Illuminate\Support\Facades\Mail::to($this->order->customer_email)
                    ->send(new \App\Mail\OrderDelivered($this->order)),
                'cancelled' => \Illuminate\Support\Facades\Mail::to($this->order->customer_email)
                    ->send(new \App\Mail\OrderCancelled($this->order)),
                default => null,
            };
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send order status email', [
                'order' => $this->order->number,
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ $order->number }}</flux:heading>
                <flux:subheading>{{ $order->created_at->format('M d, Y · H:i') }}</flux:subheading>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('account.orders.invoice', $order)" target="_blank" variant="ghost" icon="document-arrow-down">
                    {{ __('Invoice') }}
                </flux:button>
                <flux:button :href="route('admin.orders.index')" variant="ghost" icon="arrow-left" wire:navigate>
                    {{ __('Back to orders') }}
                </flux:button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <flux:card>
                    <flux:heading size="lg">{{ __('Items') }}</flux:heading>
                    <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ $item->product_icon }}</span>
                                    <div>
                                        <div class="font-medium">{{ $item->product_name }}</div>
                                        <flux:text size="sm" class="text-zinc-500">Rp {{ number_format((float) $item->price, 0, ',', '.') }} × {{ $item->quantity }}</flux:text>
                                    </div>
                                </div>
                                <div class="font-semibold">Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                    <flux:separator class="my-4" />
                    <div class="flex justify-between text-base font-semibold">
                        <span>{{ __('Total') }}</span>
                        <span>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">{{ __('Shipping') }}</flux:heading>
                    <flux:text class="mt-2 whitespace-pre-line">{{ $order->shipping_address }}</flux:text>

                    @if ($order->shipping_courier || $order->shipping_city_name)
                        <flux:separator class="my-3" />
                        <dl class="grid grid-cols-[140px_1fr] gap-y-1 text-sm">
                            @if ($order->shipping_city_name)
                                <dt class="text-zinc-500">{{ __('City') }}</dt>
                                <dd>{{ $order->shipping_city_name }}{{ $order->shipping_province ? ', '.$order->shipping_province : '' }}</dd>
                            @endif
                            @if ($order->shipping_courier)
                                <dt class="text-zinc-500">{{ __('Courier') }}</dt>
                                <dd>{{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}</dd>
                            @endif
                            @if ($order->shipping_etd)
                                <dt class="text-zinc-500">{{ __('Estimate') }}</dt>
                                <dd>{{ $order->shipping_etd }} {{ __('days') }}</dd>
                            @endif
                            @if ($order->shipping_weight)
                                <dt class="text-zinc-500">{{ __('Weight') }}</dt>
                                <dd>{{ number_format($order->shipping_weight) }} g</dd>
                            @endif
                            <dt class="text-zinc-500">{{ __('Cost') }}</dt>
                            <dd>{{ idr($order->shipping_cost) }}</dd>
                        </dl>
                    @elseif ($order->shipping_region)
                        <flux:separator class="my-3" />
                        <flux:text class="text-zinc-500 text-sm">{{ __('Region:') }} {{ $order->shipping_region }} — {{ idr($order->shipping_cost) }}</flux:text>
                    @endif

                    @if ($order->notes)
                        <flux:heading size="sm" class="mt-4">{{ __('Notes') }}</flux:heading>
                        <flux:text class="text-zinc-500">{{ $order->notes }}</flux:text>
                    @endif
                </flux:card>
            </div>

            <div class="space-y-6">
                <flux:card>
                    <flux:heading size="lg">{{ __('Customer') }}</flux:heading>
                    <flux:text class="mt-2">{{ $order->customer_name }}</flux:text>
                    <flux:text class="text-zinc-500">{{ $order->customer_email }}</flux:text>
                    <flux:text class="text-zinc-500">{{ $order->customer_phone }}</flux:text>
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">{{ __('Status') }}</flux:heading>
                    <form wire:submit="updateStatus" class="mt-3 flex flex-col gap-3">
                        <flux:select wire:model="status">
                            @foreach (\App\Models\Order::STATUSES as $s)
                                <flux:select.option value="{{ $s }}">{{ ucfirst($s) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:button type="submit" variant="primary">{{ __('Update status') }}</flux:button>
                    </form>
                    @if ($order->shipped_at)
                        <flux:text size="sm" class="mt-3 text-zinc-500">{{ __('Shipped:') }} {{ $order->shipped_at->format('M d, Y · H:i') }}</flux:text>
                    @endif
                    @if ($order->delivered_at)
                        <flux:text size="sm" class="text-zinc-500">{{ __('Delivered:') }} {{ $order->delivered_at->format('M d, Y · H:i') }}</flux:text>
                    @endif
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">{{ __('Tracking') }}</flux:heading>
                    @if (! $order->shipping_courier)
                        <flux:text size="sm" class="mt-2 text-zinc-500">
                            {{ __('No courier set on this order.') }}
                        </flux:text>
                    @else
                        <form wire:submit="saveTrackingNumber" class="mt-3 flex flex-col gap-3">
                            <flux:input
                                wire:model="tracking_number"
                                :label="__('AWB / Resi number')"
                                placeholder="e.g. JNE0123456789"
                                description="{{ __(':courier · setting this auto-marks the order as shipped', ['courier' => strtoupper($order->shipping_courier)]) }}"
                            />
                            <div class="flex flex-wrap gap-2">
                                <flux:button type="submit" variant="primary" size="sm">{{ __('Save') }}</flux:button>
                                @if ($order->tracking_number)
                                    <flux:button type="button" variant="ghost" size="sm" icon="map-pin" wire:click="trackPackage">{{ __('Check status') }}</flux:button>
                                @endif
                            </div>
                        </form>

                        @if ($tracking_error)
                            <div class="mt-3 rounded-lg bg-rose-50 p-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">
                                {{ $tracking_error }}
                            </div>
                        @endif

                        @if (! empty($tracking_data))
                            <div class="mt-4 space-y-3">
                                @if (! empty($tracking_data['delivery_status']))
                                    @php $ds = $tracking_data['delivery_status']; @endphp
                                    <div class="rounded-lg bg-emerald-50 p-3 text-sm dark:bg-emerald-950/30">
                                        <div class="font-semibold">{{ $ds['status'] ?? '—' }}</div>
                                        @if (! empty($ds['pod_receiver']))
                                            <div class="text-xs text-zinc-500">{{ __('Received by:') }} {{ $ds['pod_receiver'] }}</div>
                                        @endif
                                        @if (! empty($ds['pod_date']))
                                            <div class="text-xs text-zinc-500">{{ $ds['pod_date'] }} {{ $ds['pod_time'] ?? '' }}</div>
                                        @endif
                                    </div>
                                @endif

                                @if (! empty($tracking_data['manifest']))
                                    <div class="border-l-2 border-zinc-200 pl-4 dark:border-zinc-700">
                                        @foreach (array_reverse($tracking_data['manifest']) as $event)
                                            <div class="relative pb-3">
                                                <div class="absolute left-[-1.4rem] top-1.5 h-2 w-2 rounded-full bg-emerald-500"></div>
                                                <div class="text-sm font-medium">{{ $event['manifest_description'] ?? $event['manifest_code'] ?? '—' }}</div>
                                                <div class="text-xs text-zinc-500">
                                                    {{ trim(($event['manifest_date'] ?? '').' '.($event['manifest_time'] ?? '')) }}
                                                    @if (! empty($event['city_name']))
                                                        · {{ $event['city_name'] }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">{{ __('Payment') }}</flux:heading>
                    <div class="mt-3 space-y-2">
                        @php
                            $payColor = match ($order->payment_status) {
                                'paid' => 'green',
                                'pending' => 'amber',
                                'unpaid' => 'zinc',
                                'failed', 'expired' => 'red',
                                'refunded' => 'purple',
                                default => 'zinc',
                            };
                        @endphp
                        <div class="flex items-center justify-between">
                            <flux:text class="text-zinc-500">{{ __('Status') }}</flux:text>
                            <flux:badge :color="$payColor" size="sm">{{ ucfirst($order->payment_status) }}</flux:badge>
                        </div>
                        @if ($order->payment_method)
                            <div class="flex items-center justify-between">
                                <flux:text class="text-zinc-500">{{ __('Method') }}</flux:text>
                                <flux:text>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</flux:text>
                            </div>
                        @endif
                        @if ($order->paid_at)
                            <div class="flex items-center justify-between">
                                <flux:text class="text-zinc-500">{{ __('Paid at') }}</flux:text>
                                <flux:text>{{ $order->paid_at->format('M d, Y · H:i') }}</flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</section>

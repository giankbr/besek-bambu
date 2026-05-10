<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New manual order')] class extends Component {
    public string $customer_name = '';

    public string $customer_email = '';

    public string $customer_phone = '';

    public string $shipping_address = '';

    public string $shipping_region = '';

    public string $notes = '';

    /** @var array<int,array{product_id:int|null,name:string,price:string,quantity:int}> */
    public array $items = [];

    public string $shipping_cost = '0';

    public string $discount = '0';

    public string $status = 'pending';

    public string $payment_status = 'unpaid';

    public string $payment_method = 'manual_transfer';

    public ?int $user_id = null;

    public string $userQuery = '';

    public function mount(): void
    {
        $this->items = [
            ['product_id' => null, 'name' => '', 'price' => '0', 'quantity' => 1],
        ];
    }

    #[Computed]
    public function userResults()
    {
        if (mb_strlen($this->userQuery) < 2) {
            return collect();
        }

        return User::query()
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->userQuery}%")
                    ->orWhere('email', 'like', "%{$this->userQuery}%");
            })
            ->limit(8)
            ->get();
    }

    #[Computed]
    public function productOptions()
    {
        return Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock', 'icon']);
    }

    public function attachUser(int $userId): void
    {
        $user = User::find($userId);
        if (! $user) {
            return;
        }
        $this->user_id = $user->id;
        $this->customer_name = $this->customer_name ?: $user->name;
        $this->customer_email = $this->customer_email ?: $user->email;
        $this->userQuery = $user->name;
    }

    public function detachUser(): void
    {
        $this->user_id = null;
        $this->userQuery = '';
    }

    public function addRow(): void
    {
        $this->items[] = ['product_id' => null, 'name' => '', 'price' => '0', 'quantity' => 1];
    }

    public function removeRow(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if (count($this->items) === 0) {
            $this->addRow();
        }
    }

    public function updatedItems($value, $key): void
    {
        // When the product picker changes, hydrate the line with the
        // catalog name and price so the admin does not have to re-type.
        if (preg_match('/^(\d+)\.product_id$/', $key, $m)) {
            $idx = (int) $m[1];
            $product = Product::find((int) $value);
            if ($product) {
                $this->items[$idx]['name'] = $product->name;
                $this->items[$idx]['price'] = (string) $product->price;
            }
        }
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->items)->reduce(function (float $carry, array $item) {
            return $carry + ((float) ($item['price'] ?? 0)) * (int) ($item['quantity'] ?? 0);
        }, 0.0);
    }

    #[Computed]
    public function total(): float
    {
        return max(0.0, $this->subtotal - (float) $this->discount + (float) $this->shipping_cost);
    }

    public function save(): void
    {
        try {
            $this->validate([
                'customer_name' => ['required', 'string', 'max:255'],
                'customer_email' => ['required', 'email', 'max:255'],
                'customer_phone' => ['nullable', 'string', 'max:50'],
                'shipping_address' => ['required', 'string', 'max:1000'],
                'shipping_region' => ['nullable', 'string', 'max:255'],
                'shipping_cost' => ['required', 'numeric', 'min:0'],
                'discount' => ['required', 'numeric', 'min:0'],
                'notes' => ['nullable', 'string', 'max:2000'],
                'status' => ['required', Rule::in(Order::STATUSES)],
                'payment_status' => ['required', Rule::in(Order::PAYMENT_STATUSES)],
                'payment_method' => ['nullable', 'string', 'max:64'],
                'user_id' => ['nullable', 'exists:users,id'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.name' => ['required', 'string', 'max:255'],
                'items.*.price' => ['required', 'numeric', 'min:0'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
                'items.*.product_id' => ['nullable', 'exists:products,id'],
            ]);

            $order = DB::transaction(function () {
                $order = Order::create([
                    'number' => $this->generateNumber(),
                    'user_id' => $this->user_id,
                    'customer_name' => $this->customer_name,
                    'customer_email' => $this->customer_email,
                    'customer_phone' => $this->customer_phone,
                    'shipping_address' => $this->shipping_address,
                    'shipping_region' => $this->shipping_region,
                    'shipping_cost' => (float) $this->shipping_cost,
                    'notes' => $this->notes,
                    'subtotal' => $this->subtotal,
                    'discount' => (float) $this->discount,
                    'tax' => 0,
                    'tax_rate' => 0,
                    'tax_inclusive' => false,
                    'total' => $this->total,
                    'status' => $this->status,
                    'payment_method' => $this->payment_method ?: null,
                    'payment_status' => $this->payment_status,
                    'paid_at' => $this->payment_status === 'paid' ? now() : null,
                ]);

                foreach ($this->items as $item) {
                    $product = isset($item['product_id']) ? Product::find($item['product_id']) : null;
                    $price = (float) $item['price'];
                    $qty = (int) $item['quantity'];

                    $order->items()->create([
                        'product_id' => $product?->id,
                        'product_name' => $item['name'],
                        'product_icon' => $product?->icon ?? '🧺',
                        'price' => $price,
                        'quantity' => $qty,
                        'line_total' => $price * $qty,
                    ]);

                    if ($product && $product->stock >= $qty && in_array($this->status, ['paid', 'shipped', 'delivered'], true)) {
                        $product->decrement('stock', $qty);
                    }
                }

                return $order;
            });

            Flux::toast(variant: 'success', heading: __('Order created'), text: $order->number);

            $this->redirectRoute('admin.orders.show', ['order' => $order]);
        } catch (ValidationException $e) {
            Flux::toast(variant: 'danger', heading: __('Failed'), text: collect($e->validator->errors()->all())->first() ?? __('Check the form.'));
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(variant: 'danger', heading: __('Failed'), text: $e->getMessage());
        }
    }

    private function generateNumber(): string
    {
        do {
            $candidate = 'BSK-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
        } while (Order::where('number', $candidate)->exists());

        return $candidate;
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ __('New manual order') }}</flux:heading>
                <flux:subheading>{{ __('Create an order placed by phone, WhatsApp, or in person.') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.orders.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Back to orders') }}
            </flux:button>
        </div>

        <form wire:submit="save" class="grid gap-6">
            <flux:card>
                <flux:heading size="lg">{{ __('Customer') }}</flux:heading>

                <div class="mt-4 grid gap-3">
                    <div>
                        <flux:label>{{ __('Existing user (optional)') }}</flux:label>
                        @if ($user_id)
                            <div class="mt-1 flex items-center gap-2">
                                <flux:badge color="green">{{ __('Linked to user #:id', ['id' => $user_id]) }}</flux:badge>
                                <flux:button size="xs" variant="ghost" wire:click="detachUser">{{ __('Unlink') }}</flux:button>
                            </div>
                        @else
                            <flux:input
                                wire:model.live.debounce.300ms="userQuery"
                                placeholder="{{ __('Search by name or email…') }}"
                                icon="magnifying-glass"
                                class="mt-1"
                            />
                            @if (mb_strlen($userQuery) >= 2 && $this->userResults->isNotEmpty())
                                <ul class="mt-1 max-h-48 overflow-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    @foreach ($this->userResults as $u)
                                        <li>
                                            <button type="button" wire:click="attachUser({{ $u->id }})" class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">
                                                <span>{{ $u->name }}</span>
                                                <span class="text-xs text-zinc-500">{{ $u->email }}</span>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="customer_name" :label="__('Name')" required />
                        <flux:input wire:model="customer_email" :label="__('Email')" type="email" required />
                    </div>
                    <div class="grid gap-3 md:grid-cols-2">
                        <flux:input wire:model="customer_phone" :label="__('Phone')" />
                        <flux:input wire:model="shipping_region" :label="__('Region / city')" />
                    </div>
                    <flux:textarea wire:model="shipping_address" :label="__('Shipping address')" rows="3" required />
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Line items') }}</flux:heading>
                    <flux:button size="sm" variant="ghost" icon="plus" wire:click="addRow">{{ __('Add row') }}</flux:button>
                </div>

                <div class="mt-4 grid gap-3">
                    @foreach ($items as $i => $item)
                        <div class="grid gap-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700 md:grid-cols-12">
                            <div class="md:col-span-4">
                                <flux:label class="text-xs">{{ __('Product (catalog)') }}</flux:label>
                                <flux:select wire:model.live="items.{{ $i }}.product_id" placeholder="{{ __('— Custom item —') }}">
                                    @foreach ($this->productOptions as $p)
                                        <flux:select.option value="{{ $p->id }}">
                                            {{ $p->icon }} {{ $p->name }} — {{ idr($p->price) }} ({{ $p->stock }} stk)
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <div class="md:col-span-4">
                                <flux:input wire:model="items.{{ $i }}.name" :label="__('Item name')" />
                            </div>
                            <div class="md:col-span-2">
                                <flux:input wire:model.live="items.{{ $i }}.price" :label="__('Price')" type="number" min="0" step="1" />
                            </div>
                            <div class="md:col-span-1">
                                <flux:input wire:model.live="items.{{ $i }}.quantity" :label="__('Qty')" type="number" min="1" />
                            </div>
                            <div class="flex items-end justify-end md:col-span-1">
                                <flux:button size="xs" variant="ghost" icon="trash" wire:click="removeRow({{ $i }})" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg">{{ __('Totals & status') }}</flux:heading>

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <flux:input wire:model.live="discount" :label="__('Discount (Rp)')" type="number" min="0" step="1" />
                    <flux:input wire:model.live="shipping_cost" :label="__('Shipping cost (Rp)')" type="number" min="0" step="1" />

                    <div class="flex flex-col justify-end rounded-lg bg-emerald-50 p-3 dark:bg-emerald-950/30">
                        <div class="text-xs text-zinc-500">{{ __('Subtotal') }} {{ idr($this->subtotal) }}</div>
                        <div class="mt-1 text-xs text-zinc-500">{{ __('Total') }}</div>
                        <div class="text-2xl font-semibold">{{ idr($this->total) }}</div>
                    </div>
                </div>

                <flux:textarea wire:model="notes" :label="__('Internal notes')" rows="3" class="mt-4" />

                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <flux:select wire:model="status" :label="__('Order status')">
                        @foreach (\App\Models\Order::STATUSES as $s)
                            <flux:select.option value="{{ $s }}">{{ ucfirst($s) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="payment_status" :label="__('Payment status')">
                        @foreach (\App\Models\Order::PAYMENT_STATUSES as $s)
                            <flux:select.option value="{{ $s }}">{{ ucfirst($s) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model="payment_method" :label="__('Payment method')" placeholder="cash, bank, qris…" />
                </div>
            </flux:card>

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Create order') }}</flux:button>
                <flux:button :href="route('admin.orders.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>

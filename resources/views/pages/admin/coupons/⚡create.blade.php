<?php

use App\Models\Coupon;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('New coupon')] class extends Component {
    public string $code = '';
    public ?string $label = null;
    public string $type = 'fixed';
    public string $value = '0';
    public string $min_order = '0';
    public ?int $usage_limit = null;
    public ?string $expires_at = null;
    public bool $is_active = true;

    public function save(): void
    {
        $this->code = strtoupper(trim($this->code));

        $validated = $this->validate([
            'code' => ['required', 'string', 'max:64', Rule::unique('coupons', 'code')],
            'label' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(Coupon::TYPES)],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'is_active' => ['boolean'],
        ]);

        Coupon::create($validated);

        Flux::toast(variant: 'success', text: __('Coupon created.'));
        $this->redirectRoute('admin.coupons.index', navigate: true);
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('New coupon') }}</flux:heading>
            <flux:subheading>{{ __('Configure a new promo code.') }}</flux:subheading>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="code" :label="__('Code')" required placeholder="WELCOME20" />
                <flux:input wire:model="label" :label="__('Label (optional)')" />
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                <flux:select wire:model="type" :label="__('Type')">
                    <flux:select.option value="fixed">{{ __('Fixed amount (Rp)') }}</flux:select.option>
                    <flux:select.option value="percent">{{ __('Percent (%)') }}</flux:select.option>
                </flux:select>
                <flux:input wire:model="value" :label="__('Value')" type="number" step="0.01" min="0" required />
                <flux:input wire:model="min_order" :label="__('Min order (Rp)')" type="number" step="1" min="0" />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="usage_limit" :label="__('Usage limit (optional)')" type="number" min="1" />
                <flux:input wire:model="expires_at" :label="__('Expires at (optional)')" type="datetime-local" />
            </div>

            <flux:checkbox wire:model="is_active" :label="__('Active')" />

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
                <flux:button :href="route('admin.coupons.index')" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
            </div>
        </form>
    </div>
</section>

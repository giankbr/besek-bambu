<?php

use App\Models\Coupon;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit coupon')] class extends Component {
    public Coupon $coupon;

    public string $code = '';
    public ?string $label = null;
    public string $type = 'fixed';
    public string $value = '0';
    public string $min_order = '0';
    public ?int $usage_limit = null;
    public ?string $expires_at = null;
    public bool $is_active = true;

    public function mount(Coupon $coupon): void
    {
        $this->coupon = $coupon;
        $this->code = $coupon->code;
        $this->label = $coupon->label;
        $this->type = $coupon->type;
        $this->value = (string) $coupon->value;
        $this->min_order = (string) $coupon->min_order;
        $this->usage_limit = $coupon->usage_limit;
        $this->expires_at = $coupon->expires_at?->format('Y-m-d\TH:i');
        $this->is_active = $coupon->is_active;
    }

    public function save(): void
    {
        $this->code = strtoupper(trim($this->code));

        try {
            $validated = $this->validate([
                'code' => ['required', 'string', 'max:64', Rule::unique('coupons', 'code')->ignore($this->coupon->id)],
                'label' => ['nullable', 'string', 'max:255'],
                'type' => ['required', Rule::in(Coupon::TYPES)],
                'value' => ['required', 'numeric', 'min:0'],
                'min_order' => ['nullable', 'numeric', 'min:0'],
                'usage_limit' => ['nullable', 'integer', 'min:1'],
                'expires_at' => ['nullable', 'date'],
                'is_active' => ['boolean'],
            ]);

            $this->coupon->update($validated);

            Flux::toast(variant: 'success', text: __('Coupon updated.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: collect($e->validator->errors()->all())->first() ?? __('Please check the form for errors.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to save'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ $coupon->code }}</flux:heading>
                <flux:subheading>{{ __('Used') }} {{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }} {{ __('times') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.coupons.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>

        <form wire:submit="save" class="grid w-full gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <flux:input wire:model="code" :label="__('Code')" required />
                <flux:input wire:model="label" :label="__('Label')" />
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
                <flux:input wire:model="usage_limit" :label="__('Usage limit')" type="number" min="1" />
                <flux:input wire:model="expires_at" :label="__('Expires at')" type="datetime-local" />
            </div>

            <flux:checkbox wire:model="is_active" :label="__('Active')" />

            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
            </div>
        </form>
    </div>
</section>

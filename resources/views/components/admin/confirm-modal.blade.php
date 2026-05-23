@props([
    'name',
    'title',
    'description' => null,
    'confirm' => __('Delete'),
    'variant' => 'danger',
    'action',
])

<flux:modal :name="$name" class="md:w-96">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ $title }}</flux:heading>
            @if ($description)
                <flux:subheading>{{ $description }}</flux:subheading>
            @endif
        </div>
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button :variant="$variant" wire:click="{{ $action }}" wire:loading.attr="disabled">
                {{ $confirm }}
            </flux:button>
        </div>
    </div>
</flux:modal>

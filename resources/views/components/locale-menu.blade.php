@php
    $activeLocale = app()->getLocale();
    $locales = [
        'id' => 'Indonesia',
        'en' => 'English',
    ];
@endphp

<flux:menu.separator />

<div class="px-2 py-2">
    <flux:select
        :label="__('nav.language')"
        size="sm"
        class="w-full"
        onchange="window.location.href = this.value"
    >
        @foreach ($locales as $code => $label)
            @if ($activeLocale === $code)
                <flux:select.option value="{{ route('locale.switch', $code) }}" selected>
                    {{ $label }}
                </flux:select.option>
            @else
                <flux:select.option value="{{ route('locale.switch', $code) }}">
                    {{ $label }}
                </flux:select.option>
            @endif
        @endforeach
    </flux:select>
</div>

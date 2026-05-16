@php
    $brandName = store_name();
    $brandUrl = config('app.url');
    $logoUrl = store_logo_url();
@endphp
<x-mail::layout>
<x-slot:header>
<x-mail::header :url="$brandUrl">
@if ($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ $brandName }}" width="140" height="32">
@else
{{ $brandName }}
@endif
</x-mail::header>
</x-slot:header>

{!! $slot !!}

@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ $brandName }}@if ($email = store_email()) · [{{ $email }}](mailto:{{ $email }})@endif

{{ __('Email ini dikirim otomatis, mohon jangan balas langsung ke pesan ini kecuali diminta.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>

<x-mail::message>
@foreach (preg_split("/\r\n|\r|\n/", trim($body)) as $line)
@if (trim($line) !== '')
{{ trim($line) }}

@endif
@endforeach

<x-mail::button :url="$shopUrl">
{{ __('Belanja sekarang') }}
</x-mail::button>

{{ __('Salam,') }}<br>
{{ store_name() }}
</x-mail::message>

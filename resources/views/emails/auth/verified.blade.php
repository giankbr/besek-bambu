<x-mail::message>
# {{ __('Halo, :name!', ['name' => $user->name]) }}

{{ __('Email Anda (:email) sudah berhasil diverifikasi. Akun Besek Bambu Anda sekarang aktif.', ['email' => $user->email]) }}

{{ __('Anda bisa mulai berbelanja, melacak pesanan, dan mengelola profil kapan saja.') }}

<x-mail::button :url="route('account.index')">
{{ __('Buka akun saya') }}
</x-mail::button>

{{ __('Terima kasih telah bergabung.') }}

{{ store_name() }}
</x-mail::message>

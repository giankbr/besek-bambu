<a
  href="{{ route('home') }}"
  {{ $attributes->class(['auth-brand']) }}
  aria-label="{{ store_name() }}"
>
  <span class="auth-brand__text">{{ store_name() }}</span>
</a>

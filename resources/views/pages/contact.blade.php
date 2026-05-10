@extends('layouts.storefront')

@section('title', 'Contact — '.store_name())

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <span class="current">Contact</span>
      </nav>

      <div class="eyebrow">Say hello</div>
      <h1 class="section-title cart-title">Get in <em>touch</em></h1>

      <div class="contact-grid">
        <div>
          <p class="confirmation__lead" style="margin-bottom:1.5rem">Have a question about a product, want to order in bulk, or just want to say hi? We'd love to hear from you.</p>

          @if (session('status'))
            <div class="confirmation-card" style="background:#eef7ee">
              <p class="confirmation-meta" style="margin:0">{{ session('status') }}</p>
            </div>
          @endif

          <form method="post" action="{{ route('contact.submit') }}" class="contact-form">
            @csrf
            <div class="checkout-row">
              <label>
                Name
                <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required />
                @error('name')<span class="form-error">{{ $message }}</span>@enderror
              </label>
              <label>
                Email
                <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required />
                @error('email')<span class="form-error">{{ $message }}</span>@enderror
              </label>
            </div>
            <label>
              Subject
              <input type="text" name="subject" value="{{ old('subject') }}" required />
              @error('subject')<span class="form-error">{{ $message }}</span>@enderror
            </label>
            <label>
              Message
              <textarea name="message" rows="6" required minlength="10" maxlength="5000">{{ old('message') }}</textarea>
              @error('message')<span class="form-error">{{ $message }}</span>@enderror
            </label>
            <button type="submit" class="hero-cta">Send message</button>
          </form>
        </div>

        @php
          $contactEmail = store_email();
          $contactPhone = store_phone();
          $contactAddress = store_address();
        @endphp
        <aside class="contact-side">
          @if ($contactAddress)
            <div class="confirmation-card">
              <h3 class="confirmation-section-title" style="margin-top:0">Workshop</h3>
              @foreach (preg_split('/\r\n|\r|\n/', $contactAddress) as $line)
                @if (trim($line) !== '')
                  <p class="confirmation-meta">{{ $line }}</p>
                @endif
              @endforeach
            </div>
          @else
            <div class="confirmation-card">
              <h3 class="confirmation-section-title" style="margin-top:0">Workshop</h3>
              <p class="confirmation-meta">Jl. Kasongan, Bantul</p>
              <p class="confirmation-meta">Yogyakarta, Indonesia 55184</p>
            </div>
          @endif

          <div class="confirmation-card">
            <h3 class="confirmation-section-title" style="margin-top:0">Hours</h3>
            <p class="confirmation-meta">Mon–Sat · 09:00–17:00</p>
            <p class="confirmation-meta">Sunday · Closed</p>
          </div>

          @if ($contactEmail)
            <div class="confirmation-card">
              <h3 class="confirmation-section-title" style="margin-top:0">Email</h3>
              <p class="confirmation-meta"><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></p>
            </div>
          @endif

          @if ($contactPhone)
            <div class="confirmation-card">
              <h3 class="confirmation-section-title" style="margin-top:0">Phone</h3>
              <p class="confirmation-meta">{{ $contactPhone }}</p>
            </div>
          @endif
        </aside>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection

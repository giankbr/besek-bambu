@extends('layouts.storefront')

@section('title', 'About — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <x-page-head
        :crumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Tentang kami'],
        ]"
        eyebrow="Cerita kami"
      >
        <h1 class="section-title page-head__title cart-title">Dibuat dengan <em>tradisi</em></h1>
      </x-page-head>

      <div class="about-grid">
        <div>
          <p class="about-lead">Besek Bambu is a craft studio rooted in Indonesia, weaving everyday objects from sustainably harvested bamboo. Each piece is handmade by artisans whose families have practiced this craft for generations.</p>

          <h2 class="confirmation-section-title" style="margin-top:2rem">Our craft</h2>
          <p class="about-body">Bamboo is harvested only after it has matured for at least three years. We work with cooperative growers who replant after every harvest. The strips are split, dried, and woven by hand — no machines, no chemicals, no shortcuts.</p>

          <h2 class="confirmation-section-title" style="margin-top:1.5rem">Why bamboo</h2>
          <p class="about-body">Bamboo regrows in months, not decades. It needs no fertilizer, almost no water, and absorbs more CO₂ than most hardwoods. When a besek finally returns to the earth, it does so without leaving a trace.</p>

          <h2 class="confirmation-section-title" style="margin-top:1.5rem">Our promise</h2>
          <ul class="about-list">
            <li>100% natural, biodegradable materials</li>
            <li>Fair wages to every artisan we work with</li>
            <li>Carbon-neutral shipping within Indonesia</li>
            <li>Lifetime repair on every piece we sell</li>
          </ul>
        </div>

        <aside class="about-side">
          <div class="confirmation-card">
            <h3 class="confirmation-section-title" style="margin-top:0">Visit our workshop</h3>
            <p class="confirmation-meta">Yogyakarta, Indonesia</p>
            <p class="confirmation-meta">Open Mon–Sat · 09:00–17:00</p>
            <a class="cart-link-btn" href="{{ route('contact') }}">Get in touch →</a>
          </div>
        </aside>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection

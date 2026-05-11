@extends('layouts.storefront')

@section('title', 'FAQ — Besek Bambu')
@section('meta_description', 'Pertanyaan umum tentang besek bambu: cara perawatan, metode pembayaran, estimasi pengiriman, dan kebijakan pemesanan.')

@push('head')
  @php
    $faqSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'FAQPage',
      'mainEntity' => [
        [
          '@type' => 'Question',
          'name' => 'How are your products made?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Every piece is hand-woven by artisans in Yogyakarta from naturally harvested bamboo. Production takes between 2–7 days per item, depending on size and complexity.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'How do I care for my besek?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Wipe clean with a damp cloth and dry in the shade. Avoid prolonged exposure to water or direct sunlight. With proper care, a besek can last for many years.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'What payment methods do you accept?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'We accept major credit cards, bank transfers, e-wallets, and QRIS via Midtrans.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'How long does shipping take?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Within Java, shipping usually takes 2–4 business days, while other islands generally take 4–7 business days.',
          ],
        ],
      ],
    ];
  @endphp
  <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <x-page-head
        :crumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'FAQ'],
        ]"
        eyebrow="Pusat bantuan"
      >
        <h1 class="section-title page-head__title cart-title">Pertanyaan yang <em>sering diajukan</em></h1>
      </x-page-head>

      <div class="faq-list">
        <details class="faq-item">
          <summary>How are your products made?</summary>
          <p>Every piece is hand-woven by artisans in Yogyakarta from naturally harvested bamboo. Production takes between 2–7 days per item, depending on size and complexity.</p>
        </details>

        <details class="faq-item">
          <summary>How do I care for my besek?</summary>
          <p>Wipe clean with a damp cloth and dry in the shade. Avoid prolonged exposure to water or direct sunlight. With proper care, a besek can last for many years.</p>
        </details>

        <details class="faq-item">
          <summary>What payment methods do you accept?</summary>
          <p>We accept all major credit cards, bank transfers (BCA, BNI, Mandiri, Permata), e-wallets (GoPay, OVO, ShopeePay), and QRIS — securely processed via Midtrans.</p>
        </details>

        <details class="faq-item">
          <summary>How long does shipping take?</summary>
          <p>Within Java, 2–4 business days. Other islands, 4–7 business days. International shipping available on request.</p>
        </details>

        <details class="faq-item">
          <summary>Can I return a product?</summary>
          <p>Yes — we accept returns within 14 days of delivery for unused items in their original condition. Custom orders are non-refundable.</p>
        </details>

        <details class="faq-item">
          <summary>Do you offer wholesale pricing?</summary>
          <p>We do! Contact us for orders of 25 pieces or more — we'd love to work with restaurants, retailers, and event planners.</p>
        </details>

        <details class="faq-item">
          <summary>Are your products safe for food?</summary>
          <p>Yes. We use no varnishes, dyes, or finishing agents. The bamboo is washed, dried, and woven — nothing else.</p>
        </details>
      </div>
    </section>

    <x-site-footer />
  </main>
@endsection

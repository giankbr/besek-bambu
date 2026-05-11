@extends('layouts.storefront')

@section('title', 'Gallery — Besek Bambu')
@section('meta_description', 'Lihat inspirasi penggunaan besek bambu handmade untuk hampers, dekorasi acara, dan kemasan produk brand lokal.')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <x-page-head
        :crumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Galeri'],
        ]"
        eyebrow="Inspirasi"
      >
        <h1 class="section-title page-head__title cart-title">Galeri <em>kami</em></h1>
      </x-page-head>
      <p class="confirmation__lead" style="max-width:640px;margin-bottom:32px">Scenes, drops, and moments featuring our handcrafted bamboo pieces.</p>

      @if ($items->isEmpty())
        <p class="shop-empty">No gallery items yet.</p>
      @else
        <div class="gallery-grid">
          @foreach ($items as $item)
            <div class="gallery-grid__card {{ $item->color_class }} {{ $item->drop ? 'drop' : '' }}" style="background-image: url('{{ image_src($item->image_url) }}');">
              <div class="gallery-grid__caption">
                <em>{{ $item->title }}</em>
                @if ($item->subtitle)<span>{{ $item->subtitle }}</span>@endif
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </section>

    <x-site-footer />
  </main>
@endsection

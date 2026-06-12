@extends('layouts.storefront')

@section('title', meta_title(__('Galeri Inspirasi Besek Bambu — Hantaran & Hampers'), store_name()))
@section('meta_description', __('Inspirasi penggunaan besek bambu handmade: hampers, seserahan pernikahan, dekorasi acara, dan kemasan produk brand lokal ramah lingkungan.'))

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container gallery-page">
      <x-page-head
        :crumbs="[
            ['label' => __('Beranda'), 'url' => route('home')],
            ['label' => __('Galeri')],
        ]"
        eyebrow="{{ __('Inspirasi') }}"
      >
        <h1 class="section-title shop-title">{!! __('Galeri <em>kami</em>') !!}</h1>
      </x-page-head>
      <p class="confirmation__lead" style="max-width:640px;margin-bottom:32px">{{ __('Suasana, drop, dan momen yang menampilkan produk bambu buatan tangan kami.') }}</p>

      @if ($items->isEmpty())
        <p class="shop-empty sf-empty">{{ __('Belum ada item galeri.') }}</p>
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

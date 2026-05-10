@extends('layouts.storefront')

@section('title', 'Gallery — Besek Bambu')

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <span class="current">Gallery</span>
      </nav>

      <div class="eyebrow">Inspiration</div>
      <h1 class="section-title cart-title">Our <em>gallery</em></h1>
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

@props(['galleryItems'])

<section class="gallery" data-gallery-slider>
  <div class="section-head gallery-head">
    <div>
      <div class="eyebrow">{{ __('Inspirasi & ide') }}</div>
      <h2 class="section-title gallery-title">{!! __('Penggunaan besek <em>Galeri</em>') !!}</h2>
    </div>
    <div class="gallery-head__tools">
      <a href="{{ route('gallery') }}" class="view-more">{{ __('Lihat galeri') }} →</a>
      <div class="gallery-nav" role="group" aria-label="{{ __('Geser kartu galeri') }}">
        <button type="button" class="gallery-nav__btn" data-gallery-prev aria-label="{{ __('Geser ke kiri') }}">←</button>
        <button type="button" class="gallery-nav__btn" data-gallery-next aria-label="{{ __('Geser ke kanan') }}">→</button>
      </div>
    </div>
  </div>

  <div class="gallery-track" data-gallery-track>
    @foreach ($galleryItems as $item)
      <a
        class="gallery-card {{ $item->color_class }} {{ $item->drop ? 'drop' : '' }}"
        href="{{ route('gallery') }}"
        style="background-image: url('{{ image_src($item->image_url) }}');"
      >
        <span class="gallery-card-name">
          <em>{{ $item->title }}</em>@if ($item->subtitle)<br/>{{ $item->subtitle }}@endif
        </span>
      </a>
    @endforeach
  </div>
</section>

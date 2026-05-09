@props(['galleryItems'])

<section class="gallery container">
  <div class="gallery-head">
    <div class="gallery-title">
      Thoughtful, Planet-Prioritizing Ideas<br/>and Inspiration ✧ <em>Gallery</em>
    </div>
    <div class="gallery-nav">
      <button aria-label="Previous">←</button>
      <button aria-label="Next">→</button>
    </div>
  </div>

  <div class="gallery-track">
    @foreach ($galleryItems as $item)
      <a class="gallery-card {{ $item->color_class }} {{ $item->drop ? 'drop' : '' }}" href="#" style="background-image: url('{{ $item->image_url }}');">
        <span class="gallery-card-name">
          <em>{{ $item->title }}</em>@if ($item->subtitle)<br/>{{ $item->subtitle }}@endif
        </span>
      </a>
    @endforeach
  </div>
</section>

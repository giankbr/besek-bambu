@props(['categories'])

<section class="section">
  <div class="section-head">
    <div>
      <div class="eyebrow">Jelajahi koleksi besek kami</div>
      <div class="section-title"><em>Kategori</em></div>
    </div>
  </div>

  <div class="cat-grid">
    @foreach ($categories as $i => $category)
      <a class="cat cat-{{ $i + 1 }}" href="{{ route('shop.category', $category) }}" style="background-image: linear-gradient(rgba(0,0,0,.1), rgba(0,0,0,.4)), url('{{ image_src($category->image_url) }}');">
        <div class="cat-content">
          <h3>{{ $category->title }}</h3>
          <span class="tag">Belanja</span>
        </div>
      </a>
    @endforeach
  </div>
</section>

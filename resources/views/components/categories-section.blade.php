@props(['categories'])

<section class="section container">
  <div class="section-head">
    <div>
      <div class="eyebrow">Explore our thoughtful and planet-first</div>
      <div class="section-title">✦ <em>Categories</em></div>
    </div>
  </div>

  <div class="cat-grid">
    @foreach ($categories as $i => $category)
      <a class="cat cat-{{ $i + 1 }}" href="#" style="background-image: linear-gradient(rgba(0,0,0,.1), rgba(0,0,0,.4)), url('{{ $category->image_url }}');">
        <div class="cat-content">
          <h3>{{ $category->title }}</h3>
          <span class="tag">Shop</span>
        </div>
      </a>
    @endforeach
  </div>
</section>

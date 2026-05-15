<section class="newsletter-section" aria-labelledby="newsletter-title">
  <div class="newsletter">
    <figure class="news-corner news-corner--tl" aria-hidden="true">
      <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=400&q=80" alt="" width="200" height="200" loading="lazy" decoding="async" />
    </figure>
    <figure class="news-corner news-corner--bl" aria-hidden="true">
      <img src="https://images.unsplash.com/photo-1586075010923-2dd45795fb39?auto=format&fit=crop&w=400&q=80" alt="" width="200" height="200" loading="lazy" decoding="async" />
    </figure>
    <figure class="news-corner news-corner--tr" aria-hidden="true">
      <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=400&q=80" alt="" width="200" height="200" loading="lazy" decoding="async" />
    </figure>
    <figure class="news-corner news-corner--br" aria-hidden="true">
      <img src="https://images.unsplash.com/photo-1543353071-10c8ba85a904?auto=format&fit=crop&w=400&q=80" alt="" width="200" height="200" loading="lazy" decoding="async" />
    </figure>

    <div class="news-center">
      <p class="label">Info &amp; promo</p>
      <h2 id="newsletter-title" class="big">
        <span class="big-line">Daftar email</span>
        <span class="big-accent">diskon 10%</span>
      </h2>
      <form class="newsletter-form" action="#" method="post" onsubmit="event.preventDefault();">
        @csrf
        <label class="newsletter-field">
          <span class="visually-hidden">Email</span>
          <input type="email" name="email" placeholder="Email Anda" required autocomplete="email" inputmode="email" />
        </label>
        <button type="submit">Daftar</button>
      </form>
      <p class="sub">Tips packing hantaran, ide isian besek, dan kode diskon untuk pembelian besek anyaman bambu berikutnya.</p>
    </div>
  </div>
</section>

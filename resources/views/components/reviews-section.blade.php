@props(['reviews'])

<section class="reviews-section" aria-labelledby="reviews-heading">
  <div class="reviews">
    <header class="reviews-top">
      <div class="reviews-intro">
        <h2 id="reviews-heading" class="reviews-kicker">{{ __('Ulasan pelanggan') }}</h2>
        <div class="reviews-score-wrap">
          <p class="reviews-score">4.9<sup>/5</sup></p>
          <p class="reviews-stars" aria-hidden="true">★★★★★</p>
        </div>
      </div>
      <p class="reviews-lead">
        {!! __('Lebih dari :count ulasan :stars untuk besek anyaman bambu kami — terpercaya untuk hantaran & kurban.', ['count' => '<strong>'.number_format(25000).'</strong>', 'stars' => '<strong>'.__('bintang 5').'</strong>']) !!}
      </p>
    </header>

    <div class="reviews-track-wrap" data-reviews-slider>
      <div class="reviews-track" role="list">
        @foreach ($reviews as $review)
          @php
            [$first, $last] = array_pad(explode(' ', $review->author_name, 2), 2, '');
          @endphp
          <article class="review {{ $review->is_featured ? 'featured' : '' }}" role="listitem">
            <div class="review-mark" aria-hidden="true">&ldquo;</div>
            <p>{{ __($review->quote) }}</p>
            <div class="review-author">
              <div class="name">{{ $first }} <em>{{ $last }}</em></div>
              <div class="role">{{ __($review->author_role) }}</div>
            </div>
          </article>
        @endforeach
      </div>
    </div>
  </div>
</section>

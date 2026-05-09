@props(['reviews'])

<section class="container">
  <div class="reviews">
    <div class="reviews-head">
      <div class="reviews-score">4.9<sup>/5</sup></div>
      <div class="reviews-lead">More than <strong>{{ number_format(25000) }}</strong><br/><strong>5-Star</strong> Reviews for Our Award-<br/>Winning Eco Products</div>
    </div>

    <div class="reviews-track">
      @foreach ($reviews as $review)
        @php
          [$first, $last] = array_pad(explode(' ', $review->author_name, 2), 2, '');
        @endphp
        <article class="review {{ $review->is_featured ? 'featured' : '' }}">
          <div class="review-mark">&ldquo;</div>
          <p>{{ $review->quote }}</p>
          <div class="review-author">
            <div class="name">{{ $first }} <em>{{ $last }}</em></div>
            <div class="role">{{ $review->author_role }}</div>
          </div>
        </article>
      @endforeach
    </div>
  </div>
</section>

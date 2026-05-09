<section class="container">
  <div class="newsletter">
    <div class="news-img-stack">
      <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=600&q=80" alt="Pot on table" />
      <img src="https://images.unsplash.com/photo-1591261730799-ee4e6c2d1e1d?auto=format&fit=crop&w=600&q=80" alt="Yellow pan" />
    </div>

    <div class="news-center">
      <div class="label">Get Recipes</div>
      <div class="big">10% Off</div>
      <form class="newsletter-form" action="#" method="post" onsubmit="event.preventDefault();">
        @csrf
        <input type="email" name="email" placeholder="Your Email" required />
        <button type="submit">Subscribe</button>
      </form>
      <div class="sub">Eco-friendly recipes, cooking tips, and a 10% discount on sustainable kitchenware for a greener lifestyle.</div>
    </div>

    <div class="news-side">
      <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&w=600&q=80" alt="Stacked cups" />
      <img src="https://images.unsplash.com/photo-1543353071-10c8ba85a904?auto=format&fit=crop&w=600&q=80" alt="People sharing meal" />
    </div>
  </div>
</section>

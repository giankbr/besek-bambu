@props(['faqs'])

@foreach ($faqs as $faq)
  <details class="faq-item">
    <summary>{{ $faq['q'] }}</summary>
    <p>{{ $faq['a'] }}</p>
  </details>
@endforeach

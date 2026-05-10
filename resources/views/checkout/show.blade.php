@extends('layouts.storefront')

@section('title', 'Checkout — '.store_name())

@php
  $defaultRegion = array_key_first($regions);
  $useRajaOngkir = ($shippingProvider ?? 'flat') === 'rajaongkir' && ($rajaOngkirReady ?? false);
@endphp

@section('content')
  <x-navbar />
  <main id="main-content" class="page-main">
    <section class="container">
      <nav class="breadcrumbs">
        <a href="{{ route('home') }}">Home</a>
        <span>/</span>
        <a href="{{ route('cart.show') }}">Cart</a>
        <span>/</span>
        <span class="current">Checkout</span>
      </nav>

      <div class="eyebrow">Almost there</div>
      <h1 class="section-title cart-title"><em>Checkout</em></h1>

      @php
        $taxBase = max(0, $subtotal - $discount);
        $totalBeforeShipping = $taxInclusive ? $taxBase : $taxBase + $tax;
        $initialShippingCost = $useRajaOngkir ? 0 : $regions[$defaultRegion]['cost'];
      @endphp

      <form
        method="post"
        action="{{ route('checkout.store') }}"
        class="checkout-grid"
        x-data='checkoutForm(@js([
          "useRajaOngkir" => $useRajaOngkir,
          "regions" => $regions,
          "defaultRegion" => $defaultRegion,
          "totalBeforeShipping" => $totalBeforeShipping,
          "totalWeight" => $totalWeight,
          "csrf" => csrf_token(),
          "urls" => [
            "search" => route("shipping.destinations"),
            "cost" => route("shipping.cost"),
          ],
        ]))'
      >
        @csrf
        <input type="hidden" name="shipping_mode" :value="useRajaOngkir ? 'rajaongkir' : 'flat'" />

        <div class="checkout-form">
          <h2 class="checkout-section-title">Contact</h2>
          <div class="checkout-row">
            <label>
              Name
              <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required />
              @error('customer_name')<span class="form-error">{{ $message }}</span>@enderror
            </label>
            <label>
              Email
              <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}" required />
              @error('customer_email')<span class="form-error">{{ $message }}</span>@enderror
            </label>
          </div>
          <label>
            Phone
            <input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required />
            @error('customer_phone')<span class="form-error">{{ $message }}</span>@enderror
          </label>

          <h2 class="checkout-section-title">Shipping</h2>
          <label>
            Address
            <textarea name="shipping_address" rows="3" required placeholder="Street, building number, postal code...">{{ old('shipping_address') }}</textarea>
            @error('shipping_address')<span class="form-error">{{ $message }}</span>@enderror
          </label>

          @if ($useRajaOngkir)
            <div style="position:relative">
              <label>
                Destination (district / city)
                <input
                  type="text"
                  x-model="destQuery"
                  @input.debounce.350ms="searchDestinations()"
                  @focus="showDestResults = true"
                  @click.outside="showDestResults = false"
                  placeholder="Type at least 2 characters (e.g. 'sleman', 'kebayoran')"
                  autocomplete="off"
                  :readonly="!!selectedDest"
                  required
                />
              </label>
              <input type="hidden" name="shipping_city_id" :value="selectedDest ? selectedDest.id : ''" />
              <input type="hidden" name="shipping_city_name" :value="selectedDest ? selectedDest.label : ''" />

              <div
                x-show="showDestResults && (destLoading || destResults.length > 0 || destError)"
                style="position:absolute;left:0;right:0;z-index:20;margin-top:4px;max-height:18rem;overflow-y:auto;background:#fff;border:1px solid #e5e0d6;border-radius:0.5rem;box-shadow:0 8px 16px rgba(0,0,0,0.08)"
                x-cloak
              >
                <div x-show="destLoading" style="padding:0.5rem 0.75rem;font-size:0.875rem;color:#7d6f5f">Searching…</div>
                <div x-show="!destLoading && destError" style="padding:0.5rem 0.75rem;font-size:0.875rem;color:#b91c1c" x-text="destError"></div>
                <template x-for="r in destResults" :key="r.id">
                  <button
                    type="button"
                    @click="pickDest(r)"
                    style="display:block;width:100%;padding:0.5rem 0.75rem;text-align:left;font-size:0.875rem;border:0;background:transparent;cursor:pointer"
                    @mouseover="$el.style.background='#f7f3ec'"
                    @mouseleave="$el.style.background='transparent'"
                  >
                    <div style="font-weight:600" x-text="r.label"></div>
                    <div style="font-size:0.75rem;color:#7d6f5f" x-text="'ID: ' + r.id"></div>
                  </button>
                </template>
              </div>

              <div
                x-show="selectedDest"
                style="margin-top:0.5rem;padding:0.5rem 0.75rem;font-size:0.875rem;background:#f1f8f3;border:1px solid #c8e6cb;border-radius:0.375rem;display:flex;align-items:center;justify-content:space-between"
              >
                <span x-text="selectedDest && selectedDest.label"></span>
                <button type="button" @click="clearDest()" style="background:transparent;border:0;color:#1f7a3a;cursor:pointer;font-weight:600">Change</button>
              </div>

              @error('shipping_city_id')<span class="form-error">{{ $message }}</span>@enderror

              <div x-show="loadingServices" class="confirmation-meta" style="margin-top:0.75rem">
                Calculating shipping cost…
              </div>

              <div x-show="!loadingServices && services.length > 0" class="checkout-payment-methods" style="margin-top:0.75rem">
                <template x-for="s in services" :key="s.code + '-' + s.service">
                  <label class="checkout-payment-method">
                    <input type="radio" name="shipping_courier_service" :value="s.code + '-' + s.service" :checked="isSelected(s)" @change="selectService(s)" required />
                    <span>
                      <strong x-text="(s.name || s.code.toUpperCase()) + ' ' + s.service"></strong>
                      <small style="display:block;color:#7d6f5f" x-text="(s.description ? s.description + ' — ' : '') + (s.etd ? s.etd + ' days · ' : '') + formatRp(s.cost)"></small>
                    </span>
                  </label>
                </template>
              </div>

              <div
                x-show="!loadingServices && selectedDest && services.length === 0 && servicesError"
                class="form-error"
                style="margin-top:0.5rem"
                x-text="servicesError"
              ></div>

              <input type="hidden" name="shipping_courier" :value="selectedCourier" />
              <input type="hidden" name="shipping_service" :value="selectedService" />
              <input type="hidden" name="shipping_cost" :value="selectedCost" />
              <input type="hidden" name="shipping_etd" :value="selectedEtd" />
              @error('shipping_courier')<span class="form-error">{{ $message }}</span>@enderror
              @error('shipping_service')<span class="form-error">{{ $message }}</span>@enderror
            </div>
          @else
            <label>
              Region
              <select name="shipping_region" x-model="region" required>
                @foreach ($regions as $key => $r)
                  <option value="{{ $key }}" {{ old('shipping_region', $defaultRegion) === $key ? 'selected' : '' }}>{{ $r['label'] }} — {{ idr($r['cost']) }}</option>
                @endforeach
              </select>
              @error('shipping_region')<span class="form-error">{{ $message }}</span>@enderror
            </label>
          @endif

          <label>
            Notes (optional)
            <textarea name="notes" rows="2" placeholder="Special instructions...">{{ old('notes') }}</textarea>
          </label>

          @if (count($paymentMethods) > 0)
            <h2 class="checkout-section-title">Payment</h2>
            @if (count($paymentMethods) === 1)
              @php $onlyKey = array_key_first($paymentMethods); @endphp
              <input type="hidden" name="payment_method" value="{{ $onlyKey }}" />
              <p class="confirmation-meta" style="margin-top:0">{{ $paymentMethods[$onlyKey] }}</p>
            @else
              <div class="checkout-payment-methods">
                @php $defaultMethod = old('payment_method', array_key_first($paymentMethods)); @endphp
                @foreach ($paymentMethods as $key => $label)
                  <label class="checkout-payment-method">
                    <input type="radio" name="payment_method" value="{{ $key }}" {{ $defaultMethod === $key ? 'checked' : '' }} />
                    <span>{{ $label }}</span>
                  </label>
                @endforeach
              </div>
              @error('payment_method')<span class="form-error">{{ $message }}</span>@enderror
            @endif
          @endif
        </div>

        <aside class="cart-summary checkout-summary">
          <h2 class="cart-summary__title">Order summary</h2>
          <ul class="checkout-items">
            @foreach ($items as $item)
              <li>
                <span class="checkout-item__name">{{ $item->product->icon }} {{ $item->product->name }} <small>× {{ $item->quantity }}</small></span>
                <span>{{ idr($item->line_total) }}</span>
              </li>
            @endforeach
          </ul>
          <div class="cart-summary__row">
            <span>Subtotal</span>
            <strong>{{ idr($subtotal) }}</strong>
          </div>
          @if ($coupon)
            <div class="cart-summary__row" style="color:#1f7a3a">
              <span>Discount ({{ $coupon->code }})</span>
              <strong>− {{ idr($discount) }}</strong>
            </div>
          @else
            <details class="cart-coupon-details" style="margin:8px 0 4px">
              <summary style="cursor:pointer;font-size:13px;color:#1f7a3a">Have a promo code?</summary>
              <form method="post" action="{{ route('cart.coupon.apply') }}" class="cart-coupon" style="margin-top:8px">
                @csrf
                <div class="cart-coupon__row">
                  <input type="text" name="code" placeholder="Enter code" maxlength="64" required />
                  <button type="submit" class="cart-link-btn">Apply</button>
                </div>
              </form>
            </details>
          @endif
          @if ($tax > 0)
            <div class="cart-summary__row">
              <span>{{ $taxInclusive ? 'Tax included ('.rtrim(rtrim(number_format($taxRate, 2), '0'), '.').'%)' : 'Tax ('.rtrim(rtrim(number_format($taxRate, 2), '0'), '.').'%)' }}</span>
              <strong>{{ $taxInclusive ? idr($tax) : '+ '.idr($tax) }}</strong>
            </div>
          @endif
          <div class="cart-summary__row">
            <span>Shipping</span>
            <strong x-text="formatRp(shippingCost())">{{ idr($initialShippingCost) }}</strong>
          </div>
          <div class="cart-summary__total">
            <span>Total</span>
            <strong x-text="formatRp({{ $totalBeforeShipping }} + shippingCost())">{{ idr($totalBeforeShipping + $initialShippingCost) }}</strong>
          </div>

          <button type="submit" class="hero-cta cart-summary__cta" :disabled="!canSubmit()" x-bind:title="canSubmit() ? '' : 'Pick a destination and shipping option first'">Place order</button>
          <a class="cart-link-btn" href="{{ route('cart.show') }}">Back to cart</a>
        </aside>
      </form>
    </section>

    <x-site-footer />
  </main>

  @push('scripts')
    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
      window.formatRp = (n) => 'Rp ' + Number(n).toLocaleString('id-ID')

      window.checkoutForm = (config) => ({
        useRajaOngkir: config.useRajaOngkir,
        regions: config.regions,
        region: config.defaultRegion,

        destQuery: '',
        destResults: [],
        destLoading: false,
        showDestResults: false,
        destError: '',
        selectedDest: null,

        services: [],
        loadingServices: false,
        servicesError: '',
        selectedCourier: '',
        selectedService: '',
        selectedCost: 0,
        selectedEtd: '',

        async searchDestinations() {
          this.destError = ''
          if (this.destQuery.trim().length < 2) {
            this.destResults = []
            return
          }
          this.destLoading = true
          try {
            const res = await fetch(`${config.urls.search}?q=${encodeURIComponent(this.destQuery)}&limit=15`, {
              headers: { 'Accept': 'application/json' },
            })
            const json = await res.json()
            this.destResults = Array.isArray(json.results) ? json.results : []
            if (this.destResults.length === 0) {
              this.destError = json.message || 'No matches. Try the city or postal code.'
            }
          } catch (e) {
            this.destError = 'Search failed. Please try again.'
          } finally {
            this.destLoading = false
          }
        },

        pickDest(r) {
          this.selectedDest = r
          this.destQuery = r.label
          this.showDestResults = false
          this.destResults = []
          this.loadServices()
        },

        clearDest() {
          this.selectedDest = null
          this.destQuery = ''
          this.services = []
          this.selectedCourier = ''
          this.selectedService = ''
          this.selectedCost = 0
          this.selectedEtd = ''
          this.servicesError = ''
        },

        async loadServices() {
          this.services = []
          this.selectedCourier = ''
          this.selectedService = ''
          this.selectedCost = 0
          this.servicesError = ''
          if (!this.selectedDest) return

          this.loadingServices = true
          try {
            const res = await fetch(config.urls.cost, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrf,
                'Accept': 'application/json',
              },
              body: JSON.stringify({
                destination_id: String(this.selectedDest.id),
                weight: config.totalWeight,
              }),
            })
            const json = await res.json()
            this.services = Array.isArray(json.services) ? json.services : []
            if (this.services.length === 0) {
              this.servicesError = json.message || 'No shipping services available for this destination. Please pick a different area or contact us.'
            }
          } catch (e) {
            this.servicesError = 'Could not fetch shipping rates. Please try again.'
          } finally {
            this.loadingServices = false
          }
        },

        isSelected(s) {
          return this.selectedCourier === s.code && this.selectedService === s.service
        },

        selectService(s) {
          this.selectedCourier = s.code
          this.selectedService = s.service
          this.selectedCost = s.cost
          this.selectedEtd = s.etd || ''
        },

        shippingCost() {
          return this.useRajaOngkir ? Number(this.selectedCost || 0) : Number(this.regions[this.region].cost)
        },

        canSubmit() {
          if (!this.useRajaOngkir) return true
          return !!this.selectedDest && !!this.selectedCourier && !!this.selectedService && this.selectedCost > 0
        },
      })
    </script>
  @endpush
@endsection

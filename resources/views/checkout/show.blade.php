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
            "cities" => url("/shipping/cities"),
            "cost" => route("shipping.cost"),
          ],
          "oldProvince" => old("shipping_province_id"),
          "oldCity" => old("shipping_city_id"),
          "oldCourier" => old("shipping_courier"),
          "oldService" => old("shipping_service"),
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
            <textarea name="shipping_address" rows="3" required>{{ old('shipping_address') }}</textarea>
            @error('shipping_address')<span class="form-error">{{ $message }}</span>@enderror
          </label>

          @if ($useRajaOngkir)
            <div class="checkout-row">
              <label>
                Province
                <select x-model="provinceId" @change="loadCities()" required>
                  <option value="">— Select province —</option>
                  @foreach ($provinces as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                  @endforeach
                </select>
              </label>
              <label>
                City / Regency
                <select name="shipping_city_id" x-model="cityId" @change="loadServices()" :disabled="!provinceId" required>
                  <option value="">— Select city —</option>
                  <template x-for="c in cities" :key="c.id">
                    <option :value="c.id" x-text="(c.type ? c.type + ' ' : '') + c.name"></option>
                  </template>
                </select>
                @error('shipping_city_id')<span class="form-error">{{ $message }}</span>@enderror
              </label>
            </div>

            <div x-show="loadingServices" class="confirmation-meta" style="margin-top:0.75rem">
              Calculating shipping cost…
            </div>

            <div x-show="!loadingServices && services.length > 0" class="checkout-payment-methods" style="margin-top:0.75rem">
              <template x-for="s in services" :key="s.courier + '-' + s.service">
                <label class="checkout-payment-method">
                  <input type="radio" name="shipping_courier_service" :value="s.courier + '-' + s.service" :checked="isSelected(s)" @change="selectService(s)" required />
                  <span>
                    <strong x-text="s.courier.toUpperCase() + ' ' + s.service"></strong>
                    <small style="display:block;color:#7d6f5f" x-text="s.description + ' — ' + (s.etd ? s.etd + ' days · ' : '') + formatRp(s.cost)"></small>
                  </span>
                </label>
              </template>
            </div>

            <div
              x-show="!loadingServices && cityId && services.length === 0 && servicesError"
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

          <button type="submit" class="hero-cta cart-summary__cta" :disabled="!canSubmit()" x-bind:title="canSubmit() ? '' : 'Pick a shipping option first'">Place order</button>
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
        provinceId: config.oldProvince || '',
        cityId: config.oldCity || '',
        cities: [],
        services: [],
        loadingServices: false,
        servicesError: '',
        selectedCourier: config.oldCourier || '',
        selectedService: config.oldService || '',
        selectedCost: 0,
        selectedEtd: '',

        init() {
          if (this.useRajaOngkir && this.provinceId) {
            this.loadCities().then(() => {
              if (this.cityId) this.loadServices()
            })
          }
        },

        async loadCities() {
          this.cities = []
          this.services = []
          this.servicesError = ''
          this.selectedCourier = ''
          this.selectedService = ''
          this.selectedCost = 0
          if (!this.provinceId) return
          const res = await fetch(`${config.urls.cities}/${this.provinceId}`)
          this.cities = await res.json()
        },

        async loadServices() {
          this.services = []
          this.selectedCourier = ''
          this.selectedService = ''
          this.selectedCost = 0
          this.servicesError = ''
          if (!this.cityId) return

          this.loadingServices = true
          try {
            const res = await fetch(config.urls.cost, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrf,
                'Accept': 'application/json',
              },
              body: JSON.stringify({ destination_city_id: this.cityId }),
            })
            const json = await res.json()
            this.services = Array.isArray(json.services) ? json.services : []
            if (this.services.length === 0) {
              this.servicesError = 'No shipping services available for this destination. Try a different city or contact us.'
            }
          } catch (e) {
            this.servicesError = 'Could not fetch shipping rates. Please try again.'
          } finally {
            this.loadingServices = false
          }
        },

        isSelected(s) {
          return this.selectedCourier === s.courier && this.selectedService === s.service
        },

        selectService(s) {
          this.selectedCourier = s.courier
          this.selectedService = s.service
          this.selectedCost = s.cost
          this.selectedEtd = s.etd || ''
        },

        shippingCost() {
          return this.useRajaOngkir ? Number(this.selectedCost || 0) : Number(this.regions[this.region].cost)
        },

        canSubmit() {
          if (!this.useRajaOngkir) return true
          return !!this.selectedCourier && !!this.selectedService && this.selectedCost > 0
        },
      })
    </script>
  @endpush
@endsection

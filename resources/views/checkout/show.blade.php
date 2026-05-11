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
      <x-page-head
        :crumbs="[
            ['label' => 'Beranda', 'url' => route('home')],
            ['label' => 'Keranjang', 'url' => route('cart.show')],
            ['label' => 'Checkout'],
        ]"
        eyebrow="Hampir selesai"
      >
        <h1 class="section-title page-head__title cart-title"><em>Checkout</em></h1>
      </x-page-head>

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
          "pickupEnabled" => $pickupEnabled,
          "pickupAddress" => $pickupAddress,
          "csrf" => csrf_token(),
          "urls" => [
          "search" => route("shipping.destinations"),
          "resolveDestination" => route("shipping.resolveDestination"),
          "provinces" => route("shipping.wilayah.provinces"),
          "regencies" => route("shipping.wilayah.regencies", ["provinceCode" => "__PROVINCE__"]),
          "districts" => route("shipping.wilayah.districts", ["regencyCode" => "__REGENCY__"]),
          "villages" => route("shipping.wilayah.villages", ["districtCode" => "__DISTRICT__"]),
            "cost" => route("shipping.cost"),
          ],
        ]))'
      >
        @csrf
        <input type="hidden" name="shipping_mode" :value="mode === 'pickup' ? 'pickup' : (useRajaOngkir ? 'rajaongkir' : 'flat')" />

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

          @if ($pickupEnabled)
            <div class="checkout-payment-methods" style="margin-bottom:0.75rem">
              <label class="checkout-payment-method">
                <input type="radio" name="checkout_mode" value="ship" x-model="mode" />
                <span><strong>🚚 Ship to my address</strong><small style="display:block;color:#7d6f5f">Calculated based on destination</small></span>
              </label>
              <label class="checkout-payment-method">
                <input type="radio" name="checkout_mode" value="pickup" x-model="mode" />
                <span><strong>🏪 Self-pickup at workshop</strong><small style="display:block;color:#7d6f5f">Free — collect at our location</small></span>
              </label>
            </div>
          @endif

          <div x-show="mode === 'pickup'" x-cloak>
            <div class="confirmation-card" style="background:#eef7ee;margin-bottom:0.75rem">
              <p class="confirmation-meta" style="margin:0 0 4px;font-weight:600">Pickup location</p>
              <p class="confirmation-meta" style="margin:0;white-space:pre-line">{{ $pickupAddress ?: 'Address not configured yet.' }}</p>
              @if ($pickupNote)
                <p class="confirmation-meta" style="margin:8px 0 0;color:#7d6f5f">{{ $pickupNote }}</p>
              @endif
            </div>
            <input type="hidden" name="shipping_address" :value="pickupAddress" />
          </div>

          <div x-show="mode !== 'pickup'" x-cloak>
            <label>
              Address
              <textarea name="shipping_address" rows="3" :required="mode !== 'pickup'" placeholder="Street, building number, postal code...">{{ old('shipping_address') }}</textarea>
              @error('shipping_address')<span class="form-error">{{ $message }}</span>@enderror
            </label>
          </div>

          <div x-show="mode !== 'pickup'" x-cloak>
          @if ($useRajaOngkir)
            <div>
              <label>
                Province
                <select x-model="provinceCode" @change="onProvinceChange()" :disabled="mode === 'pickup' || loadingProvinces || resolvingDestination || loadingServices" required>
                  <option value="">Select province</option>
                  <template x-for="row in provinces" :key="row.code">
                    <option :value="row.code" x-text="row.name"></option>
                  </template>
                </select>
              </label>
              <label>
                City / Regency
                <select x-model="regencyCode" @change="onRegencyChange()" :disabled="!provinceCode || mode === 'pickup' || loadingRegencies || resolvingDestination || loadingServices" required>
                  <option value="">Select city / regency</option>
                  <template x-for="row in regencies" :key="row.code">
                    <option :value="row.code" x-text="row.name"></option>
                  </template>
                </select>
              </label>
              <label>
                District
                <select x-model="districtCode" @change="onDistrictChange()" :disabled="!regencyCode || mode === 'pickup' || loadingDistricts || resolvingDestination || loadingServices" required>
                  <option value="">Select district</option>
                  <template x-for="row in districts" :key="row.code">
                    <option :value="row.code" x-text="row.name"></option>
                  </template>
                </select>
              </label>
              <label>
                Village
                <select x-model="villageCode" @change="onVillageChange()" :disabled="!districtCode || mode === 'pickup' || loadingVillages || resolvingDestination || loadingServices" required>
                  <option value="">Select village</option>
                  <template x-for="row in villages" :key="row.code">
                    <option :value="row.code" x-text="row.name"></option>
                  </template>
                </select>
              </label>
              <p class="confirmation-meta" style="margin-top:0.5rem" x-show="loadingProvinces || loadingRegencies || loadingDistricts || loadingVillages" x-cloak>
                Loading area data…
              </p>

              <input type="hidden" name="shipping_province" :value="selectedProvinceName" />
              <input type="hidden" name="shipping_city_id" :value="selectedDest ? selectedDest.id : ''" />
              <input type="hidden" name="shipping_city_name" :value="selectedRegencyName" />

              @error('shipping_city_id')<span class="form-error">{{ $message }}</span>@enderror
              @error('shipping_province')<span class="form-error">{{ $message }}</span>@enderror

              <div x-show="resolvingDestination" class="confirmation-meta" style="margin-top:0.75rem">
                Matching destination to courier area…
              </div>

              <div x-show="destinationError" class="form-error" style="margin-top:0.5rem" x-text="destinationError"></div>
              <div x-show="destinationInfo" class="confirmation-meta" style="margin-top:0.5rem;color:#1f7a3a" x-text="destinationInfo"></div>

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
              <select name="shipping_region" x-model="region" :required="mode !== 'pickup'">
                @foreach ($regions as $key => $r)
                  <option value="{{ $key }}" {{ old('shipping_region', $defaultRegion) === $key ? 'selected' : '' }}>{{ $r['label'] }} — {{ idr($r['cost']) }}</option>
                @endforeach
              </select>
              @error('shipping_region')<span class="form-error">{{ $message }}</span>@enderror
            </label>
          @endif
          </div>

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
        mode: 'ship',
        pickupEnabled: !!config.pickupEnabled,
        pickupAddress: config.pickupAddress || '',

        provinces: [],
        regencies: [],
        districts: [],
        villages: [],
        provinceCode: '',
        regencyCode: '',
        districtCode: '',
        villageCode: '',
        loadingProvinces: false,
        loadingRegencies: false,
        loadingDistricts: false,
        loadingVillages: false,
        destinationError: '',
        destinationInfo: '',
        resolvingDestination: false,
        selectedDest: null,

        services: [],
        loadingServices: false,
        servicesError: '',
        selectedCourier: '',
        selectedService: '',
        selectedCost: 0,
        selectedEtd: '',

        get selectedProvinceName() {
          const row = this.provinces.find((r) => r.code === this.provinceCode)
          return row ? row.name : ''
        },

        get selectedRegencyName() {
          const row = this.regencies.find((r) => r.code === this.regencyCode)
          return row ? row.name : ''
        },

        get selectedDistrictName() {
          const row = this.districts.find((r) => r.code === this.districtCode)
          return row ? row.name : ''
        },

        makeWilayahUrl(template, code, token) {
          return template.replace(token, encodeURIComponent(code))
        },

        async loadProvinces() {
          this.loadingProvinces = true
          try {
            const res = await fetch(config.urls.provinces, { headers: { 'Accept': 'application/json' } })
            const json = await res.json()
            this.provinces = Array.isArray(json.results) ? json.results : []
          } catch (e) {
            this.provinces = []
            this.destinationError = 'Failed to load provinces. Please refresh this page.'
          } finally {
            this.loadingProvinces = false
          }
        },

        async onProvinceChange() {
          this.regencyCode = ''
          this.districtCode = ''
          this.villageCode = ''
          this.regencies = []
          this.districts = []
          this.villages = []
          this.clearDest()
          if (!this.provinceCode) return
          this.loadingRegencies = true
          try {
            const url = this.makeWilayahUrl(config.urls.regencies, this.provinceCode, '__PROVINCE__')
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } })
            const json = await res.json()
            this.regencies = Array.isArray(json.results) ? json.results : []
          } catch (e) {
            this.destinationError = 'Failed to load cities/regencies.'
          } finally {
            this.loadingRegencies = false
          }
        },

        async onRegencyChange() {
          this.districtCode = ''
          this.villageCode = ''
          this.districts = []
          this.villages = []
          this.clearDest()
          if (!this.regencyCode) return
          this.loadingDistricts = true
          try {
            const url = this.makeWilayahUrl(config.urls.districts, this.regencyCode, '__REGENCY__')
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } })
            const json = await res.json()
            this.districts = Array.isArray(json.results) ? json.results : []
          } catch (e) {
            this.destinationError = 'Failed to load districts.'
          } finally {
            this.loadingDistricts = false
          }
        },

        async onDistrictChange() {
          this.villageCode = ''
          this.villages = []
          this.clearDest()
          if (!this.districtCode) return
          this.loadingVillages = true
          try {
            const url = this.makeWilayahUrl(config.urls.villages, this.districtCode, '__DISTRICT__')
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } })
            const json = await res.json()
            this.villages = Array.isArray(json.results) ? json.results : []
          } catch (e) {
            this.destinationError = 'Failed to load villages.'
          } finally {
            this.loadingVillages = false
          }
        },

        async onVillageChange() {
          this.clearDest()
          if (!this.villageCode || !this.provinceCode || !this.regencyCode || !this.districtCode) return
          await this.resolveDestination()
          if (this.selectedDest) {
            await this.loadServices()
          }
        },

        clearDest() {
          this.selectedDest = null
          this.services = []
          this.selectedCourier = ''
          this.selectedService = ''
          this.selectedCost = 0
          this.selectedEtd = ''
          this.servicesError = ''
          this.destinationError = ''
          this.destinationInfo = ''
        },

        async resolveDestination() {
          this.resolvingDestination = true
          this.destinationError = ''
          this.destinationInfo = ''
          try {
            const res = await fetch(config.urls.resolveDestination, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrf,
                'Accept': 'application/json',
              },
              body: JSON.stringify({
                province_name: this.selectedProvinceName,
                regency_name: this.selectedRegencyName,
                district_name: this.selectedDistrictName,
              }),
            })
            const json = await res.json()
            if (json?.destination?.id) {
              this.selectedDest = json.destination
              return
            }
            const fallback = await this.fallbackResolveByRegency()
            if (fallback) {
              this.selectedDest = fallback
              this.destinationInfo = 'Destination matched with fallback (regency-level).'
              return
            }
            this.destinationError = json?.message || 'Destination mapping failed. Please choose another district.'
          } catch (e) {
            this.destinationError = 'Failed to resolve destination for courier.'
          } finally {
            this.resolvingDestination = false
          }
        },

        async fallbackResolveByRegency() {
          if (!this.selectedRegencyName) return null
          try {
            const res = await fetch(`${config.urls.search}?q=${encodeURIComponent(this.selectedRegencyName)}&limit=10`, {
              headers: { 'Accept': 'application/json' },
            })
            const json = await res.json()
            const rows = Array.isArray(json.results) ? json.results : []
            if (rows.length === 0) return null
            return rows[0]
          } catch (e) {
            return null
          }
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
          if (this.mode === 'pickup') return 0
          return this.useRajaOngkir ? Number(this.selectedCost || 0) : Number(this.regions[this.region].cost)
        },

        canSubmit() {
          if (this.mode === 'pickup') return true
          if (!this.useRajaOngkir) return true
          return !!this.selectedDest && !!this.selectedCourier && !!this.selectedService && this.selectedCost > 0
        },

        init() {
          if (this.useRajaOngkir) {
            this.loadProvinces()
          }
        },
      })
    </script>
  @endpush
@endsection

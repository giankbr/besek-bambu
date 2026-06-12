<?php

use App\Mail\NewOrderReceived;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

if (! function_exists('idr')) {
    function idr(int|float|string|null $amount): string
    {
        return 'Rp '.number_format((float) ($amount ?? 0), 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('mail_greeting')) {
    function mail_greeting(string $name): string
    {
        return __('Halo, :name!', ['name' => $name]);
    }
}

if (! function_exists('send_customer_mail')) {
    function send_customer_mail(string $email, Mailable $mailable): void
    {
        Mail::to($email)->locale('id')->send($mailable);
    }
}

if (! function_exists('parse_email_list')) {
    /**
     * @return list<string>
     */
    function parse_email_list(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $parts = preg_split('/[,;]+/', $value) ?: [];

        return array_values(array_unique(array_filter(
            array_map(static fn (string $part): string => trim($part), $parts),
            static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
        )));
    }
}

if (! function_exists('admin_notification_emails')) {
    /**
     * @return list<string>
     */
    function admin_notification_emails(): array
    {
        $configured = parse_email_list((string) setting('order_notification_email', ''));

        if ($configured !== []) {
            return $configured;
        }

        $env = parse_email_list((string) config('mail.admin_address', ''));

        if ($env !== []) {
            return $env;
        }

        $store = store_email();

        return $store ? [$store] : [];
    }
}

if (! function_exists('notify_admin_new_order')) {
    function notify_admin_new_order(Order $order): void
    {
        $recipients = admin_notification_emails();

        if ($recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send(new NewOrderReceived($order->loadMissing('items')));
        } catch (Throwable $e) {
            Log::warning('Failed to send admin new order notification', [
                'order' => $order->number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

if (! function_exists('store_name')) {
    function store_name(): string
    {
        $value = setting('store_name');
        if ($value) {
            return trim((string) $value);
        }

        $appName = trim((string) config('app.name', 'Besek Bambu'));

        return $appName === 'Laravel' ? 'Besek Bambu' : $appName;
    }
}

if (! function_exists('store_email_subject')) {
    /** Branded subject prefix: [Store Name] without stray spaces inside brackets. */
    function store_email_subject(string $text): string
    {
        return '['.store_name().'] '.$text;
    }
}

if (! function_exists('meta_title')) {
    function meta_title(string ...$parts): string
    {
        return implode(', ', array_filter($parts, static fn ($part) => $part !== '' && $part !== null));
    }
}

if (! function_exists('store_logo_url')) {
    function store_logo_url(): ?string
    {
        $logo = setting('store_logo');

        return $logo ? image_src((string) $logo) : null;
    }
}

if (! function_exists('store_email')) {
    function store_email(): ?string
    {
        $value = setting('store_email');

        return $value ? (string) $value : null;
    }
}

if (! function_exists('store_phone')) {
    function store_phone(): ?string
    {
        $value = setting('store_phone');

        return $value ? (string) $value : null;
    }
}

if (! function_exists('whatsapp_digits')) {
    /**
     * WhatsApp number digits for wa.me links (order number, store phone, or social link).
     */
    function whatsapp_digits(): ?string
    {
        $raw = (string) (setting('whatsapp_order_number') ?: setting('store_phone') ?: '');
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits !== '') {
            return $digits;
        }

        $social = trim((string) setting('social_whatsapp'));

        if ($social !== '') {
            if (preg_match('#wa\.me/(\d+)#i', $social, $matches)) {
                return $matches[1];
            }

            if (preg_match('#phone=(\d+)#i', $social, $matches)) {
                return $matches[1];
            }
        }

        $envDigits = preg_replace('/\D+/', '', (string) config('store.whatsapp_number', ''));

        return $envDigits !== '' ? $envDigits : null;
    }
}

if (! function_exists('whatsapp_order_message')) {
    /**
     * Pre-filled WhatsApp text for notifying admin about an order.
     */
    function whatsapp_order_message(Order $order): string
    {
        $lines = [
            __('Halo, saya baru saja buat pesanan di :store.', ['store' => store_name()]),
            '',
            __('No. pesanan: :num', ['num' => $order->number]),
            __('Nama: :name', ['name' => $order->customer_name]),
            __('Total: :total', ['total' => idr($order->total)]),
            __('Pembayaran: :status', ['status' => payment_status_label($order->payment_status)]),
        ];

        $method = payment_method_label($order->payment_method);

        if ($method !== '—') {
            $lines[] = __('Metode: :method', ['method' => $method]);
        }

        $lines[] = '';
        $lines[] = __('Mohon konfirmasi pesanan saya. Terima kasih!');

        return implode("\n", $lines);
    }
}

if (! function_exists('whatsapp_order_url')) {
    function whatsapp_order_url(Order $order): ?string
    {
        $digits = whatsapp_digits();

        if ($digits === null) {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode(whatsapp_order_message($order));
    }
}

if (! function_exists('store_address')) {
    function store_address(): ?string
    {
        $value = setting('store_address');

        return $value ? (string) $value : null;
    }
}

if (! function_exists('store_location_area')) {
    /**
     * Short area label for copy/SEO, derived from the store address setting.
     * e.g. "Mandiraja, Banjarnegara, Jawa Tengah"
     */
    function store_location_area(): string
    {
        $custom = trim((string) setting('store_location_label', ''));

        if ($custom !== '') {
            return $custom;
        }

        $address = trim((string) store_address());

        if ($address === '') {
            return __('Banjarnegara, Jawa Tengah');
        }

        $normalized = preg_replace('/\s+/', ' ', $address);
        $normalized = trim(preg_replace('/\b\d{5}\b/', '', $normalized));
        $parts = array_values(array_filter(array_map('trim', explode(',', $normalized))));

        if ($parts === []) {
            return __('Banjarnegara, Jawa Tengah');
        }

        $province = null;
        $provinceIndex = null;

        foreach ($parts as $index => $part) {
            if (preg_match('/jawa\s+(tengah|timur|barat)|yogyakarta|dki\s+jakarta|bali|banten/i', $part)) {
                $province = $part;
                $provinceIndex = $index;
                break;
            }
        }

        if ($province === null) {
            return $parts[count($parts) - 1];
        }

        unset($parts[$provinceIndex]);
        $parts = array_values($parts);

        if (count($parts) >= 2) {
            $kabupaten = $parts[count($parts) - 1];
            $kecamatan = $parts[count($parts) - 2];

            return "{$kecamatan}, {$kabupaten}, {$province}";
        }

        if (count($parts) === 1) {
            return "{$parts[0]}, {$province}";
        }

        return $province;
    }
}

if (! function_exists('store_faqs')) {
    /**
     * @return list<array{q: string, a: string}>
     */
    function store_faqs(): array
    {
        return [
            [
                'q' => __('Bagaimana produk Anda dibuat?'),
                'a' => __('Setiap produk dianyam tangan oleh pengrajin di :location, dari bambu yang dipanen secara alami. Produksi memakan waktu 2–7 hari per item, tergantung ukuran dan kerumitan.', ['location' => store_location_area()]),
            ],
            [
                'q' => __('Bagaimana cara merawat besek saya?'),
                'a' => __('Lap dengan kain lembap dan keringkan di tempat teduh. Hindari paparan air atau sinar matahari langsung dalam waktu lama. Dengan perawatan yang tepat, besek bisa awet bertahun-tahun.'),
            ],
            [
                'q' => __('Metode pembayaran apa saja yang diterima?'),
                'a' => __('Kami menerima kartu kredit/debit, transfer bank (BCA, BNI, Mandiri, Permata), e-wallet (GoPay, OVO, ShopeePay), dan QRIS. Pembayaran dilayani secara aman melalui Midtrans.'),
            ],
            [
                'q' => __('Berapa lama pengiriman?'),
                'a' => __('Di Jawa, 2–4 hari kerja. Luar Jawa, 4–7 hari kerja. Pengiriman internasional tersedia atas permintaan.'),
            ],
            [
                'q' => __('Bisakah saya mengembalikan produk?'),
                'a' => __('Ya, kami menerima pengembalian dalam 14 hari setelah barang diterima untuk produk yang belum dipakai dan dalam kondisi asli. Pesanan custom tidak dapat dikembalikan.'),
            ],
            [
                'q' => __('Apakah ada harga grosir?'),
                'a' => __('Tentu. Hubungi kami untuk pesanan 25 buah atau lebih. Kami siap melayani UMKM, toko oleh-oleh, hampers, dan penyelenggara acara.'),
            ],
            [
                'q' => __('Apakah produk Anda aman untuk makanan?'),
                'a' => __('Ya. Kami tidak memakai pernis, pewarna, atau bahan finishing. Bambu dicuci, dikeringkan, dan dianyam tanpa bahan kimia tambahan.'),
            ],
        ];
    }
}

if (! function_exists('faq_page_schema')) {
    /**
     * @param  list<array{q: string, a: string}>  $faqs
     * @return array<string, mixed>
     */
    function faq_page_schema(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a'],
                ],
            ], $faqs),
        ];
    }
}

if (! function_exists('store_postal_address_schema')) {
    /**
     * @return array<string, string>|null
     */
    function store_postal_address_schema(): ?array
    {
        $address = trim((string) store_address());

        if ($address === '') {
            return null;
        }

        preg_match('/\b(\d{5})\b/', $address, $postalMatch);
        $withoutPostal = trim(preg_replace('/\b\d{5}\b/', '', $address));
        $parts = array_values(array_filter(array_map('trim', explode(',', $withoutPostal))));

        $schema = [
            '@type' => 'PostalAddress',
            'addressCountry' => 'ID',
        ];

        if (isset($postalMatch[1])) {
            $schema['postalCode'] = $postalMatch[1];
        }

        if ($parts === []) {
            $schema['streetAddress'] = $withoutPostal;

            return $schema;
        }

        $province = null;
        $provinceIndex = null;

        foreach ($parts as $index => $part) {
            if (preg_match('/jawa\s+(tengah|timur|barat)|yogyakarta|dki\s+jakarta|bali|banten/i', $part)) {
                $province = $part;
                $provinceIndex = $index;
                break;
            }
        }

        if ($province !== null) {
            $schema['addressRegion'] = $province;
            unset($parts[$provinceIndex]);
            $parts = array_values($parts);
        }

        if (count($parts) >= 2) {
            $schema['addressLocality'] = $parts[count($parts) - 1];
            $schema['streetAddress'] = implode(', ', array_slice($parts, 0, -1));
        } elseif (count($parts) === 1) {
            $schema['streetAddress'] = $parts[0];
        }

        return $schema;
    }
}

if (! function_exists('store_socials')) {
    /**
     * @return array<string, string> map of platform key => url for non-empty links
     */
    function store_socials(): array
    {
        return collect([
            'instagram' => setting('social_instagram'),
            'facebook' => setting('social_facebook'),
            'tiktok' => setting('social_tiktok'),
            'whatsapp' => setting('social_whatsapp'),
        ])
            ->map(fn ($v) => is_string($v) ? trim($v) : '')
            ->filter(fn ($v) => $v !== '')
            ->all();
    }
}

if (! function_exists('midtrans_channel_catalog')) {
    /**
     * @return array<string, array{label: string, logos: list<array{file: string, alt: string}>}>
     */
    function midtrans_channel_catalog(): array
    {
        return config('midtrans_channels.channels', []);
    }
}

if (! function_exists('midtrans_display_channel_keys')) {
    /**
     * Channel keys shown on checkout and passed to Snap enabled_payments.
     *
     * @return list<string>
     */
    function midtrans_display_channel_keys(): array
    {
        $saved = setting('payment_midtrans_display_channels');
        $catalog = array_keys(midtrans_channel_catalog());

        if (is_array($saved)) {
            return array_values(array_intersect($saved, $catalog));
        }

        return array_values(array_intersect(
            config('midtrans_channels.default_display', []),
            $catalog,
        ));
    }
}

if (! function_exists('midtrans_payment_logos')) {
    /**
     * Payment logos for enabled Midtrans channels (admin settings).
     *
     * @return list<array{file: string, alt: string}>
     */
    function midtrans_payment_logos(): array
    {
        $logos = [];

        foreach (midtrans_display_channel_keys() as $key) {
            $channel = midtrans_channel_catalog()[$key] ?? null;

            if ($channel === null) {
                continue;
            }

            foreach ($channel['logos'] as $logo) {
                $logos[] = $logo;
            }
        }

        return $logos;
    }
}

if (! function_exists('enabled_payment_methods')) {
    /**
     * @return array<string, string> map of method key => label for enabled methods
     */
    function enabled_payment_methods(): array
    {
        $candidates = [
            'midtrans' => [
                'enabled' => (bool) setting('payment_midtrans', true) && (bool) config('services.midtrans.server_key'),
                'label' => __('Bayar online'),
            ],
            'manual_transfer' => [
                'enabled' => (bool) setting('payment_manual_transfer', false),
                'label' => 'Manual bank transfer',
            ],
            'cod' => [
                'enabled' => (bool) setting('payment_cod', false),
                'label' => 'Cash on delivery',
            ],
        ];

        $out = [];
        foreach ($candidates as $key => $info) {
            if ($info['enabled']) {
                $out[$key] = $info['label'];
            }
        }

        return $out;
    }
}

if (! function_exists('image_src')) {
    function image_src(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return asset('storage/'.ltrim($value, '/'));
    }
}

if (! function_exists('grant_order_session_access')) {
    function grant_order_session_access(Order $order): void
    {
        $numbers = session('accessible_order_numbers', []);

        if (! is_array($numbers)) {
            $numbers = [];
        }

        $numbers[] = $order->number;
        $numbers = array_values(array_unique($numbers));

        if (count($numbers) > 20) {
            $numbers = array_slice($numbers, -20);
        }

        session(['accessible_order_numbers' => $numbers]);
    }
}

if (! function_exists('order_signed_url')) {
    function order_signed_url(string $routeName, Order $order, ?DateTimeInterface $expires = null): string
    {
        return URL::temporarySignedRoute(
            $routeName,
            $expires ?? now()->addDays(30),
            ['order' => $order],
        );
    }
}

if (! function_exists('order_status_label')) {
    /**
     * Label status pesanan dalam Bahasa Indonesia (bukan nilai teknis Inggris).
     */
    function order_status_label(string $status): string
    {
        return match ($status) {
            'pending' => __('Menunggu'),
            'paid' => __('Sedang diproses'),
            'shipped' => __('Dikirim'),
            'delivered' => __('Sampai'),
            'cancelled' => __('Dibatalkan'),
            default => ucfirst($status),
        };
    }
}

if (! function_exists('payment_status_label')) {
    function payment_status_label(string $status): string
    {
        return match ($status) {
            'unpaid' => __('Belum dibayar'),
            'pending' => __('Menunggu pembayaran'),
            'paid' => __('Lunas'),
            'failed' => __('Gagal'),
            'expired' => __('Kedaluwarsa'),
            'refunded' => __('Dikembalikan'),
            default => ucfirst($status),
        };
    }
}

if (! function_exists('is_midtrans_payment_method')) {
    /**
     * Payment types returned by Midtrans Snap / notification webhooks.
     */
    function is_midtrans_payment_method(?string $method): bool
    {
        if ($method === null || $method === '') {
            return false;
        }

        return in_array($method, [
            'midtrans',
            'qris',
            'gopay',
            'shopeepay',
            'dana',
            'bank_transfer',
            'bca_va',
            'bni_va',
            'bri_va',
            'permata_va',
            'other_va',
            'credit_card',
            'echannel',
            'akulaku',
            'kredivo',
            'cstore',
            'indomaret',
            'alfamart',
        ], true);
    }
}

if (! function_exists('order_can_pay_with_midtrans')) {
    function order_can_pay_with_midtrans(Order $order): bool
    {
        if (! $order->canBePaid()) {
            return false;
        }

        if (! setting('payment_midtrans', true) || ! config('services.midtrans.server_key')) {
            return false;
        }

        return is_midtrans_payment_method($order->payment_method)
            || filled($order->payment_token);
    }
}

if (! function_exists('payment_method_label')) {
    function payment_method_label(?string $method): string
    {
        if ($method === null || $method === '') {
            return '—';
        }

        return match ($method) {
            'midtrans' => __('Bayar online'),
            'manual_transfer' => __('Transfer bank manual'),
            'cod' => __('Bayar di tempat (COD)'),
            'bank_transfer' => __('Transfer bank'),
            'bca_va' => __('VA BCA'),
            'bni_va' => __('VA BNI'),
            'bri_va' => __('VA BRI'),
            'permata_va' => __('VA Permata'),
            'other_va' => __('Virtual Account'),
            'echannel' => __('Mandiri Bill Payment'),
            'credit_card' => __('Kartu kredit/debit'),
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'qris' => 'QRIS',
            'dana' => 'DANA',
            'akulaku' => 'Akulaku',
            'kredivo' => 'Kredivo',
            'cstore', 'indomaret', 'alfamart' => __('Gerai retail'),
            default => ucwords(str_replace('_', ' ', $method)),
        };
    }
}

if (! function_exists('default_meta_description')) {
    function default_meta_description(): string
    {
        $custom = trim((string) setting('seo_default_meta_description', ''));

        if ($custom !== '') {
            return $custom;
        }

        return __('Besek bambu handmade untuk hantaran, hampers, seserahan & kemasan ramah lingkungan. Anyaman pengrajin di :location, kirim ke seluruh Indonesia.', [
            'location' => store_location_area(),
        ]);
    }
}

if (! function_exists('default_home_meta_title')) {
    function default_home_meta_title(): string
    {
        return meta_title(
            __('Besek Bambu Handmade — Hantaran, Hampers & Kemasan Anyaman'),
            store_name(),
        );
    }
}

if (! function_exists('default_shop_meta_title')) {
    function default_shop_meta_title(): string
    {
        return meta_title(
            __('Produk Besek Bambu 7×7–20×20 — Hantaran & Hampers'),
            store_name(),
        );
    }
}

if (! function_exists('default_shop_meta_description')) {
    function default_shop_meta_description(): string
    {
        return __('Jelajahi katalog besek bambu handmade berbagai ukuran. Pilihan untuk hantaran, hampers, seserahan, souvenir, dan kemasan ramah lingkungan. Pesan online, kirim ke seluruh Indonesia.');
    }
}

if (! function_exists('seo_category_meta_title')) {
    function seo_category_meta_title(Category $category): string
    {
        return meta_title(
            __('Besek Bambu :size — Hantaran, Hampers & Souvenir', ['size' => $category->title]),
            store_name(),
        );
    }
}

if (! function_exists('seo_category_meta_description')) {
    function seo_category_meta_description(Category $category): string
    {
        return __('Koleksi besek bambu :size handmade dari pengrajin :location. Cocok untuk hantaran, hampers, seserahan, dan kemasan ramah lingkungan. Siap kirim ke seluruh Indonesia.', [
            'size' => $category->title,
            'location' => store_location_area(),
        ]);
    }
}

if (! function_exists('generate_blog_seo_meta')) {
    /**
     * @return array{meta_title: string, meta_description: string}
     */
    function generate_blog_seo_meta(string $title, ?string $excerpt = null, ?string $body = null, string $locale = 'id'): array
    {
        $metaTitle = Str::limit(meta_title($title, store_name()), 160, '');

        $plain = trim((string) $excerpt);
        if ($plain === '') {
            $plain = preg_replace('/\s+/u', ' ', trim(strip_tags($body ?? '')));
        }

        if (mb_strlen($plain) >= 80) {
            $metaDescription = Str::limit($plain, 155, '…');
        } elseif ($locale === 'en') {
            $metaDescription = Str::limit(
                "Bamboo basket article: {$title}. Tips for hantaran, hampers, wedding gifts & eco-friendly packaging from ".store_name().'.',
                155,
                '…',
            );
        } else {
            $metaDescription = Str::limit(
                __('Artikel besek bambu: :title. Tips hantaran, hampers, seserahan & kemasan ramah lingkungan dari :brand.', [
                    'title' => $title,
                    'brand' => store_name(),
                ]),
                155,
                '…',
            );
        }

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
        ];
    }
}

if (! function_exists('social_share_urls')) {
    /**
     * @return array{whatsapp: string, facebook: string, twitter: string}
     */
    function social_share_urls(string $title, string $url): array
    {
        $urlEnc = rawurlencode($url);
        $titleEnc = rawurlencode($title);
        $messageEnc = rawurlencode(trim($title).' — '.store_name());

        return [
            'whatsapp' => 'https://wa.me/?text='.$messageEnc.'%20'.$urlEnc,
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u='.$urlEnc,
            'twitter' => 'https://twitter.com/intent/tweet?text='.$titleEnc.'&url='.$urlEnc,
        ];
    }
}

if (! function_exists('generate_product_seo_meta')) {
    /**
     * @return array{meta_title: string, meta_description: string}
     */
    function generate_product_seo_meta(string $name, ?string $description = null, ?string $categoryTitle = null): array
    {
        $brand = store_name();
        $location = store_location_area();
        $sizeSuffix = $categoryTitle ? " {$categoryTitle}" : '';

        $metaTitle = meta_title(
            trim("{$name} — Besek Bambu{$sizeSuffix}"),
            $brand,
        );
        $metaTitle = Str::limit($metaTitle, 160, '');

        $plain = strip_tags($description ?? '');
        $plain = preg_replace('/\s+/u', ' ', trim($plain));

        if (mb_strlen($plain) >= 80) {
            $metaDescription = Str::limit($plain, 155, '…');
        } else {
            $sizeLabel = $categoryTitle ? __(' ukuran :size', ['size' => $categoryTitle]) : '';
            $metaDescription = __('Besek bambu:category handmade dari pengrajin di :location. Cocok untuk hantaran, hampers, seserahan & kemasan ramah lingkungan. Pesan di :brand.', [
                'category' => $sizeLabel,
                'location' => $location,
                'brand' => $brand,
            ]);
            $metaDescription = Str::limit($metaDescription, 155, '…');
        }

        return [
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
        ];
    }
}

if (! function_exists('default_og_image_url')) {
    function default_og_image_url(): ?string
    {
        $og = setting('seo_default_og_image');

        if ($og) {
            return image_src((string) $og);
        }

        return store_logo_url();
    }
}

if (! function_exists('locale_query_params')) {
    /**
     * Query string for a locale variant. Indonesian is the default and omits ?lang=id.
     *
     * @return array<string, string>
     */
    function locale_query_params(?string $locale = null): array
    {
        $query = request()->query();
        unset($query['lang']);

        $locale ??= app()->getLocale();

        if ($locale === 'en') {
            $query['lang'] = 'en';
        }

        return $query;
    }
}

if (! function_exists('canonical_url')) {
    /** Canonical URL for the current page, including pagination and ?lang=en when active. */
    function canonical_url(): string
    {
        $query = locale_query_params();
        $url = request()->url();

        if ($query === []) {
            return $url;
        }

        return $url.'?'.http_build_query($query);
    }
}

if (! function_exists('localized_url_for_full_url')) {
    /** Build a locale variant for an absolute URL (used by /lang/{locale} fallback). */
    function localized_url_for_full_url(string $locale, string $fullUrl): string
    {
        $parts = parse_url($fullUrl) ?: [];
        $query = [];

        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        unset($query['lang']);

        if ($locale === 'en') {
            $query['lang'] = 'en';
        }

        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? parse_url((string) config('app.url'), PHP_URL_HOST);
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '/';
        $origin = "{$scheme}://{$host}{$port}";

        if ($path === '/' && $query === []) {
            return $origin;
        }

        $url = $origin.$path;

        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return $url;
    }
}

if (! function_exists('localized_switch_target')) {
    /** Redirect target after switching locale via /lang/{locale}. */
    function localized_switch_target(string $locale): string
    {
        $previous = url()->previous();
        $appRoot = rtrim((string) config('app.url'), '/');
        $previousHost = is_string($previous) ? parse_url($previous, PHP_URL_HOST) : null;
        $requestHost = request()->getHost();

        if (
            is_string($previous)
            && $previous !== ''
            && $previousHost === $requestHost
            && ! str_contains(parse_url($previous, PHP_URL_PATH) ?? '', '/lang/')
        ) {
            return localized_url_for_full_url($locale, $previous);
        }

        return localized_url_for_full_url($locale, url('/'));
    }
}

if (! function_exists('localized_url')) {
    /** Hreflang alternate URL for a locale (id = clean URL without lang param). */
    function localized_url(string $locale): string
    {
        $query = locale_query_params($locale);
        $url = request()->url();

        if ($query === []) {
            return $url;
        }

        return $url.'?'.http_build_query($query);
    }
}

if (! function_exists('twitter_handle')) {
    function twitter_handle(): ?string
    {
        $handle = trim((string) setting('social_twitter', ''));

        if ($handle === '') {
            return null;
        }

        return str_starts_with($handle, '@') ? $handle : '@'.$handle;
    }
}

if (! function_exists('seo_meta_text')) {
    /** Escape meta/title text once even when the source already contains entities. */
    function seo_meta_text(?string $value): string
    {
        $value = trim(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return e($value);
    }
}

if (! function_exists('seo_site_root')) {
    function seo_site_root(): string
    {
        return rtrim(url('/'), '/');
    }
}

if (! function_exists('seo_schema_id')) {
    function seo_schema_id(string $fragment): string
    {
        return seo_site_root().'/#'.ltrim($fragment, '#');
    }
}

if (! function_exists('seo_store_social_urls')) {
    /**
     * @return list<string>
     */
    function seo_store_social_urls(): array
    {
        return collect([
            setting('social_instagram'),
            setting('social_facebook'),
            setting('social_tiktok'),
            setting('social_whatsapp'),
        ])->filter()->values()->all();
    }
}

if (! function_exists('seo_merchant_return_policy_schema')) {
    /**
     * @return array<string, mixed>
     */
    function seo_merchant_return_policy_schema(): array
    {
        return [
            '@type' => 'MerchantReturnPolicy',
            'applicableCountry' => 'ID',
            'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
            'merchantReturnDays' => 14,
            'returnMethod' => 'https://schema.org/ReturnByMail',
            'returnFees' => 'https://schema.org/FreeReturn',
        ];
    }
}

if (! function_exists('seo_offer_shipping_details_schema')) {
    /**
     * @return array<string, mixed>
     */
    function seo_offer_shipping_details_schema(?Product $product = null): array
    {
        $leadDays = max(0, (int) ($product?->production_lead_days ?? 0));
        $handlingMin = $leadDays > 0 ? $leadDays : 2;
        $handlingMax = max($handlingMin, $leadDays > 0 ? $leadDays : 7);

        return [
            '@type' => 'OfferShippingDetails',
            'shippingRate' => [
                '@type' => 'MonetaryAmount',
                'value' => 0,
                'currency' => 'IDR',
            ],
            'shippingDestination' => [
                '@type' => 'DefinedRegion',
                'addressCountry' => 'ID',
            ],
            'deliveryTime' => [
                '@type' => 'ShippingDeliveryTime',
                'handlingTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => $handlingMin,
                    'maxValue' => $handlingMax,
                    'unitCode' => 'DAY',
                ],
                'transitTime' => [
                    '@type' => 'QuantitativeValue',
                    'minValue' => 2,
                    'maxValue' => 7,
                    'unitCode' => 'DAY',
                ],
            ],
        ];
    }
}

if (! function_exists('seo_product_offer_schema')) {
    /**
     * @return array<string, mixed>
     */
    function seo_product_offer_schema(Product $product): array
    {
        return [
            '@type' => 'Offer',
            'price' => (float) $product->price,
            'priceCurrency' => 'IDR',
            'priceValidUntil' => now()->addYear()->toDateString(),
            'availability' => $product->stock > 0
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'url' => route('shop.product', $product),
            'seller' => ['@type' => 'Organization', 'name' => store_name()],
            'shippingDetails' => seo_offer_shipping_details_schema($product),
            'hasMerchantReturnPolicy' => seo_merchant_return_policy_schema(),
        ];
    }
}

if (! function_exists('seo_product_schema_images')) {
    /**
     * @return list<string>
     */
    function seo_product_schema_images(Product $product): array
    {
        $images = collect();

        if ($product->image_url) {
            $images->push(image_src($product->image_url));
        }

        foreach ($product->images ?? [] as $image) {
            $src = image_src($image->path);
            if ($src && ! $images->contains($src)) {
                $images->push($src);
            }
        }

        return $images->values()->all();
    }
}

if (! function_exists('seo_product_review_nodes')) {
    /**
     * @param  iterable<int, ProductReview>|null  $reviews
     * @return list<array<string, mixed>>
     */
    function seo_product_review_nodes(Product $product, ?iterable $reviews = null): array
    {
        if ($reviews === null) {
            $reviews = $product->approvedReviews()
                ->with('user:id,name')
                ->latest()
                ->limit(10)
                ->get();
        }

        $nodes = [];

        foreach ($reviews as $review) {
            $body = trim((string) ($review->body ?: $review->title));
            if ($body === '') {
                continue;
            }

            $nodes[] = array_filter([
                '@type' => 'Review',
                'author' => [
                    '@type' => 'Person',
                    'name' => $review->user?->name ?: __('Pelanggan'),
                ],
                'datePublished' => $review->created_at?->toDateString(),
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (int) $review->rating,
                    'bestRating' => 5,
                    'worstRating' => 1,
                ],
                'reviewBody' => $body,
            ]);
        }

        return $nodes;
    }
}

if (! function_exists('seo_product_aggregate_rating_schema')) {
    /**
     * @param  iterable<int, ProductReview>|null  $reviews
     * @return array<string, mixed>|null
     */
    function seo_product_aggregate_rating_schema(Product $product, ?iterable $reviews = null): ?array
    {
        if ($reviews === null) {
            $reviews = $product->approvedReviews()->get();
        }

        $collection = $reviews instanceof Collection
            ? $reviews
            : collect($reviews);

        $count = $collection->count();

        if ($count === 0) {
            return null;
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => round((float) $collection->avg('rating'), 1),
            'reviewCount' => $count,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }
}

if (! function_exists('seo_product_schema_node')) {
    /**
     * Product node for @graph (no @context).
     *
     * @param  iterable<int, ProductReview>|null  $reviews
     * @return array<string, mixed>
     */
    function seo_product_schema_node(Product $product, ?iterable $reviews = null): array
    {
        $productUrl = route('shop.product', $product);
        $images = seo_product_schema_images($product);
        $reviewNodes = seo_product_review_nodes($product, $reviews);

        $node = array_filter([
            '@type' => 'Product',
            '@id' => $productUrl.'#product',
            'name' => $product->name,
            'description' => strip_tags((string) $product->description),
            'sku' => 'BSK-'.$product->id,
            'mpn' => 'BSK-'.$product->id,
            'image' => $images !== [] ? $images : null,
            'category' => $product->category?->title,
            'brand' => ['@type' => 'Brand', 'name' => store_name()],
            'offers' => seo_product_offer_schema($product),
            'aggregateRating' => seo_product_aggregate_rating_schema($product, $reviews),
            'review' => $reviewNodes !== [] ? $reviewNodes : null,
        ], fn ($value) => $value !== null && $value !== []);

        return $node;
    }
}

if (! function_exists('seo_product_schema_document')) {
    /**
     * Standalone Product JSON-LD document.
     *
     * @param  iterable<int, ProductReview>|null  $reviews
     * @return array<string, mixed>
     */
    function seo_product_schema_document(Product $product, ?iterable $reviews = null): array
    {
        return [
            '@context' => 'https://schema.org',
            ...seo_product_schema_node($product, $reviews),
        ];
    }
}

if (! function_exists('seo_schema_graph')) {
    /**
     * Yoast-style linked schema graph for Organization + WebSite + LocalBusiness.
     *
     * @param  list<array<string, mixed>>  $extra
     * @return array{'@context': string, '@graph': list<array<string, mixed>>}
     */
    function seo_schema_graph(array $extra = []): array
    {
        $brand = store_name();
        $root = seo_site_root();
        $orgId = seo_schema_id('organization');
        $websiteId = seo_schema_id('website');
        $logoUrl = store_logo_url();
        $socials = seo_store_social_urls();
        $postalAddress = store_postal_address_schema();

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => $brand,
            'alternateName' => array_values(array_unique(array_filter([
                $brand,
                'Besek Bambu',
                'besek bambu',
            ]))),
            'url' => $root.'/',
            'logo' => $logoUrl ? [
                '@type' => 'ImageObject',
                'url' => $logoUrl,
                'width' => 200,
                'height' => 60,
            ] : null,
            'email' => store_email() ?: null,
            'telephone' => store_phone() ?: null,
            'address' => $postalAddress,
            'sameAs' => $socials !== [] ? $socials : null,
        ], fn ($value) => $value !== null && $value !== []);

        $website = [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => $root.'/',
            'name' => $brand,
            'description' => default_meta_description(),
            'publisher' => ['@id' => $orgId],
            'inLanguage' => app()->getLocale() === 'en' ? 'en-US' : 'id-ID',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('shop.index').'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $localBusiness = array_filter([
            '@type' => 'LocalBusiness',
            '@id' => seo_schema_id('localbusiness'),
            'name' => $brand,
            'url' => $root.'/',
            'image' => default_og_image_url() ?: $logoUrl,
            'telephone' => store_phone() ?: null,
            'email' => store_email() ?: null,
            'address' => $postalAddress,
            'areaServed' => store_location_area(),
            'sameAs' => $socials !== [] ? $socials : null,
        ], fn ($value) => $value !== null && $value !== []);

        return [
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_filter([$organization, $website, $localBusiness, ...$extra])),
        ];
    }
}

if (! function_exists('seo_webpage_node')) {
    /**
     * @return array<string, mixed>
     */
    function seo_webpage_node(string $url, string $name, ?string $description = null, ?string $image = null): array
    {
        $node = [
            '@type' => 'WebPage',
            '@id' => rtrim($url, '/').'#webpage',
            'url' => $url,
            'name' => $name,
            'isPartOf' => ['@id' => seo_schema_id('website')],
            'about' => ['@id' => seo_schema_id('organization')],
            'inLanguage' => app()->getLocale() === 'en' ? 'en-US' : 'id-ID',
        ];

        if ($description) {
            $node['description'] = $description;
        }

        if ($image) {
            $node['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => $image,
                'width' => 1200,
                'height' => 630,
            ];
        }

        return $node;
    }
}

if (! function_exists('seo_json_ld')) {
    /**
     * @param  array<string, mixed>  $data
     */
    function seo_json_ld(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}

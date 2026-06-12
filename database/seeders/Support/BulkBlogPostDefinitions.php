<?php

namespace Database\Seeders\Support;

/**
 * Generates 90 additional SEO-focused blog post definitions (sort_order 11–100).
 */
class BulkBlogPostDefinitions
{
    private const CTA_ID = 'Lihat <a href="/shop">katalog besek</a> atau <a href="/grosir">pesan grosir</a> untuk acara dan bisnis Anda.';

    private const CTA_EN = 'Browse our <a href="/shop">basket catalog</a> or <a href="/grosir">order wholesale</a> for your event or business.';

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $posts = [];
        $sort = 11;
        $daysAgo = 28;

        $sizes = [
            ['label' => '7×7', 'slug' => '7x7', 'detail' => 'souvenir mini dan token tamu', 'detail_en' => 'mini souvenirs and guest tokens'],
            ['label' => '12×12', 'slug' => '12x12', 'detail' => 'parsel ringan dan hampers compact', 'detail_en' => 'light parcels and compact hampers'],
            ['label' => '15×15', 'slug' => '15x15', 'detail' => 'hantaran standar dan gift set UMKM', 'detail_en' => 'standard hantaran and MSME gift sets'],
            ['label' => '16×16', 'slug' => '16x16', 'detail' => 'isian sedikit lebih lapang dengan dekor pita', 'detail_en' => 'slightly roomier fills with ribbon decor'],
            ['label' => '20×20', 'slug' => '20x20', 'detail' => 'hampers premium dan parcel korporat', 'detail_en' => 'premium hampers and corporate parcels'],
        ];

        $useCases = [
            ['label' => 'hantaran pernikahan', 'label_en' => 'wedding hantaran', 'slug' => 'hantaran-pernikahan', 'keyword' => 'hantaran', 'keyword_en' => 'hantaran'],
            ['label' => 'hampers hadiah', 'label_en' => 'gift hampers', 'slug' => 'hampers-hadiah', 'keyword' => 'hampers', 'keyword_en' => 'hampers'],
            ['label' => 'seserahan adat', 'label_en' => 'traditional seserahan', 'slug' => 'seserahan-adat', 'keyword' => 'seserahan', 'keyword_en' => 'seserahan'],
            ['label' => 'souvenir acara', 'label_en' => 'event souvenirs', 'slug' => 'souvenir-acara', 'keyword' => 'souvenir', 'keyword_en' => 'souvenirs'],
            ['label' => 'kemasan UMKM', 'label_en' => 'MSME packaging', 'slug' => 'kemasan-umkm', 'keyword' => 'kemasan', 'keyword_en' => 'packaging'],
            ['label' => 'parcel oleh-oleh', 'label_en' => 'souvenir parcels', 'slug' => 'parcel-oleh-oleh', 'keyword' => 'oleh-oleh', 'keyword_en' => 'souvenir gifts'],
        ];

        foreach ($sizes as $size) {
            foreach ($useCases as $use) {
                $title = "Besek Bambu {$size['label']} untuk {$use['label']}";
                $titleEn = "Bamboo Basket {$size['label']} for {$use['label_en']}";
                $slug = "besek-bambu-{$size['slug']}-{$use['slug']}";
                $posts[] = self::entry(
                    title: $title,
                    slug: $slug,
                    excerpt: "Panduan memakai besek bambu {$size['label']} sebagai {$use['label']}: tips isian, tampilan, dan estimasi kebutuhan pesanan.",
                    metaTitle: "{$title} | Besek Anyaman",
                    metaDescription: "Besek bambu {$size['label']} untuk {$use['label']}. Tips packing {$use['keyword']}, food-safe, dan pesan grosir dari pengrajin.",
                    body: self::body(
                        "Ukuran {$size['label']} banyak dipilih untuk {$use['label']} karena proporsinya pas untuk {$size['detail']}. Anyaman bambu handmade memberi kesan natural yang sulit ditiru kemasan karton atau plastik.",
                        [
                            self::section('Kapan ukuran ini cocok', [
                                "Untuk {$use['label']}, besek {$size['label']} membantu presentasi rapi tanpa memakan terlalu banyak ruang di meja atau rak display.",
                                'Pastikan isian tidak terlalu berat agar struktur anyaman tetap kokoh selama acara atau pengiriman.',
                            ], [
                                'Cocok untuk foto produk dan flat lay di media sosial.',
                                'Mudah ditambah pita, kartu ucapan, atau stiker logo brand.',
                                'Bisa dipakai ulang oleh penerima sebagai organizer kecil.',
                            ]),
                            self::section('Tips packing', [
                                'Gunakan lapisan food-grade untuk makanan. Isi tidak boleh bergesekan langsung dengan anyaman basah.',
                                "Untuk pesanan banyak, konsultasikan finishing seragam agar seluruh {$use['label']} terlihat konsisten.",
                            ]),
                        ],
                        self::CTA_ID,
                    ),
                    titleEn: $titleEn,
                    excerptEn: "Guide to using {$size['label']} bamboo baskets for {$use['label_en']}: filling ideas, presentation, and order estimates.",
                    metaTitleEn: "{$titleEn} | Woven Baskets",
                    metaDescriptionEn: "{$size['label']} bamboo basket for {$use['label_en']}. {$use['keyword_en']} packing tips, food-safe use, and wholesale from artisans.",
                    bodyEn: self::body(
                        "Size {$size['label']} is popular for {$use['label_en']} because it fits {$size['detail_en']}. Handmade bamboo weave feels natural in a way cardboard or plastic cannot match.",
                        [
                            self::section('When this size works', [
                                "For {$use['label_en']}, a {$size['label']} basket keeps presentation neat without taking too much table or shelf space.",
                                'Avoid overly heavy fills so the weave stays sturdy during events or shipping.',
                            ], [
                                'Great for product photos and social flat lays.',
                                'Easy to add ribbon, greeting cards, or brand logo stickers.',
                                'Recipients can reuse the basket as a small organiser.',
                            ]),
                            self::section('Packing tips', [
                                'Use food-grade liners for edibles. Avoid direct contact with damp weave.',
                                "For bulk orders, align on uniform finishing so every {$use['label_en']} set looks consistent.",
                            ]),
                        ],
                        self::CTA_EN,
                    ),
                    sortOrder: $sort++,
                    daysAgo: $daysAgo++,
                );
            }
        }

        $occasions = [
            ['Lamaran & tunangan', 'Engagement & proposal', 'lamaran-tunangan', 'lamaran', 'engagements'],
            ['Akad nikah', 'Wedding akad', 'akad-nikah', 'akad', 'wedding akad'],
            ['Tasyakuran & syukuran', 'Thanksgiving gatherings', 'tasyakuran', 'syukuran', 'thanksgiving events'],
            ['Aqiqah & khitanan', 'Aqiqah & circumcision', 'aqiqah-khitanan', 'aqiqah', 'aqiqah celebrations'],
            ['Ulang tahun & anniversary', 'Birthdays & anniversaries', 'ulang-tahun', 'ulang tahun', 'birthdays'],
            ['Wisuda & kelulusan', 'Graduation', 'wisuda', 'wisuda', 'graduation'],
            ['Grand opening toko', 'Store grand opening', 'grand-opening', 'grand opening', 'store openings'],
            ['Halal bihalal kantor', 'Office halal bihalal', 'halal-bihalal', 'halal bihalal', 'office gatherings'],
            ['Parcel Natal & tahun baru', 'Christmas & New Year parcels', 'natal-tahun-baru', 'Natal', 'holiday parcels'],
            ['Imlek & Gong Xi', 'Lunar New Year', 'imlek', 'Imlek', 'Lunar New Year'],
            ['Hadiah Hari Ibu & Ayah', "Mother's & Father's Day gifts", 'hadiah-orang-tua', 'hadiah orang tua', 'parent appreciation gifts'],
            ['Corporate appreciation day', 'Corporate appreciation day', 'corporate-appreciation', 'apresiasi karyawan', 'employee appreciation'],
            ['Gathering komunitas', 'Community gatherings', 'gathering-komunitas', 'gathering', 'community events'],
            ['Peluncuran produk UMKM', 'MSME product launch', 'peluncuran-produk', 'product launch', 'product launches'],
            ['Bazar & pop-up market', 'Bazaar & pop-up market', 'bazar-popup', 'bazar', 'pop-up markets'],
        ];

        foreach ($occasions as [$label, $labelEn, $slugPart, $keyword, $keywordEn]) {
            $title = "Ide Besek Bambu untuk {$label}";
            $titleEn = "Bamboo Basket Ideas for {$labelEn}";
            $posts[] = self::entry(
                title: $title,
                slug: "ide-besek-bambu-{$slugPart}",
                excerpt: "Inspirasi kemasan besek anyaman untuk {$label}: ukuran rekomendasi, isian, dan tips pesan dalam jumlah banyak.",
                metaTitle: "{$title} — Hampers & Hantaran",
                metaDescription: "Ide besek bambu untuk {$keyword}: packing rapi, ramah lingkungan, dan cocok untuk hadiah personal maupun korporat.",
                body: self::body(
                    "{$label} sering membutuhkan kemasan yang terasa spesial tanpa boros plastik sekali pakai. Besek bambu memberi nuansa hangat dan lokal yang cocok dengan berbagai tema acara.",
                    [
                        self::section('Ukuran yang sering dipakai', [
                            'Acara intim: 12×12 atau 15×15. Acara korporat atau keluarga besar: 16×16 hingga 20×20.',
                        ], [
                            'Sesuaikan isian dengan durasi acara — makanan segar perlu lapisan dalam.',
                            'Tambahkan kartu ucapan agar penerima mengingat momen.',
                        ]),
                        self::section('Pesan grosir untuk acara', [
                            "Untuk {$keyword}, pesanan 25+ pcs membantu harga unit lebih efisien dan finishing seragam.",
                            'Pesan minimal 2–3 minggu sebelum hari H untuk menghindari kekurangan stok musiman.',
                        ]),
                    ],
                    'Diskusikan kebutuhan Anda di <a href="/contact">halaman kontak</a> atau lihat paket <a href="/grosir">grosir & custom</a>.',
                ),
                titleEn: $titleEn,
                excerptEn: "Woven basket packaging inspiration for {$labelEn}: recommended sizes, fillings, and bulk order tips.",
                metaTitleEn: "{$titleEn} — Hampers & Gifts",
                metaDescriptionEn: "Bamboo basket ideas for {$keywordEn}: neat packing, eco-friendly, suited for personal and corporate gifts.",
                bodyEn: self::body(
                    "{$labelEn} often needs packaging that feels special without single-use plastic waste. Bamboo baskets add a warm, local tone for many event themes.",
                    [
                        self::section('Sizes commonly used', [
                            'Intimate events: 12×12 or 15×15. Corporate or large family events: 16×16 to 20×20.',
                        ], [
                            'Match fillings to event duration — fresh food needs inner liners.',
                            'Add greeting cards so recipients remember the moment.',
                        ]),
                        self::section('Wholesale for events', [
                            "For {$keywordEn}, orders of 25+ pcs improve unit pricing and uniform finishing.",
                            'Order at least 2–3 weeks before the event to avoid seasonal stock shortages.',
                        ]),
                    ],
                    'Discuss your needs on our <a href="/contact">contact page</a> or see <a href="/grosir">wholesale & custom</a> packages.',
                ),
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $verticals = [
            ['Bakery & roti artisan', 'Artisan bakery & bread', 'bakery-roti', 'toko roti', 'bakeries'],
            ['Kue kering & cookies', 'Cookies & dry cakes', 'kue-kering', 'kue kering', 'cookies'],
            ['Kopi & specialty coffee', 'Specialty coffee', 'kopi-specialty', 'kopi', 'coffee'],
            ['Teh herbal & infus', 'Herbal & infusion tea', 'teh-herbal', 'teh', 'tea'],
            ['Keripik & camilan', 'Chips & snacks', 'keripik-camilan', 'keripik', 'snacks'],
            ['Sambal & bumbu khas', 'Sambal & spice blends', 'sambal-bumbu', 'sambal', 'sambal'],
            ['Madu & produk lebah', 'Honey & bee products', 'madu-propolis', 'madu', 'honey'],
            ['Dodol & jenang tradisional', 'Dodol & traditional sweets', 'dodol-jenang', 'dodol', 'dodol'],
            ['Cokelat & confectionery', 'Chocolate & confectionery', 'cokelat-confectionery', 'cokelat', 'chocolate'],
            ['Granola & healthy snack', 'Granola & healthy snacks', 'granola-snack', 'granola', 'granola'],
            ['Skincare & spa lokal', 'Local skincare & spa', 'skincare-spa', 'skincare', 'skincare'],
            ['Kerajinan tangan', 'Handicrafts', 'kerajinan-tangan', 'kerajinan', 'handicrafts'],
            ['Hotel & guest amenity', 'Hotel guest amenities', 'hotel-amenity', 'hotel', 'hotels'],
            ['Cafe takeaway', 'Cafe takeaway', 'cafe-takeaway', 'kafe', 'cafes'],
            ['Brand F&B startup', 'F&B startup brands', 'brand-fnb-startup', 'startup F&B', 'F&B startups'],
        ];

        foreach ($verticals as [$label, $labelEn, $slugPart, $keyword, $keywordEn]) {
            $title = "Besek Bambu sebagai Kemasan {$label}";
            $titleEn = "Bamboo Baskets as Packaging for {$labelEn}";
            $posts[] = self::entry(
                title: $title,
                slug: "besek-kemasan-{$slugPart}",
                excerpt: "Mengapa {$keyword} memilih besek anyaman bambu: diferensiasi brand, food-safe, dan pengalaman unboxing yang lebih premium.",
                metaTitle: "{$title} | Kemasan Ramah Lingkungan",
                metaDescription: "Besek bambu untuk {$keyword}: tips ukuran, display toko, margin kemasan, dan pesanan grosir untuk UMKM.",
                body: self::body(
                    "Di segmen {$label}, kemasan adalah bagian dari produk. Besek bambu membantu cerita brand terasa lebih otentik dan dekat dengan bahan alami.",
                    [
                        self::section('Keuntungan untuk bisnis', [
                            'Meningkatkan perceived value tanpa mendominasi harga produk utama.',
                            'Foto produk lebih menarik di Instagram dan marketplace.',
                        ], [
                            'Pelanggan bisa reuse besek setelah produk habis.',
                            'Mendukung narasi ramah lingkungan di media sosial.',
                            'Cocok untuk gift set dan bundling premium.',
                        ]),
                        self::section('Mulai dari volume kecil', [
                            'Uji respon pasar dengan SKU terbatas, lalu naik ke tier grosir saat reorder stabil.',
                        ]),
                    ],
                    'Jelajahi <a href="/shop">produk besek</a> atau minta penawaran di <a href="/grosir">halaman grosir</a>.',
                ),
                titleEn: $titleEn,
                excerptEn: "Why {$keywordEn} choose woven bamboo baskets: brand differentiation, food-safe use, and a more premium unboxing experience.",
                metaTitleEn: "{$titleEn} | Eco Packaging",
                metaDescriptionEn: "Bamboo baskets for {$keywordEn}: sizing tips, shelf display, packaging margin, and wholesale for MSMEs.",
                bodyEn: self::body(
                    "In the {$labelEn} segment, packaging is part of the product. Bamboo baskets help your brand story feel more authentic and close to natural materials.",
                    [
                        self::section('Business benefits', [
                            'Raises perceived value without dominating core product pricing.',
                            'Product photos perform better on Instagram and marketplaces.',
                        ], [
                            'Customers can reuse baskets after the product is gone.',
                            'Supports eco-friendly messaging on social media.',
                            'Ideal for gift sets and premium bundles.',
                        ]),
                        self::section('Start with small volume', [
                            'Test market response with limited SKUs, then move to wholesale tiers as reorders stabilise.',
                        ]),
                    ],
                    'Explore our <a href="/shop">basket products</a> or request a quote on the <a href="/grosir">wholesale page</a>.',
                ),
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $guides = [
            ['Packing aman untuk pengiriman jarak jauh', 'Safe packing for long-distance shipping', 'packing-pengiriman-jauh', 'pengiriman', 'shipping'],
            ['Memilih besek food-safe untuk makanan', 'Choosing food-safe baskets for food', 'besek-food-safe', 'food-safe', 'food-safe packaging'],
            ['Kombinasi besek, pita, dan kartu ucapan', 'Combining baskets, ribbon & greeting cards', 'kombinasi-pita-kartu', 'dekorasi', 'decoration'],
            ['Foto flat lay besek untuk media sosial', 'Flat lay basket photos for social media', 'foto-flat-lay-besek', 'fotografi produk', 'product photography'],
            ['Display besek di rak toko oleh-oleh', 'Displaying baskets on souvenir shop shelves', 'display-rak-toko', 'display toko', 'shop display'],
            ['Menghitung margin setelah pakai kemasan besek', 'Calculating margin with basket packaging', 'hitung-margin-kemasan', 'margin bisnis', 'business margin'],
            ['Branding stiker logo di besek bambu', 'Logo sticker branding on bamboo baskets', 'branding-stiker-logo', 'branding', 'branding'],
            ['Besek bambu vs anyaman rotan', 'Bamboo basket vs rattan weave', 'besek-vs-rotan', 'perbandingan rotan', 'rattan comparison'],
            ['Besek bambu vs kardus premium', 'Bamboo basket vs premium cardboard', 'besek-vs-kardus', 'perbandingan kardus', 'cardboard comparison'],
            ['Timeline pesan besek untuk acara besar', 'Basket order timeline for large events', 'timeline-pesan-acara', 'timeline produksi', 'production timeline'],
            ['Checklist seserahan pernikahan adat', 'Traditional wedding seserahan checklist', 'checklist-seserahan-adat', 'checklist seserahan', 'seserahan checklist'],
            ['Tips negosiasi harga grosir besek', 'Tips for negotiating wholesale basket prices', 'tips-nego-grosir', 'nego grosir', 'wholesale negotiation'],
            ['MOQ dan lead time pesanan custom', 'MOQ and lead time for custom orders', 'moq-lead-time-custom', 'MOQ custom', 'custom MOQ'],
            ['Tren kemasan berkelanjutan 2026', 'Sustainable packaging trends 2026', 'tren-kemasan-berkelanjutan', 'packaging trend', 'packaging trends'],
            ['Cara menyimpan stok besek di gudang toko', 'How to store basket stock in shop warehouses', 'simpan-stok-besek', 'penyimpanan stok', 'stock storage'],
        ];

        foreach ($guides as [$title, $titleEn, $slugPart, $keyword, $keywordEn]) {
            $posts[] = self::entry(
                title: $title,
                slug: "panduan-{$slugPart}",
                excerpt: "Panduan praktis seputar {$keyword} untuk penjual dan penyelenggara acara yang memakai besek bambu handmade.",
                metaTitle: "{$title} — Tips Besek Bambu",
                metaDescription: "Tips besek bambu: {$keyword}. Panduan untuk UMKM, wedding planner, dan toko oleh-oleh di Indonesia.",
                body: self::body(
                    "Artikel ini merangkum praktik terbaik seputar {$keyword} ketika besek bambu dipakai sebagai kemasan utama atau pelengkap hadiah.",
                    [
                        self::section('Poin penting', [
                            'Rencanakan jumlah dan ukuran jauh hari agar produksi pengrajin tidak terburu-buru.',
                            'Komunikasikan tema warna acara atau brand agar finishing seragam.',
                        ], [
                            'Simpan contoh referensi foto agar tim packing memahami standar tampilan.',
                            'Uji satu sampel sebelum produksi massal untuk custom logo atau ukuran.',
                        ]),
                        self::section('Langkah berikutnya', [
                            'Setelah konsep jelas, pesan melalui katalog atau konsultasi grosir untuk penawaran volume.',
                        ]),
                    ],
                    'Butuh bantuan? <a href="/contact">Hubungi kami</a> atau baca <a href="/faq">FAQ</a>.',
                ),
                titleEn: $titleEn,
                excerptEn: "Practical guide on {$keywordEn} for sellers and event organisers using handmade bamboo baskets.",
                metaTitleEn: "{$titleEn} — Bamboo Basket Tips",
                metaDescriptionEn: "Bamboo basket tips: {$keywordEn}. Guide for MSMEs, wedding planners, and souvenir shops in Indonesia.",
                bodyEn: self::body(
                    "This article summarises best practices for {$keywordEn} when bamboo baskets are used as primary or complementary gift packaging.",
                    [
                        self::section('Key points', [
                            'Plan quantity and sizes early so artisan production is not rushed.',
                            'Share event colour themes or brand guidelines for uniform finishing.',
                        ], [
                            'Keep photo references so packing teams understand the visual standard.',
                            'Test one sample before mass production for custom logos or sizes.',
                        ]),
                        self::section('Next steps', [
                            'Once the concept is clear, order via the catalog or wholesale consultation for volume pricing.',
                        ]),
                    ],
                    'Need help? <a href="/contact">Contact us</a> or read our <a href="/faq">FAQ</a>.',
                ),
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $pairings = [
            ['Nastar & kue kering Lebaran', 'nastar-kue-kering', 'nastar'],
            ['Brownies & cake slice', 'brownies-cake-slice', 'brownies'],
            ['Keripik & camilan goreng', 'keripik-camilan-goreng', 'keripik'],
            ['Produk kopi bubuk & beans', 'kopi-bubuk-beans', 'kopi bubuk'],
            ['Paket teh celup premium', 'teh-celup-premium', 'teh celup'],
        ];

        foreach ($pairings as [$label, $slugPart, $keyword]) {
            $title = "Besek Bambu untuk Kemasan {$label}";
            $titleEn = "Bamboo Baskets for {$label} Packaging";
            $posts[] = self::entry(
                title: $title,
                slug: "besek-kemasan-{$slugPart}",
                excerpt: "Rekomendasi ukuran besek anyaman untuk {$keyword}: tampilan rak toko, food-safe, dan tips packing agar produk tetap renyah.",
                metaTitle: "{$title} — Ukuran & Tips Packing",
                metaDescription: "Besek bambu untuk {$keyword}: ukuran 12×12–20×20, lapisan food-grade, dan pesanan grosir untuk UMKM kuliner.",
                body: self::body(
                    "{$label} sering dijual sebagai gift set atau parcel. Besek bambu memberi nuansa artisan yang cocok dengan produk homemade maupun brand premium.",
                    [
                        self::section('Ukuran yang umum dipakai', [
                            'Porsi kecil 2–4 pcs: 12×12. Mix isian sedang: 15×15. Gift set lebih besar: 16×16 atau 20×20.',
                        ]),
                        self::section('Tips menjaga kualitas produk', [
                            'Gunakan inner wrap food-grade agar minyak atau kelembapan tidak merusak anyaman.',
                            'Untuk produk renyah, hindari menumpuk terlalu berat di dalam satu besek.',
                        ]),
                    ],
                    'Lihat <a href="/shop">katalog besek</a> atau pesan volume di <a href="/grosir">halaman grosir</a>.',
                ),
                titleEn: $titleEn,
                excerptEn: "Recommended woven basket sizes for {$keyword}: shelf display, food-safe liners, and packing tips to keep products crisp.",
                metaTitleEn: "{$titleEn} — Sizes & Packing Tips",
                metaDescriptionEn: "Bamboo baskets for {$keyword}: sizes 12×12–20×20, food-grade liners, and wholesale for culinary MSMEs.",
                bodyEn: self::body(
                    "{$label} is often sold as gift sets or parcels. Bamboo baskets add an artisan feel suited to homemade and premium brands alike.",
                    [
                        self::section('Commonly used sizes', [
                            'Small portions 2–4 pcs: 12×12. Medium mix: 15×15. Larger gift sets: 16×16 or 20×20.',
                        ]),
                        self::section('Keeping product quality', [
                            'Use food-grade inner wrap so oil or moisture does not damage the weave.',
                            'For crispy products, avoid stacking too heavily in one basket.',
                        ]),
                    ],
                    'See our <a href="/shop">basket catalog</a> or order volume on the <a href="/grosir">wholesale page</a>.',
                ),
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $regions = [
            ['Banjarnegara & Jawa Tengah', 'Banjarnegara & Central Java', 'banjarnegara-jateng', 'pengrajin lokal', 'local artisans'],
            ['Yogyakarta & oleh-oleh khas', 'Yogyakarta specialty souvenirs', 'yogyakarta-oleh-oleh', 'oleh-oleh Jogja', 'Jogja souvenirs'],
            ['Bali & gift set wisatawan', 'Bali tourist gift sets', 'bali-gift-set', 'souvenir Bali', 'Bali souvenirs'],
            ['Jakarta & corporate parcel', 'Jakarta corporate parcels', 'jakarta-corporate-parcel', 'parcel korporat', 'corporate parcels'],
            ['Bandung & pastry gift box', 'Bandung pastry gift boxes', 'bandung-pastry-gift', 'pastry Bandung', 'Bandung pastry'],
        ];

        foreach ($regions as [$label, $labelEn, $slugPart, $keyword, $keywordEn]) {
            $title = "Besek Bambu untuk Pasar {$label}";
            $titleEn = "Bamboo Baskets for the {$labelEn} Market";
            $posts[] = self::entry(
                title: $title,
                slug: "besek-pasar-{$slugPart}",
                excerpt: "Insight pasar {$keyword}: mengapa besek anyaman bambu diminati dan bagaimana toko lokal memakainya untuk diferensiasi.",
                metaTitle: "{$title} — Insight UMKM & Acara",
                metaDescription: "Besek bambu untuk {$keyword}. Tips ukuran, display, dan pesanan grosir untuk toko oleh-oleh dan event di Indonesia.",
                body: self::body(
                    "Di segmen {$label}, pelanggan mencari kemasan yang terasa autentik. Besek bambu handmade mendukung cerita {$keyword} tanpa terlihat generik.",
                    [
                        self::section('Peluang bisnis', [
                            'Kombinasikan besek dengan produk khas daerah untuk paket hadiah yang mudah difoto dan dibagikan.',
                            'Reorder rutin membantu menjaga konsistensi tampilan di seluruh cabang atau reseller.',
                        ]),
                    ],
                    'Konsultasi kebutuhan wilayah Anda lewat <a href="/contact">kontak</a> atau <a href="/grosir">grosir</a>.',
                ),
                titleEn: $titleEn,
                excerptEn: "Market insight for {$keywordEn}: why woven bamboo baskets are in demand and how local shops use them to differentiate.",
                metaTitleEn: "{$titleEn} — MSME & Event Insight",
                metaDescriptionEn: "Bamboo baskets for {$keywordEn}. Sizing, display, and wholesale tips for souvenir shops and events in Indonesia.",
                bodyEn: self::body(
                    "In the {$labelEn} segment, customers want packaging that feels authentic. Handmade bamboo baskets support the {$keywordEn} story without looking generic.",
                    [
                        self::section('Business opportunities', [
                            'Pair baskets with regional products for photogenic, shareable gift packs.',
                            'Regular reorders keep presentation consistent across branches or resellers.',
                        ]),
                    ],
                    'Consult your regional needs via <a href="/contact">contact</a> or <a href="/grosir">wholesale</a>.',
                ),
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $longTail = [
            ['Harga besek bambu grosir per lusin', 'Wholesale bamboo basket price per dozen', 'harga-grosir-per-lusin', 'harga grosir', 'wholesale pricing'],
            ['Besek bambu biodegradable untuk acara hijau', 'Biodegradable bamboo baskets for green events', 'biodegradable-acara-hijau', 'acara hijau', 'green events'],
            ['Cara memilih besek untuk wedding planner', 'How wedding planners choose baskets', 'tips-wedding-planner', 'wedding planner', 'wedding planners'],
            ['Besek mini 7×7 untuk goodie bag anak', 'Mini 7×7 baskets for kids goodie bags', 'mini-7x7-goodie-bag', 'goodie bag', 'goodie bags'],
            ['Perbedaan anyaman bambu dan pandan', 'Bamboo weave vs pandan weave', 'anyaman-bambu-vs-pandan', 'bambu vs pandan', 'bamboo vs pandan'],
        ];

        foreach ($longTail as [$title, $titleEn, $slugPart, $keyword, $keywordEn]) {
            $posts[] = self::entry(
                title: $title,
                slug: "besek-{$slugPart}",
                excerpt: "Jawaban praktis seputar {$keyword} saat mempertimbangkan besek anyaman bambu untuk bisnis atau acara.",
                metaTitle: "{$title} | Besek Anyaman",
                metaDescription: "Informasi besek bambu: {$keyword}. Tips pemilihan ukuran, pesanan grosir, dan kemasan ramah lingkungan.",
                body: self::body(
                    "Topik {$keyword} sering ditanyakan pelanggan baru. Berikut ringkasan yang bisa membantu Anda memutuskan sebelum memesan.",
                    [
                        self::section('Yang perlu dipertimbangkan', [
                            'Volume pesanan, timeline acara, dan apakah perlu custom branding.',
                            'Jenis isian — kering vs basah — menentukan ukuran dan lapisan dalam besek.',
                        ]),
                    ],
                    'Mulai dari <a href="/shop">katalog</a> atau minta penawaran di <a href="/grosir">grosir & custom</a>.',
                ),
                titleEn: $titleEn,
                excerptEn: "Practical answers about {$keywordEn} when considering woven bamboo baskets for business or events.",
                metaTitleEn: "{$titleEn} | Woven Baskets",
                metaDescriptionEn: "Bamboo basket info: {$keywordEn}. Sizing tips, wholesale orders, and eco-friendly packaging.",
                bodyEn: self::body(
                    "The topic of {$keywordEn} comes up often from new customers. Here is a summary to help you decide before ordering.",
                    [
                        self::section('What to consider', [
                            'Order volume, event timeline, and whether custom branding is needed.',
                            'Fill type — dry vs moist — determines basket size and inner liners.',
                        ]),
                    ],
                    'Start from our <a href="/shop">catalog</a> or request a quote on <a href="/grosir">wholesale & custom</a>.',
                ),
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        return $posts;
    }

    /**
     * @param  list<string>  $introParagraphs
     * @param  list<array{heading: string, paragraphs?: list<string>, list?: list<string>}>  $sections
     */
    private static function body(string $intro, array $sections, string $cta): string
    {
        $html = '<p>'.$intro.'</p>';

        foreach ($sections as $section) {
            $html .= '<h2>'.$section['heading'].'</h2>';
            foreach ($section['paragraphs'] ?? [] as $paragraph) {
                $html .= '<p>'.$paragraph.'</p>';
            }
            if (! empty($section['list'])) {
                $html .= '<ul>';
                foreach ($section['list'] as $item) {
                    $html .= '<li>'.$item.'</li>';
                }
                $html .= '</ul>';
            }
        }

        $html .= '<p>'.$cta.'</p>';

        return $html;
    }

    /**
     * @param  list<string>  $paragraphs
     * @param  list<string>  $list
     * @return array{heading: string, paragraphs: list<string>, list: list<string>}
     */
    private static function section(string $heading, array $paragraphs, array $list = []): array
    {
        return [
            'heading' => $heading,
            'paragraphs' => $paragraphs,
            'list' => $list,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function entry(
        string $title,
        string $slug,
        string $excerpt,
        string $metaTitle,
        string $metaDescription,
        string $body,
        string $titleEn,
        string $excerptEn,
        string $metaTitleEn,
        string $metaDescriptionEn,
        string $bodyEn,
        int $sortOrder,
        int $daysAgo,
    ): array {
        return [
            'title' => $title,
            'title_en' => $titleEn,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'excerpt_en' => $excerptEn,
            'meta_title' => $metaTitle,
            'meta_title_en' => $metaTitleEn,
            'meta_description' => $metaDescription,
            'meta_description_en' => $metaDescriptionEn,
            'author_name' => 'Tim Besek Bambu',
            'sort_order' => $sortOrder,
            'days_ago' => $daysAgo,
            'body' => $body,
            'body_en' => $bodyEn,
        ];
    }
}

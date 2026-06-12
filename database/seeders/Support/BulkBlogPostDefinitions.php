<?php

namespace Database\Seeders\Support;

/**
 * Generates 90 additional SEO-focused blog post definitions (sort_order 11–100).
 */
class BulkBlogPostDefinitions
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        $posts = [];
        $sort = 11;
        $daysAgo = 28;

        $sizes = [
            ['label' => '7×7', 'slug' => '7x7', 'detail' => 'souvenir mini dan token tamu'],
            ['label' => '12×12', 'slug' => '12x12', 'detail' => 'parsel ringan dan hampers compact'],
            ['label' => '15×15', 'slug' => '15x15', 'detail' => 'hantaran standar dan gift set UMKM'],
            ['label' => '16×16', 'slug' => '16x16', 'detail' => 'isian sedikit lebih lapang dengan dekor pita'],
            ['label' => '20×20', 'slug' => '20x20', 'detail' => 'hampers premium dan parcel korporat'],
        ];

        $useCases = [
            ['label' => 'hantaran pernikahan', 'slug' => 'hantaran-pernikahan', 'keyword' => 'hantaran'],
            ['label' => 'hampers hadiah', 'slug' => 'hampers-hadiah', 'keyword' => 'hampers'],
            ['label' => 'seserahan adat', 'slug' => 'seserahan-adat', 'keyword' => 'seserahan'],
            ['label' => 'souvenir acara', 'slug' => 'souvenir-acara', 'keyword' => 'souvenir'],
            ['label' => 'kemasan UMKM', 'slug' => 'kemasan-umkm', 'keyword' => 'kemasan'],
            ['label' => 'parcel oleh-oleh', 'slug' => 'parcel-oleh-oleh', 'keyword' => 'oleh-oleh'],
        ];

        foreach ($sizes as $size) {
            foreach ($useCases as $use) {
                $title = "Besek Bambu {$size['label']} untuk {$use['label']}";
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
                        'Lihat <a href="/shop">katalog besek</a> atau <a href="/grosir">pesan grosir</a> untuk acara dan bisnis Anda.',
                    ),
                    sortOrder: $sort++,
                    daysAgo: $daysAgo++,
                );
            }
        }

        $occasions = [
            ['Lamaran & tunangan', 'lamaran-tunangan', 'lamaran'],
            ['Akad nikah', 'akad-nikah', 'akad'],
            ['Tasyakuran & syukuran', 'tasyakuran', 'syukuran'],
            ['Aqiqah & khitanan', 'aqiqah-khitanan', 'aqiqah'],
            ['Ulang tahun & anniversary', 'ulang-tahun', 'ulang tahun'],
            ['Wisuda & kelulusan', 'wisuda', 'wisuda'],
            ['Grand opening toko', 'grand-opening', 'grand opening'],
            ['Halal bihalal kantor', 'halal-bihalal', 'halal bihalal'],
            ['Parcel Natal & tahun baru', 'natal-tahun-baru', 'Natal'],
            ['Imlek & Gong Xi', 'imlek', 'Imlek'],
            ['Hadiah Hari Ibu & Ayah', 'hadiah-orang-tua', 'hadiah orang tua'],
            ['Corporate appreciation day', 'corporate-appreciation', 'apresiasi karyawan'],
            ['Gathering komunitas', 'gathering-komunitas', 'gathering'],
            ['Peluncuran produk UMKM', 'peluncuran-produk', 'product launch'],
            ['Bazar & pop-up market', 'bazar-popup', 'bazar'],
        ];

        foreach ($occasions as [$label, $slugPart, $keyword]) {
            $title = "Ide Besek Bambu untuk {$label}";
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
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $verticals = [
            ['Bakery & roti artisan', 'bakery-roti', 'toko roti'],
            ['Kue kering & cookies', 'kue-kering', 'kue kering'],
            ['Kopi & specialty coffee', 'kopi-specialty', 'kopi'],
            ['Teh herbal & infus', 'teh-herbal', 'teh'],
            ['Keripik & camilan', 'keripik-camilan', 'keripik'],
            ['Sambal & bumbu khas', 'sambal-bumbu', 'sambal'],
            ['Madu & produk lebah', 'madu-propolis', 'madu'],
            ['Dodol & jenang tradisional', 'dodol-jenang', 'dodol'],
            ['Cokelat & confectionery', 'cokelat-confectionery', 'cokelat'],
            ['Granola & healthy snack', 'granola-snack', 'granola'],
            ['Skincare & spa lokal', 'skincare-spa', 'skincare'],
            ['Kerajinan tangan', 'kerajinan-tangan', 'kerajinan'],
            ['Hotel & guest amenity', 'hotel-amenity', 'hotel'],
            ['Cafe takeaway', 'cafe-takeaway', 'kafe'],
            ['Brand F&B startup', 'brand-fnb-startup', 'startup F&B'],
        ];

        foreach ($verticals as [$label, $slugPart, $keyword]) {
            $title = "Besek Bambu sebagai Kemasan {$label}";
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
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $guides = [
            ['Packing aman untuk pengiriman jarak jauh', 'packing-pengiriman-jauh', 'pengiriman'],
            ['Memilih besek food-safe untuk makanan', 'besek-food-safe', 'food-safe'],
            ['Kombinasi besek, pita, dan kartu ucapan', 'kombinasi-pita-kartu', 'dekorasi'],
            ['Foto flat lay besek untuk media sosial', 'foto-flat-lay-besek', 'fotografi produk'],
            ['Display besek di rak toko oleh-oleh', 'display-rak-toko', 'display toko'],
            ['Menghitung margin setelah pakai kemasan besek', 'hitung-margin-kemasan', 'margin bisnis'],
            ['Branding stiker logo di besek bambu', 'branding-stiker-logo', 'branding'],
            ['Besek bambu vs anyaman rotan', 'besek-vs-rotan', 'perbandingan rotan'],
            ['Besek bambu vs kardus premium', 'besek-vs-kardus', 'perbandingan kardus'],
            ['Timeline pesan besek untuk acara besar', 'timeline-pesan-acara', 'timeline produksi'],
            ['Checklist seserahan pernikahan adat', 'checklist-seserahan-adat', 'checklist seserahan'],
            ['Tips negosiasi harga grosir besek', 'tips-nego-grosir', 'nego grosir'],
            ['MOQ dan lead time pesanan custom', 'moq-lead-time-custom', 'MOQ custom'],
            ['Tren kemasan berkelanjutan 2026', 'tren-kemasan-berkelanjutan', 'packaging trend'],
            ['Cara menyimpan stok besek di gudang toko', 'simpan-stok-besek', 'penyimpanan stok'],
        ];

        foreach ($guides as [$title, $slugPart, $keyword]) {
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
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $regions = [
            ['Banjarnegara & Jawa Tengah', 'banjarnegara-jateng', 'pengrajin lokal'],
            ['Yogyakarta & oleh-oleh khas', 'yogyakarta-oleh-oleh', 'oleh-oleh Jogja'],
            ['Bali & gift set wisatawan', 'bali-gift-set', 'souvenir Bali'],
            ['Jakarta & corporate parcel', 'jakarta-corporate-parcel', 'parcel korporat'],
            ['Bandung & pastry gift box', 'bandung-pastry-gift', 'pastry Bandung'],
        ];

        foreach ($regions as [$label, $slugPart, $keyword]) {
            $title = "Besek Bambu untuk Pasar {$label}";
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
                sortOrder: $sort++,
                daysAgo: $daysAgo++,
            );
        }

        $longTail = [
            ['Harga besek bambu grosir per lusin', 'harga-grosir-per-lusin', 'harga grosir'],
            ['Besek bambu biodegradable untuk acara hijau', 'biodegradable-acara-hijau', 'acara hijau'],
            ['Cara memilih besek untuk wedding planner', 'tips-wedding-planner', 'wedding planner'],
            ['Besek mini 7×7 untuk goodie bag anak', 'mini-7x7-goodie-bag', 'goodie bag'],
            ['Perbedaan anyaman bambu dan pandan', 'anyaman-bambu-vs-pandan', 'bambu vs pandan'],
        ];

        foreach ($longTail as [$title, $slugPart, $keyword]) {
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
        int $sortOrder,
        int $daysAgo,
    ): array {
        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'author_name' => 'Tim Besek Bambu',
            'sort_order' => $sortOrder,
            'days_ago' => $daysAgo,
            'body' => $body,
        ];
    }
}

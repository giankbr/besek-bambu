<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'title' => 'Panduan Ukuran Besek untuk Seserahan Pernikahan',
                'slug' => 'panduan-ukuran-besek-seserahan-pernikahan',
                'excerpt' => 'Bingung memilih ukuran besek untuk lamaran atau seserahan? Panduan praktis 7×7 hingga 20×20 beserta rekomendasi isian.',
                'meta_title' => 'Panduan Ukuran Besek Seserahan Pernikahan — 7×7 hingga 20×20',
                'meta_description' => 'Panduan lengkap memilih ukuran besek bambu untuk seserahan & hantaran pernikahan. Rekomendasi 12×12, 15×15, 20×20 beserta tips packing.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 1,
                'body' => <<<'HTML'
<p>Seserahan pernikahan adalah momen yang penuh makna — dan besek bambu menjadi wadah favorit karena kesan alami, hangat, dan ramah lingkungan. Namun, memilih ukuran yang tepat sering jadi pertanyaan pertama calon pengantin.</p>

<h2>Ukuran populer untuk seserahan</h2>
<ul>
<li><strong>12×12 cm</strong> — Ideal untuk snack, kue kering, atau isian kecil seperti madu dan kurma. Pas untuk seserahan minimalis.</li>
<li><strong>15×15 cm</strong> — Ukuran paling sering dipakai untuk hantaran sepasang. Muat untuk makanan ringan, buah, atau pernak-pernik.</li>
<li><strong>16×16 cm</strong> — Sedikit lebih lapang, cocok bila ingin menambah lapisan kain atau pita hias di dalam besek.</li>
<li><strong>20×20 cm</strong> — Untuk hantaran utama atau isian yang lebih besar, seperti parsel makanan atau set hadiah lengkap.</li>
</ul>

<h2>Tips memilih ukuran</h2>
<p>Pertimbangkan jumlah item yang akan dimasukkan, apakah besek dipakai berpasangan (sepasang), dan estetika keseluruhan seserahan. Besek yang terlalu penuh terlihat sesak; yang terlalu longgar bisa terasa kurang premium.</p>

<p>Untuk acara besar atau pesanan banyak pasangan, pertimbangkan <a href="/grosir">pesanan grosir</a> agar ukuran dan finishing seragam.</p>
HTML,
            ],
            [
                'title' => '7 Ide Isi Besek Hantaran yang Estetik dan Berkesan',
                'slug' => 'ide-isi-besek-hantaran-estetik',
                'excerpt' => 'Dari parsel makanan hingga pernak-pernik tradisional — ide isian besek hantaran yang fotogenik dan bermakna.',
                'meta_title' => '7 Ide Isi Besek Hantaran Estetik — Hampers & Seserahan',
                'meta_description' => 'Inspirasi isi besek hantaran: parsel makanan, kurma, madu, kue kering, dan dekorasi pita. Tips packing agar tampil rapi dan Instagram-worthy.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 2,
                'body' => <<<'HTML'
<p>Besek bambu bukan sekadar wadah. Ia menjadi bagian dari cerita yang Anda sampaikan. Berikut ide isian yang sering dipilih pelanggan kami untuk hantaran, hampers, dan seserahan.</p>

<h2>Ide isian favorit</h2>
<ol>
<li><strong>Parsel kue & kue kering:</strong> Klasik dan aman. Pilih warna kue yang kontras dengan anyaman bambu natural.</li>
<li><strong>Madu & kurma:</strong> Pas untuk hantaran Lebaran atau lamaran dengan nuansa hangat.</li>
<li><strong>Buah potong segar:</strong> Untuk acara yang segera disajikan; pastikan besek tidak terlalu lama terbuka.</li>
<li><strong>Produk UMKM lokal:</strong> Sambal, keripik, atau camilan khas daerah, sekaligus promosi brand kecil.</li>
<li><strong>Tea set mini atau mug:</strong> Kombinasi besek dan barang keramik untuk kesan premium.</li>
<li><strong>Souvenir pernikahan:</strong> Taruh kartu ucapan, lilin kecil, atau token kenangan tamu.</li>
<li><strong>Nasi kotak & lauk:</strong> Untuk syukuran atau tasyakuran; besek 16×16 atau 20×20 biasanya paling pas.</li>
</ol>

<h2>Tips packing</h2>
<p>Gunakan lapisan kain, kertas nasi, atau tissue food-grade di dasar besek. Tambahkan pita dan kartu ucapan agar tampilan lebih rapi. Foto dari atas (flat lay) biasanya paling menonjolkan anyaman bambu.</p>
HTML,
            ],
            [
                'title' => 'Besek Bambu vs Kemasan Plastik: Mengapa UMKM Beralih',
                'slug' => 'besek-bambu-vs-kemasan-plastik-umkm',
                'excerpt' => 'Kemasan ramah lingkungan bukan tren sesaat. Pelajari mengapa UMKM F&B dan brand lokal memilih besek anyaman bambu.',
                'meta_title' => 'Besek Bambu vs Plastik — Kemasan Ramah Lingkungan untuk UMKM',
                'meta_description' => 'Perbandingan besek bambu dan kemasan plastik untuk UMKM: estetika, food-safe, biodegradable, dan citra brand yang lebih premium.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 3,
                'body' => <<<'HTML'
<p>Semakin banyak UMKM kuliner dan brand lokal yang mengganti kotak plastik sekali pakai dengan kemasan anyaman bambu. Alasannya bukan hanya soal lingkungan — tapi juga soal bagaimana pelanggan <em>melihat</em> produk Anda.</p>

<h2>Estetika & diferensiasi</h2>
<p>Plastik transparan fungsional, tapi tidak berkesan. Besek bambu memberi kesan artisan, premium, dan otentik — cocok untuk produk yang ingin dikenal sebagai "buatan tangan" atau "khas Indonesia".</p>

<h2>Ramah lingkungan & food-safe</h2>
<p>Bambu alami mudah terurai dan tidak meninggalkan mikroplastik. Anyaman kami tanpa bahan kimia berlebih, aman untuk kontak makanan bila digunakan dengan lapisan food-grade yang tepat.</p>

<h2>Biaya vs nilai persepsi</h2>
<p>Harga per unit besek mungkin sedikit di atas plastik, tetapi banyak pelanggan bersedia membayar lebih untuk pengalaman unboxing yang lebih baik. Bagi bisnis hampers dan gift set, besek sering menjadi bagian dari nilai jual produk.</p>

<h2>Mulai dari volume kecil</h2>
<p>Tidak perlu langsung pesan ribuan. Coba mulai dari katalog eceran kami, lalu naik ke <a href="/grosir">pesanan grosir</a> saat permintaan stabil. Kami juga melayani custom logo untuk brand yang ingin konsistensi visual.</p>
HTML,
            ],
            [
                'title' => 'Cara Merawat Besek Bambu agar Awet dan Tidak Berjamur',
                'slug' => 'cara-merawat-besek-bambu-awet',
                'excerpt' => 'Tips sederhana menyimpan, membersihkan, dan merawat besek anyaman bambu setelah dipakai untuk hantaran atau kemasan makanan.',
                'meta_title' => 'Cara Merawat Besek Bambu — Tips Awet & Anti Jamur',
                'meta_description' => 'Panduan merawat besek bambu handmade: cara bersihkan, keringkan, simpan, dan hindari jamur agar anyaman tetap rapi untuk dipakai ulang.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 4,
                'days_ago' => 6,
                'body' => <<<'HTML'
<p>Besek bambu tahan dipakai berulang bila dirawat dengan benar. Anyaman alami memang lebih sensitif daripada plastik, tetapi dengan perawatan sederhana besek bisa bertahan lama dan tetap terlihat rapi.</p>

<h2>Setelah dipakai untuk makanan</h2>
<p>Bersihkan sisa kotoran dengan kain lembab, lalu lap kering. Jangan direndam terlalu lama dalam air. Jika ada noda, gunakan sabun cuci piring encer dan bilas cepat.</p>

<h2>Mengeringkan dengan benar</h2>
<p>Keringkan di tempat teduh dan berventilasi — hindari sinar matahari langsung berjam-jam karena bisa membuat anyaman kering dan rapuh. Pastikan benar-benar kering sebelum disimpan.</p>

<h2>Penyimpanan</h2>
<ul>
<li>Simpan di tempat kering, tidak lembap.</li>
<li>Jangan ditumpuk saat masih sedikit lembab.</li>
<li>Bisa diletakkan di rak terbuka, bukan plastik tertutup rapat.</li>
</ul>

<h2>Jika muncul noda atau jamur</h2>
<p>Lap dengan cuka encer atau air hangat + sabun, keringkan menyeluruh. Besek yang sudah dipakai untuk kontak makanan sebaiknya tidak dipakai lagi untuk makanan jika sudah berjamur dalam.</p>

<p>Butuh besek pengganti untuk acara berikutnya? Lihat <a href="/shop">katalog ukuran</a> kami atau <a href="/grosir">pesan grosir</a> untuk stok rutin.</p>
HTML,
            ],
            [
                'title' => 'Besek Bambu untuk Hampers Lebaran: Ide Parcel yang Elegan',
                'slug' => 'besek-bambu-hampers-lebaran',
                'excerpt' => 'Inspirasi hampers Lebaran pakai besek anyaman: kombinasi kue, kurma, dan oleh-oleh khas yang tampil premium tanpa kemasan plastik.',
                'meta_title' => 'Besek Bambu untuk Hampers Lebaran — Ide Parcel Elegan',
                'meta_description' => 'Ide hampers Lebaran dengan besek bambu handmade. Tips isian parcel, ukuran rekomendasi, dan pesanan grosir untuk UMKM & toko oleh-oleh.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 5,
                'days_ago' => 9,
                'body' => <<<'HTML'
<p>Lebaran adalah musim parcel tertinggi di Indonesia. Besek bambu memberi kesan hangat dan tradisional yang sulit ditiru kotak karton generik — sekaligus ramah lingkungan.</p>

<h2>Kombinasi isian favorit musim Lebaran</h2>
<ul>
<li>Kue kering & nastar dalam besek 12×12 atau 15×15</li>
<li>Kurma premium + kartu ucapan</li>
<li>Paket kopi atau teh lokal</li>
<li>Produk UMKM khas daerah (keripik, sambal, dodol)</li>
</ul>

<h2>Ukuran yang sering dipilih</h2>
<p><strong>15×15</strong> untuk parcel standar keluarga. <strong>20×20</strong> untuk hampers korporat atau klien penting. Untuk giveaway masal, <strong>12×12</strong> lebih ekonomis.</p>

<h2>Pesan grosir sebelum ramai</h2>
<p>Permintaan naik drastis menjelang Lebaran. Pesan minimal 25 pcs lebih awal agar produksi dan pengiriman sempat. Lihat halaman <a href="/grosir">Grosir & Custom</a> untuk konsultasi volume.</p>
HTML,
            ],
            [
                'title' => 'Memilih Besek untuk Toko Oleh-oleh dan UMKM Kuliner',
                'slug' => 'besek-untuk-toko-oleh-oleh-umkm',
                'excerpt' => 'Panduan praktis memilih ukuran besek bambu sebagai kemasan produk oleh-oleh, camilan, dan makanan ringan untuk dijual di toko.',
                'meta_title' => 'Besek untuk Toko Oleh-oleh & UMKM — Panduan Kemasan',
                'meta_description' => 'Cara memilih besek bambu untuk toko oleh-oleh dan UMKM kuliner: ukuran, food-safe, harga grosir, dan tips tampilan rak yang menarik.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 6,
                'days_ago' => 12,
                'body' => <<<'HTML'
<p>Banyak toko oleh-oleh di Jawa dan Bali beralih ke kemasan anyaman karena pelanggan wisatawan mencari "sesuatu yang autentik". Besek bambu cocok untuk keripik, kue, sambal, hingga produk kering.</p>

<h2>Pertimbangan sebelum memilih ukuran</h2>
<ul>
<li>Berat & volume produk per porsi jual</li>
<li>Apakah produk perlu lapisan food-grade di dalam besek</li>
<li>Margin jual setelah biaya kemasan</li>
<li>Konsistensi tampilan di rak toko</li>
</ul>

<h2>Tips display di toko</h2>
<p>Susun besek dengan label harga yang jelas. Anyaman natural kontras bagus dengan produk berwarna cerah. Foto untuk media sosial toko — flat lay besek + produk — sering meningkatkan engagement.</p>

<h2>Harga grosir untuk stok rutin</h2>
<p>UMKM yang reorder bulanan biasanya mulai dari tier volume di halaman produk. Untuk kebutuhan 25+ pcs per model, hubungi kami lewat <a href="/grosir">halaman grosir</a>.</p>
HTML,
            ],
            [
                'title' => 'Custom Logo di Besek Bambu: Proses, MOQ, dan Estimasi Waktu',
                'slug' => 'custom-logo-besek-bambu-proses-moq',
                'excerpt' => 'Ingin besek bambu dengan branding logo toko atau acara? Pelajari opsi custom, minimal pesanan, dan timeline produksi.',
                'meta_title' => 'Custom Logo di Besek Bambu — Proses & Minimal Pesanan',
                'meta_description' => 'Panduan pesanan besek bambu custom logo: opsi branding stiker/pita, minimal order, estimasi produksi, dan tips brief untuk pengrajin.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 7,
                'days_ago' => 15,
                'body' => <<<'HTML'
<p>Branding di kemasan anyaman membuat produk Anda lebih mudah diingat. Kami melayani custom untuk brand F&B, wedding planner, dan corporate gifting.</p>

<h2>Opsi branding yang umum</h2>
<ul>
<li><strong>Stiker logo</strong> — Paling cepat dan fleksibel untuk volume menengah</li>
<li><strong>Pita satin custom</strong> — Cocok untuk hampers premium</li>
<li><strong>Kartu ucapan / hang tag</strong> — Menyertakan nama brand & pesan</li>
<li><strong>Ukuran di luar katalog</strong> — Brief khusus untuk acara besar</li>
</ul>

<h2>Minimal pesanan & timeline</h2>
<p>Pesanan grosir custom umumnya mulai 25 pcs per model. Produksi 2–7 hari tergantung ukuran dan jumlah, ditambah waktu pengiriman. Konsultasi gratis sebelum produksi dimulai.</p>

<p>Kirim brief Anda lewat <a href="/contact?subject=Pertanyaan%20grosir%20%2F%20custom%20besek%20bambu">form kontak</a> atau baca detail di <a href="/grosir">Grosir & Custom</a>.</p>
HTML,
            ],
            [
                'title' => 'Besek Bambu sebagai Kemasan Kue dan Pastry: Apa yang Perlu Diperhatikan',
                'slug' => 'besek-kemasan-kue-pastry-food-safe',
                'excerpt' => 'Bakeri dan pastry shop pakai besek anyaman untuk tampilan premium. Simak tips food-safe dan ukuran yang pas untuk berbagai jenis kue.',
                'meta_title' => 'Besek Bambu untuk Kemasan Kue & Pastry — Tips Food-Safe',
                'meta_description' => 'Tips menggunakan besek bambu sebagai kemasan kue, pastry, dan bakery: food-safe, ukuran 12×12–20×20, dan perawatan untuk produk basah vs kering.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 8,
                'days_ago' => 18,
                'body' => <<<'HTML'
<p>Kue kering, brownies, dan pastry kering sangat populer dipakai dalam besek bambu. Tampilannya rustic-premium dan cocok untuk gift set bakery rumahan maupun brand established.</p>

<h2>Produk yang cocok</h2>
<ul>
<li>Kue kering, cookies, dan nastar</li>
<li>Brownies & cake slice yang dibungkus individual</li>
<li>Donat mini atau pastry kering</li>
<li>Paket tea time (kue + teh)</li>
</ul>

<h2>Produk yang perlu ekstra perhatian</h2>
<p>Kue basah atau berkrim sebaiknya dibungkus plastik food-grade dulu di dalam besek, agar kelembapan tidak merusak anyaman. Besek tetap berfungsi sebagai lapisan presentasi luar.</p>

<h2>Ukuran rekomendasi bakery</h2>
<p><strong>12×12</strong> untuk 3–5 pcs cookies. <strong>15×15</strong> untuk mix pastry sedang. <strong>16×16</strong> atau <strong>20×20</strong> untuk gift box lebih besar.</p>
HTML,
            ],
            [
                'title' => 'Souvenir Pernikahan dari Besek Bambu: Ukuran dan Budget',
                'slug' => 'souvenir-pernikahan-besek-bambu',
                'excerpt' => 'Besek mini sebagai souvenir tamu pernikahan: pilihan ukuran 7×7 dan 12×12, ide isian, dan tips pesan dalam jumlah banyak.',
                'meta_title' => 'Souvenir Pernikahan Besek Bambu — Ukuran & Budget',
                'meta_description' => 'Ide souvenir pernikahan dari besek bambu handmade: ukuran 7×7 & 12×12, isian murah meriah, pesanan grosir untuk ratusan tamu.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 9,
                'days_ago' => 21,
                'body' => <<<'HTML'
<p>Souvenir besek bambu diminati pasangan yang ingin nuansa natural dan tidak boros plastik. Ukuran kecil cukup untuk token terima kasih kepada tamu.</p>

<h2>Ukuran favorit souvenir</h2>
<ul>
<li><strong>7×7 cm</strong> — Mini gift: permen, kartu ucapan, atau lilin kecil</li>
<li><strong>12×12 cm</strong> — Sedikit lebih lapang untuk kue kering 2–3 pcs atau produk UMKM</li>
</ul>

<h2>Menekan budget tanpa mengorbankan estetika</h2>
<p>Pilih isian sederhana tapi presentasi rapi: pita matching warna tema pernikahan, kartu nama pasangan, dan tissue food-grade. Anyaman bambu natural sudah cukup menonjol tanpa dekor berlebihan.</p>

<h2>Pesan untuk 100+ tamu</h2>
<p>Pesanan souvenir skala besar sebaiknya dipesan 3–4 minggu sebelum hari H. Lihat <a href="/grosir">opsi grosir</a> dan konsultasikan jumlah final setelah RSVP.</p>
HTML,
            ],
            [
                'title' => '5 Alasan Besek Anyaman Bambu Cocok untuk Corporate Gifting',
                'slug' => 'besek-bambu-corporate-gifting',
                'excerpt' => 'Hadiah korporat ramah lingkungan makin diminati. Mengapa besek bambu jadi pilihan HR dan marketing untuk parcel karyawan & klien.',
                'meta_title' => 'Besek Bambu untuk Corporate Gifting — 5 Alasan',
                'meta_description' => 'Alasan besek bambu cocok untuk corporate gifting & hampers perusahaan: citra ESG, estetika premium, custom logo, dan produk lokal Indonesia.',
                'author_name' => 'Tim Besek Bambu',
                'sort_order' => 10,
                'days_ago' => 24,
                'body' => <<<'HTML'
<p>Perusahaan semakin memperhatikan dampak lingkungan dari hadiah korporat. Besek anyaman bambu menawarkan keseimbangan antara profesional, berkelanjutan, dan otentik.</p>

<h2>1. Mendukung narasi ESG</h2>
<p>Kemasan biodegradable mengurangi jejak plastik sekali pakai — mudah dikomunikasikan di laporan atau internal comms.</p>

<h2>2. Tampilan premium</h2>
<p>Anyaman tangan memberi kesan curated, tidak terlihat seperti merchandise massal.</p>

<h2>3. Mendukung produk lokal</h2>
<p>Diproduksi pengrajin di Indonesia — selaras dengan program UMKM dan beli produk dalam negeri.</p>

<h2>4. Fleksibel untuk custom</h2>
<p>Logo perusahaan, pita warna brand, dan ukuran sesuai budget per karyawan.</p>

<h2>5. Cocok untuk berbagai isian</h2>
<p>Dari snack box hingga produk wellness. Paket dengan <a href="/shop">produk pilihan</a> atau isian dari partner Anda sendiri.</p>

<p>Untuk penawaran volume perusahaan, hubungi tim kami via <a href="/contact">kontak</a>.</p>
HTML,
            ],
        ];

        foreach ($posts as $row) {
            $daysAgo = $row['days_ago'] ?? max(1, 28 - ($row['sort_order'] * 2));
            unset($row['days_ago']);

            BlogPost::updateOrCreate(
                ['slug' => $row['slug']],
                $row + [
                    'is_published' => true,
                    'published_at' => now()->subDays($daysAgo),
                ],
            );
        }
    }
}

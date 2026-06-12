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
        ];

        foreach ($posts as $row) {
            BlogPost::updateOrCreate(
                ['slug' => $row['slug']],
                $row + [
                    'is_published' => true,
                    'published_at' => now()->subDays(3 - $row['sort_order']),
                ],
            );
        }
    }
}

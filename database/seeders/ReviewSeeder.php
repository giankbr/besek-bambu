<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['quote' => 'Anyamannya rapi dan kuat. Pas untuk hampers dan seserahan.', 'author_name' => 'Mira Aldine', 'author_role' => 'Pembeli rumahan', 'is_featured' => false],
            ['quote' => 'Pesan untuk acara kantor, semua tamu suka. Wangi bambu alami, tidak ada bahan kimia.', 'author_name' => 'Jane Cooper', 'author_role' => 'Event organizer', 'is_featured' => false],
            ['quote' => 'Pengiriman cepat, besek sampai dalam kondisi baik. Akan pesan lagi untuk Lebaran.', 'author_name' => 'Darlene Robertson', 'author_role' => 'Pemilik UMKM', 'is_featured' => true],
            ['quote' => 'Ukuran pas untuk packing kue kering. Harga grosirnya juga masuk akal.', 'author_name' => 'Jacob Jones', 'author_role' => 'Pemilik toko oleh-oleh', 'is_featured' => false],
            ['quote' => 'Sudah beberapa kali repeat order. Kualitas anyaman konsisten.', 'author_name' => 'Esther Howard', 'author_role' => 'Pelanggan tetap', 'is_featured' => false],
        ];

        foreach ($rows as $i => $row) {
            Review::create($row + ['sort_order' => $i]);
        }
    }
}

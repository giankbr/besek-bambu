<?php

return [
    /*
    | Menampilkan wishlist di UI dan mendaftar route wishlist.
    | Set FEATURE_WISHLIST=true untuk mengaktifkan kembali.
    */
    'wishlist' => env('FEATURE_WISHLIST', false),

    /*
    | Field waktu produksi (hari) di form produk admin & pill di halaman produk.
    | Set FEATURE_PRODUCTION_LEAD_DAYS=true untuk menampilkan kembali.
    */
    'production_lead_days' => env('FEATURE_PRODUCTION_LEAD_DAYS', false),
];

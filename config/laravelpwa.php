<?php

return [
    'name' => 'Perspective News',
    'manifest' => [
        'name' => env('APP_NAME', 'Perspective'),
        'short_name' => 'Perspective',
        'start_url' => '/',
        'background_color' => '#F8FAFC', // Warna background splash screen (sesuai background SVG)
        'theme_color' => '#1E293B',      // Warna status bar HP (sesuai elemen gelap SVG)
        'display' => 'standalone',
        'orientation'=> 'any',
        'status_bar'=> 'black',
        'icons' => [
            // Ikon Utama untuk Splash Screen & Install
            '512x512' => [
                'path' => '/images/icons/icon-512x512.png', // Pastikan file ini ada!
                'purpose' => 'any maskable'
            ],
            // Ikon untuk Home Screen Android
            '192x192' => [
                'path' => '/images/icons/icon-192x192.png', // Jika malas buat yg 192, pakai yg 512 juga bisa (tapi kurang optimal)
                'purpose' => 'any maskable'
            ],
        ],
        // Pintasan saat tekan lama icon aplikasi
        'shortcuts' => [
            [
                'name' => 'Berita Terbaru',
                'description' => 'Cek berita terkini',
                'url' => '/',
                'icons' => [
                    [
                        'src' => '/images/icons/icon-512x512.png',
                        'purpose' => 'any'
                    ]
                ]
            ],
        ],
        'custom' => []
    ]
];
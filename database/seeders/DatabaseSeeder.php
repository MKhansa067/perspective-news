<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@news.com', // Email untuk login
            'password' => Hash::make('password'), // Password: password
            'role' => 'admin', // Role Admin
            'email_verified_at' => now(),
        ]);

        // 2. Buat Akun User Biasa (untuk tes)
        User::create([
            'name' => 'Pembaca Setia',
            'email' => 'user@news.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // 3. Buat Kategori Berita (Wajib ada agar bisa posting)
        $categories = ['Teknologi', 'Olahraga', 'Politik', 'Hiburan', 'Otomotif'];
        
        foreach ($categories as $cat) {
            Category::create([
                'name' => $cat,
                'slug' => \Illuminate\Support\Str::slug($cat),
            ]);
        }
    }
}
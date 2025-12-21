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
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@perspective.com', // Email untuk login
            'password' => 'password123', // Password: password
            'role' => 'admin', // Role Admin
            'email_verified_at' => now(),
        ]);

        // 2. Buat Akun User Biasa (untuk tes)
        User::factory()->create([
            'name' => 'Pembaca Setia',
            'email' => 'user@perspective.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        // 3. Buat Kategori Berita (Wajib ada agar bisa posting)
        $categories = ['Teknologi', 'Olahraga', 'Politik', 'Hiburan', 'Otomotif'];
        
        foreach ($categories as $cat) {
            Category::firstOrCreate([
                'name' => $cat,
                'slug' => \Illuminate\Support\Str::slug($cat),
            ]);
        }
    }
}
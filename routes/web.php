<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PostController as PublicPostController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan; // <--- PENTING: Import Artisan

/*
|--------------------------------------------------------------------------
| Public Routes (Bisa diakses siapa saja)
|--------------------------------------------------------------------------
*/

// Halaman Depan
Route::get('/', [HomeController::class, 'index'])->name('home');

// Baca Berita (Detail)
Route::get('/read/{post:slug}', [PublicPostController::class, 'show'])->name('posts.show');

// Search (Form action mengarah ke sini)
Route::get('/search', [HomeController::class, 'search'])->name('search');


/*
|--------------------------------------------------------------------------
| Auth Routes (Socialite - Google Login)
|--------------------------------------------------------------------------
*/

// Redirect ke Google
Route::get('/auth/google', function () {
    return Socialite::driver('google')->redirect();
})->name('google.login');

// Callback dari Google
Route::get('/auth/google/callback', function () {
    $googleUser = Socialite::driver('google')->user();
 
    // Cari user berdasarkan google_id atau email
    $user = User::updateOrCreate([
        'email' => $googleUser->getEmail(), // Key pencarian
    ], [
        'name' => $googleUser->getName(),
        'google_id' => $googleUser->getId(),
        'password' => null, // Login google tidak butuh password
        'role' => 'user', // Default role
    ]);
 
    Auth::login($user);
 
    return redirect()->route('home');
});

// Route bawaan Breeze (Login/Register/Logout standar)
require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| Admin Routes (Protected by Auth & IsAdmin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard Admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Berita (CRUD)
    Route::resource('posts', AdminPostController::class);

    // Manajemen User (Hanya list dan delete simpel)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});


/*
|--------------------------------------------------------------------------
| EMERGENCY ROUTE: FORCE MIGRATION & SEED
|--------------------------------------------------------------------------
| Gunakan ini karena database di Railway kosong (0 tabel).
| Ini akan membuat tabel users, posts, dll secara paksa.
*/

Route::get('/setup-database-now', function () {
    try {
        // 1. Jalankan Migrate Fresh (Hapus semua tabel & buat ulang)
        // --force wajib ada agar bisa jalan di production
        // --seed wajib ada agar Admin langsung dibuat
        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true 
        ]);
        
        $output = Artisan::output();
        
        return "<h1>✅ DATABASE BERHASIL DIBANGUN ULANG!</h1>" .
               "<p>Tabel 'users' dan lain-lain sudah dibuat.</p>" .
               "<pre style='background: #eee; padding: 10px;'>$output</pre>" . 
               "<br><br>" .
               "<h3>Admin Login Info:</h3>" .
               "<ul>" .
               "<li>Email: <b>admin@perspective.com</b></li>" .
               "<li>Password: <b>password123</b></li>" .
               "</ul>" .
               "<a href='/login' style='font-size:20px; font-weight:bold; background-color: yellow; padding: 10px;'>-> KLIK SINI UNTUK LOGIN <-</a>";
               
    } catch (\Exception $e) {
        return "<h1 style='color:red'>❌ ERROR SAAT MIGRASI</h1>" .
               "<p>Pastikan variable DB_CONNECTION=mysql sudah diset di Railway.</p>" .
               "<p>Error detail: " . $e->getMessage() . "</p>";
    }
});
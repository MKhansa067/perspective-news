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
| DIAGNOSTIC & FIX TOOL (JALUR PENYELAMATAN)
|--------------------------------------------------------------------------
| Akses route ini untuk melihat isi database dan memperbaiki admin
*/

Route::get('/fix-admin-now', function () {
    // 1. Cek isi tabel users
    try {
        $allUsers = User::all();
        $dbName = DB::connection()->getDatabaseName();
        $dbHost = config('database.connections.mysql.host');
    } catch (\Exception $e) {
        return "<h1>KONEKSI DATABASE GAGAL!</h1><p>" . $e->getMessage() . "</p>";
    }
    
    $output = "<h1>Diagnosa Database</h1>";
    $output .= "<p>Database: <b>$dbName</b> | Host: <b>$dbHost</b></p>";
    $output .= "<hr>";
    
    if ($allUsers->isEmpty()) {
        $output .= "<h3 style='color:red'>⚠️ TABEL USERS KOSONG! (Aplikasi Web melihat database kosong)</h3>";
    } else {
        $output .= "<h3 style='color:blue'>ℹ️ Ditemukan " . $allUsers->count() . " user di database ini:</h3><ul>";
        foreach($allUsers as $u) {
            $output .= "<li>Email: <b>{$u->email}</b> | Role: <b>{$u->role}</b> | ID: {$u->id}</li>";
        }
        $output .= "</ul>";
    }
    $output .= "<hr>";

    // 2. EKSEKUSI PERBAIKAN
    $adminEmail = 'admin@perspective.com';
    $fixedPassword = 'password123';
    
    $admin = User::where('email', $adminEmail)->first();

    if ($admin) {
        // Jika ada, paksa update password
        $admin->password = $fixedPassword; // Laravel 11 otomatis hash via casting
        $admin->save();
        $output .= "<h2>✅ UPDATE: User Admin ditemukan & Password di-reset!</h2>";
    } else {
        // Jika tidak ada, paksa buat baru
        try {
            User::create([
                'name' => 'Super Admin',
                'email' => $adminEmail,
                'password' => $fixedPassword,
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
            $output .= "<h2>✅ CREATE: User Admin BARU berhasil dibuat!</h2>";
        } catch (\Exception $e) {
            $output .= "<h2 style='color:red'>❌ ERROR CREATE: " . $e->getMessage() . "</h2>";
        }
    }

    $output .= "<p>Silakan coba login sekarang dengan:</p>";
    $output .= "<ul><li>Email: <b>$adminEmail</b></li><li>Password: <b>$fixedPassword</b></li></ul>";
    $output .= "<br><a href='/login' style='font-size:20px; font-weight:bold; background-color: yellow; padding: 10px;'>-> KLIK SINI UNTUK LOGIN <-</a>";

    return $output;
});
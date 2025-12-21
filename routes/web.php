<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PostController as PublicPostController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
| SECRET ROUTE: REGISTER ADMIN MANUAL
|--------------------------------------------------------------------------
| Akses route ini untuk mendaftar sebagai Admin secara resmi lewat Web.
| Karena lewat web, enkripsi password dijamin 100% cocok.
*/

// 1. Tampilkan Form Register Admin
Route::get('/secret-admin-register', function () {
    return '
    <div style="display:flex; justify-content:center; align-items:center; height:100vh; font-family:sans-serif;">
        <form action="/secret-admin-register" method="POST" style="border:1px solid #ccc; padding:20px; border-radius:8px; width:300px;">
            <input type="hidden" name="_token" value="'.csrf_token().'">
            <h2 style="text-align:center;">Daftar Super Admin</h2>
            
            <label>Nama Lengkap</label><br>
            <input type="text" name="name" required style="width:100%; margin-bottom:10px; padding:5px;">
            
            <label>Email Admin</label><br>
            <input type="email" name="email" required style="width:100%; margin-bottom:10px; padding:5px;">
            
            <label>Password</label><br>
            <input type="password" name="password" required style="width:100%; margin-bottom:20px; padding:5px;">
            
            <button type="submit" style="width:100%; padding:10px; background:blue; color:white; border:none; cursor:pointer;">DAFTAR & LOGIN</button>
        </form>
    </div>
    ';
});

// 2. Proses Data Register Admin
Route::post('/secret-admin-register', function (Request $request) {
    // Validasi sederhana
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:4'
    ]);

    // Buat User Baru dengan Role ADMIN
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => $request->password, // Laravel otomatis hash via Model Casting
        'role' => 'admin', // <--- INI KUNCINYA
        'email_verified_at' => now(),
    ]);

    // Langsung Login Otomatis
    Auth::login($user);

    // Redirect ke Dashboard
    return redirect()->route('admin.dashboard');
});
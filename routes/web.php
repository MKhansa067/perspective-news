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
| EMERGENCY ROUTE: FORCE RESET PASSWORD
|--------------------------------------------------------------------------
| Gunakan ini untuk memperbaiki login admin di Production (Railway)
| HAPUS route ini setelah berhasil login!
*/

Route::get('/force-reset-password', function () {
    // 1. Cari User Admin
    $user = User::where('email', 'admin@perspective.com')->first();

    if (!$user) {
        // Jika belum ada, buat baru
        $user = new User();
        $user->name = 'Super Admin';
        $user->email = 'admin@perspective.com';
        $user->role = 'admin';
        $user->email_verified_at = now();
    }

    // 2. Set Password Baru (Memastikan Hash sesuai environment server)
    $passwordBaru = 'password123';
    $user->password = Hash::make($passwordBaru);
    $user->save();

    // 3. Pastikan Kategori Ada (Biar web tidak error)
    Category::firstOrCreate(['slug' => 'teknologi'], ['name' => 'Teknologi']);
    Category::firstOrCreate(['slug' => 'bisnis'], ['name' => 'Bisnis']);

    return "BERHASIL! Password untuk <b>{$user->email}</b> telah di-reset menjadi: <b>{$passwordBaru}</b>. <br><br> <a href='/login'>KLIK DISINI UNTUK LOGIN</a>";
});
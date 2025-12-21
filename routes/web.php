<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PostController as PublicPostController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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
        // 'role' tetap default 'user' kecuali diubah manual di database
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
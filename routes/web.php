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
    $user->password = $passwordBaru;
    $user->save();

    // 3. Pastikan Kategori Ada (Biar web tidak error)
    Category::firstOrCreate(['slug' => 'teknologi'], ['name' => 'Teknologi']);
    Category::firstOrCreate(['slug' => 'bisnis'], ['name' => 'Bisnis']);

    return "BERHASIL! Password untuk <b>{$user->email}</b> telah di-reset menjadi: <b>{$passwordBaru}</b>. <br><br> <a href='/login'>KLIK DISINI UNTUK LOGIN</a>";
    });

    Route::get('/debug-db-fix', function () {
        // 1. Cek Koneksi & Database yang dipakai
        $dbName = DB::connection()->getDatabaseName();
        $host = config('database.connections.mysql.host');
        $count = User::count();
        
        // 2. Cek apakah Admin ada?
        $admin = User::where('email', 'admin@perspective.com')->first();
        
        $status = "Database Aktif: <b>$dbName</b> di Host: <b>$host</b><br>";
        $status .= "Jumlah Total User: <b>$count</b><br><br>";

        if ($admin) {
            // Jika ada, kita reset passwordnya biar yakin
            $admin->password = 'password123'; // Karena ada casting 'hashed', ini otomatis di-hash
            $admin->save();
            $status .= "✅ User Admin DITEMUKAN. Password di-reset ulang ke: <b>password123</b>.";
        } else {
            // Jika TIDAK ADA, kita buat paksa sekarang juga
            try {
                User::create([
                    'name' => 'Super Admin',
                    'email' => 'admin@perspective.com',
                    'password' => 'password123', // Otomatis hash karena casting
                    'role' => 'admin',
                    'email_verified_at' => now(),
                ]);
                $status .= "⚠️ User Admin TIDAK DITEMUKAN, tapi barusan BERHASIL DIBUAT SECARA PAKSA.<br>";
                $status .= "Password: <b>password123</b>";
            } catch (\Exception $e) {
                $status .= "❌ GAGAL MEMBUAT USER: " . $e->getMessage();
            }
        }
    
        $status .= "<br><br><a href='/login'>KLIK DISINI UNTUK LOGIN</a>";
        return $status;
    });
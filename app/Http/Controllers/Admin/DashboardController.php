<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengambil statistik
        $totalPosts = Post::count();
        $totalUsers = User::where('role', 'user')->count();
        
        // Mengambil 5 berita terpopuler berdasarkan views
        $popularPosts = Post::orderBy('views', 'desc')->take(5)->get();

        return view('admin.dashboard', compact('totalPosts', 'totalUsers', 'popularPosts'));
    }
}
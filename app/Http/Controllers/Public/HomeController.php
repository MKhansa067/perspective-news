<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Ambil berita terbaru
        $posts = Post::with('category')->latest()->paginate(9);
        return view('welcome', compact('posts'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $posts = Post::where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->latest()
                    ->paginate(9);

        return view('welcome', compact('posts', 'query'));
    }
}

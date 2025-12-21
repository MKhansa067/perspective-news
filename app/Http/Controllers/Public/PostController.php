<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show(Post $post)
    {
        // Fitur Counter: Tambah 1 view setiap kali halaman dibuka
        $post->increment('views');

        return view('posts.show', compact('post'));
    }
}
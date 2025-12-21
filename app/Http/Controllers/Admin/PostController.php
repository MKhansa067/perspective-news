<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    public function index()
    {
        // List berita dengan pagination
        $posts = Post::with('category')->latest()->paginate(10);
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.posts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
            'description' => 'required',
            'content' => 'required',
        ]);

        // Upload Gambar
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        Post::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5), // Slug unik
            'image' => $imagePath,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('admin.posts.index')->with('success', 'Berita berhasil dibuat!');
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'required',
            'content' => 'required',
        ]);

        $data = $request->except('image');
        $data['slug'] = Str::slug($request->title) . '-' . Str::random(5);

        // Cek jika ada gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Berita berhasil diperbarui!');
    }

    public function destroy(Post $post)
    {
        // Hapus gambar dari storage
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        
        $post->delete();
        return redirect()->route('admin.posts.index')->with('success', 'Berita dihapus!');
    }

    public function show(Post $post)
    {
        // 1. Ambil IP Address pengunjung
        $visitorIp = request()->ip();

        // 2. Buat Key Unik: Gabungan ID Berita + IP Pengunjung
        // Contoh: viewed_post_5_192.168.1.1
        $cacheKey = 'viewed_post_' . $post->id . '_' . $visitorIp;

        // 3. Cek apakah Key ini ada di Cache?
        if (!Cache::has($cacheKey)) {
            
            // Jika BELUM ada (berarti dia belum lihat dalam 1 jam terakhir):
            $post->increment('views');

            // Simpan Key di Cache selama 60 menit (3600 detik)
            // Jadi kalau dia refresh dalam 60 menit ke depan, views tidak akan nambah
            Cache::put($cacheKey, true, 60 * 60); 
        }

        return view('posts.show', compact('post'));
    }
}
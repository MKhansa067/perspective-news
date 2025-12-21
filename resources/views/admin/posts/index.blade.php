@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Kelola Berita</h3>
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">Tambah Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Views</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posts as $post)
                <tr>
                    <td>{{ Str::limit($post->title, 50) }}</td>
                    <td>{{ $post->category->name }}</td>
                    <td>{{ $post->views }}</td>
                    <td>
                        <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" onsubmit="return confirm('Hapus?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">{{ $posts->links() }}</div>
</div>
@endsection
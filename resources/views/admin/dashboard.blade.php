@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard Admin</h2>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card text-bg-primary">
                <div class="card-body text-center">
                    <h3>{{ $totalPosts }}</h3>
                    <p>Total Berita</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-bg-success">
                <div class="card-body text-center">
                    <h3>{{ $totalUsers }}</h3>
                    <p>Total User</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">Tulis Berita</a>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">Kelola Berita</a>
    </div>

    <div class="card">
        <div class="card-header">5 Berita Terpopuler</div>
        <ul class="list-group list-group-flush">
            @foreach($popularPosts as $post)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{ $post->title }}
                    <span class="badge bg-primary rounded-pill">{{ $post->views }} Views</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
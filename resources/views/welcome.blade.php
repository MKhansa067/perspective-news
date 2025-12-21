@extends('layouts.app')

@section('content')
<div class="container">
    @if(isset($query))
        <h4 class="mb-4">Hasil pencarian: "{{ $query }}"</h4>
    @else
        <div class="p-5 mb-4 bg-body-secondary rounded-3">
            <h1 class="display-5 fw-bold">Berita Terhangat</h1>
            <p class="col-md-8 fs-4">Baca berita terbaru dan terpercaya hari ini.</p>
        </div>
    @endif

    <div class="row g-4">
        @forelse($posts as $post)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    @if($post->image)
                        <img src="{{ asset('storage/' . $post->image) }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 200px;">No Image</div>
                    @endif
                    <div class="card-body">
                        <small class="text-primary fw-bold">{{ $post->category->name }}</small>
                        <h5 class="card-title mt-2">
                            <a href="{{ route('posts.show', $post->slug) }}" class="text-decoration-none text-reset">{{ $post->title }}</a>
                        </h5>
                        <p class="card-text text-muted">{{ Str::limit($post->description, 100) }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 d-flex justify-content-between text-muted small">
                        <span>{{ $post->created_at->diffForHumans() }}</span>
                        <span><i class="bi bi-eye"></i> {{ $post->views }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <p class="text-muted">Belum ada berita.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $posts->links() }}
    </div>
</div>
@endsection
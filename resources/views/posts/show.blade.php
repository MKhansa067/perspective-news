@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">{{ $post->category->name }}</li>
                </ol>
            </nav>

            <h1 class="fw-bold mb-3">{{ $post->title }}</h1>
            <div class="text-muted mb-4">
                Oleh <strong>{{ $post->user->name }}</strong> &bull; {{ $post->created_at->format('d M Y') }} &bull; <i class="bi bi-eye"></i> {{ $post->views }} views
            </div>

            @if($post->image)
                <img src="{{ asset('storage/' . $post->image) }}" class="img-fluid rounded w-100 mb-4">
            @endif

            <article class="fs-5 lh-lg">
                {!! nl2br(e($post->content)) !!}
            </article>

            <hr class="my-5">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">&larr; Kembali ke Home</a>
        </div>
    </div>
</div>
@endsection
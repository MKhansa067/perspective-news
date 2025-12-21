<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>{{ config('app.name', 'Perspective') }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="icon" href="{{ asset('icon.svg') }}" type="image/svg+xml">
    
    <style>
        body { font-family: 'Georgia', serif; }
        /* Font sistem untuk elemen UI agar terlihat modern */
        h1, h2, h3, h4, h5, h6, .navbar, .btn, .badge, .nav-link { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        
        .navbar-brand { font-weight: bold; letter-spacing: -0.5px; }
        img { max-width: 100%; height: auto; }
        
        /* Perbaikan kecil agar link di dalam card tidak merusak warna judul */
        a.text-reset { text-decoration: none; }
        a.text-reset:hover { text-decoration: underline; color: var(--bs-primary) !important; }
    </style>
    
    <link rel="icon" href="{{ asset('icon.svg') }}" type="image/svg+xml">

    @laravelPWA
</head>
<body>

    <nav class="navbar navbar-expand-lg border-bottom sticky-top bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}" style="letter-spacing: -0.5px; font-size: 1.5rem;">
                <img src="{{ asset('icon.svg') }}" alt="Perspective Logo" width="35" height="35" class="d-inline-block align-text-bottom me-1">
                Perspective
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <form class="d-flex mx-auto my-2 my-lg-0" action="{{ route('search') }}" method="GET">
                    <input class="form-control me-2" type="search" name="q" placeholder="Cari sudut pandang..." value="{{ request('q') }}">
                </form>

                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <button class="btn btn-link nav-link" id="darkModeToggle" title="Ganti Tema">
                            <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
                        </button>
                    </li>

                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if(Auth::user()->role === 'admin')
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Dashboard Admin</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @yield('content')
    </main>

    <footer class="py-4 text-center text-body-secondary border-top mt-5">
        <small>&copy; {{ date('Y') }} Perspective. All rights reserved.</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const toggle = document.getElementById('darkModeToggle');
        const icon = document.getElementById('themeIcon');
        const html = document.documentElement;

        // Cek LocalStorage saat load
        if (localStorage.getItem('theme') === 'dark') {
            html.setAttribute('data-bs-theme', 'dark');
            icon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
        }

        toggle.addEventListener('click', () => {
            if (html.getAttribute('data-bs-theme') === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                icon.classList.replace('bi-sun-fill', 'bi-moon-stars-fill');
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                icon.classList.replace('bi-moon-stars-fill', 'bi-sun-fill');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="{{ $__locale }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog &mdash; {{ $__appName }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:system-ui,sans-serif; }
        .navbar { background:rgba(13,17,23,.95); backdrop-filter:blur(12px); border-bottom:1px solid rgba(255,255,255,.06); }
        .navbar-brand { font-weight:800; background:linear-gradient(135deg,#818cf8,#6366f1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .card { background:rgba(30,41,59,.8); border:1px solid rgba(255,255,255,.07); border-radius:12px; }
        .post-card { text-decoration:none; display:block; transition:all .2s; }
        .post-card:hover .card { border-color:rgba(99,102,241,.3); transform:translateY(-2px); }
        footer { border-top:1px solid rgba(255,255,255,.07); }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/">{{ $__appName }}</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="/">Startseite</a></li>
                <li class="nav-item"><a class="nav-link active" href="/blog">Blog</a></li>
                @if ($__user)
                    <li class="nav-item"><a class="nav-link" href="/mein-konto">{{ $__user['username'] }}</a></li>
                @else
                    <li class="nav-item"><a class="btn btn-primary btn-sm ms-2" href="/login">Anmelden</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <h1 class="h3 fw-bold mb-5">Blog</h1>

    @if ($posts)
    <div class="row g-4">
        @foreach ($posts as $post)
        <div class="col-12 col-md-6 col-lg-4">
            <a href="/blog/{{ $post->getAttribute('slug') }}" class="post-card">
                <div class="card p-4 h-100">
                    <h2 class="h5 fw-bold text-light mb-2">{{ $post->getAttribute('title') }}</h2>
                    <p class="text-muted small mb-3">{{ $post->autoExcerpt(120) }}</p>
                    <div class="mt-auto text-muted" style="font-size:.78rem">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ $post->getAttribute('published_at') ? date('d.m.Y', strtotime($post->getAttribute('published_at'))) : '' }}
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-file-text d-block fs-1 mb-3 opacity-25"></i>
        Noch keine Beitr&auml;ge vorhanden.
    </div>
    @endif
</main>

<footer class="py-4 text-center text-muted small">
    <div class="container">&copy; {{ date('Y') }} {{ $__appName }}</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy" crossorigin="anonymous"></script>
</body>
</html>

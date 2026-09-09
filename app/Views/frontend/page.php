<!DOCTYPE html>
<html lang="{{ $__locale }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} &mdash; {{ $__appName }}</title>
    @if ($page && $page->getAttribute('meta_description'))
    <meta name="description" content="{{ $page->getAttribute('meta_description') }}">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:system-ui,sans-serif; }
        .navbar { background:rgba(13,17,23,.95); backdrop-filter:blur(12px); border-bottom:1px solid rgba(255,255,255,.06); }
        .navbar-brand { font-weight:800; background:linear-gradient(135deg,#818cf8,#6366f1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        footer { border-top:1px solid rgba(255,255,255,.07); }
        .page-content { line-height:1.8; }
        .page-content h1,.page-content h2,.page-content h3 { color:#f1f5f9; margin-top:2rem; }
        .page-content p { color:#cbd5e1; }
        .page-content a { color:#818cf8; }
        .page-content blockquote { border-left:4px solid #6366f1; padding-left:1rem; color:#94a3b8; }
        .page-content code { background:rgba(99,102,241,.15); color:#a5b4fc; padding:.15em .4em; border-radius:4px; }
        .page-content pre { background:rgba(0,0,0,.3); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:1rem; overflow-x:auto; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/">{{ $__appName }}</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="/">Startseite</a></li>
                <li class="nav-item"><a class="nav-link" href="/blog">Blog</a></li>
                @if ($__user)
                    <li class="nav-item"><a class="nav-link" href="/mein-konto">{{ $__user['username'] }}</a></li>
                @else
                    <li class="nav-item"><a class="btn btn-primary btn-sm ms-2" href="/login">Anmelden</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5" style="max-width:800px">

    {{-- Admin-Vorschau-Banner --}}
    @if ($__user && $page && $page->getAttribute('status') !== 'published')
    <div class="alert alert-warning border-0 mb-4 d-flex align-items-center gap-2" style="background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.3)!important">
        <i class="bi bi-eye-fill"></i>
        <strong>Vorschau</strong> &mdash; Diese Seite ist noch nicht ver&ouml;ffentlicht.
        <a href="/admin/seiten/{{ $page->getAttribute('id') }}/bearbeiten" class="ms-auto btn btn-sm btn-outline-warning">
            <i class="bi bi-pencil me-1"></i> Bearbeiten
        </a>
    </div>
    @endif

    <article>
        <h1 class="fw-bold mb-4" style="color:#f1f5f9">{{ $title }}</h1>
        <div class="page-content">
            <?php echo \HuberCMS\Core\BlockRenderer::render($page ? $page->getAttribute('content') : ''); ?>
        </div>
    </article>

</main>

<footer class="py-4 text-center text-muted small">
    <div class="container">&copy; {{ date('Y') }} {{ $__appName }}</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>
</body>
</html>

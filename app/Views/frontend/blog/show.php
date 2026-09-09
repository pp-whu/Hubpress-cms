<!DOCTYPE html>
<html lang="{{ $__locale }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} &mdash; {{ $__appName }}</title>
    @if ($post && $post->getAttribute('meta_description'))
    <meta name="description" content="{{ $post->getAttribute('meta_description') }}">
    @endif
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:system-ui,sans-serif; }
        .navbar { background:rgba(13,17,23,.95); backdrop-filter:blur(12px); border-bottom:1px solid rgba(255,255,255,.06); }
        .navbar-brand { font-weight:800; background:linear-gradient(135deg,#818cf8,#6366f1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        footer { border-top:1px solid rgba(255,255,255,.07); }
        .post-content { line-height:1.9; font-size:1.05rem; }
        .post-content h1,.post-content h2,.post-content h3 { color:#f1f5f9; margin-top:2.5rem; margin-bottom:.75rem; }
        .post-content p { color:#cbd5e1; margin-bottom:1.2rem; }
        .post-content a { color:#818cf8; text-decoration:underline; }
        .post-content blockquote { border-left:4px solid #6366f1; padding:.75rem 1rem; color:#94a3b8; background:rgba(99,102,241,.06); border-radius:0 8px 8px 0; margin:1.5rem 0; }
        .post-content code { background:rgba(99,102,241,.15); color:#a5b4fc; padding:.15em .4em; border-radius:4px; font-size:.9em; }
        .post-content pre { background:rgba(0,0,0,.3); border:1px solid rgba(255,255,255,.1); border-radius:8px; padding:1.25rem; overflow-x:auto; margin:1.5rem 0; }
        .post-content img { max-width:100%; border-radius:8px; margin:1rem 0; }
        .post-meta { font-size:.85rem; }
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

<main class="container py-5" style="max-width:780px">

    {{-- Admin-Vorschau-Banner --}}
    @if ($__user && $post && $post->getAttribute('status') !== 'published')
    <div class="alert border-0 mb-4 d-flex align-items-center gap-2"
         style="background:rgba(245,158,11,.15);color:#fbbf24;border:1px solid rgba(245,158,11,.3)!important">
        <i class="bi bi-eye-fill"></i>
        <strong>Vorschau</strong> &mdash; Dieser Beitrag ist noch nicht ver&ouml;ffentlicht.
        <a href="/admin/beitraege/{{ $post->getAttribute('id') }}/bearbeiten"
           class="ms-auto btn btn-sm btn-outline-warning">
            <i class="bi bi-pencil me-1"></i> Bearbeiten
        </a>
    </div>
    @endif

    {{-- Article Header --}}
    <article>
        <header class="mb-5">
            <div class="mb-3">
                <a href="/blog" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> Zur&uuml;ck zum Blog
                </a>
            </div>
            <h1 class="fw-black mb-3" style="font-size:2.2rem;color:#f1f5f9;line-height:1.2">
                {{ $title }}
            </h1>
            <div class="post-meta text-muted d-flex align-items-center gap-3 flex-wrap">
                @if ($post && $post->getAttribute('published_at'))
                <?php $pubDate = date('d. F Y', strtotime($post->getAttribute('published_at'))); ?>
                <span><i class="bi bi-calendar3 me-1"></i>{{ $pubDate }}</span>
                @endif
                <span><i class="bi bi-clock me-1"></i>
                    <?php
                        $wordCount = str_word_count(strip_tags($post ? $post->getAttribute('content') : ''));
                        echo max(1, round($wordCount / 200));
                    ?> Min. Lesezeit
                </span>
            </div>
        </header>

        <div class="post-content">
            <?php
                $rawContent = $post ? $post->getAttribute('content') : '';
                echo \HuberCMS\Core\BlockRenderer::render($rawContent);
            ?>
        </div>
    </article>

</main>

<footer class="py-4 text-center text-muted small">
    <div class="container">
        <a href="/blog" class="text-muted text-decoration-none me-3">
            <i class="bi bi-arrow-left me-1"></i> Alle Beitr&auml;ge
        </a>
        &copy; {{ date('Y') }} {{ $__appName }}
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>
</body>
</html>

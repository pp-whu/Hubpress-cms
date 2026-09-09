@extends('frontend.layouts.app')

@section('content')
{{-- Hero --}}
<section style="padding:5rem 0 4rem;background:radial-gradient(ellipse at 30% 50%, rgba(99,102,241,.12) 0%, transparent 60%)">
    <div class="container text-center">
        <span class="badge-cat mb-3 d-inline-block">
            <i class="bi bi-stars me-1"></i> Das moderne Open-Source-CMS
        </span>
        <h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:900;line-height:1.1;
                   color:var(--hp-text);margin-bottom:1.25rem">
            Willkommen bei {{ $__appName }}
        </h1>
        <p style="color:var(--hp-muted);font-size:1.15rem;max-width:560px;margin:0 auto 2.5rem">
            Schnell. Sicher. Modern. Ein CMS das mit dir w&auml;chst.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a href="/blog" class="hp-btn hp-btn-primary">
                <i class="bi bi-file-text-fill"></i> Blog entdecken
            </a>
            <a href="/seite/kontakt" class="hp-btn hp-btn-outline">
                <i class="bi bi-envelope"></i> Kontakt aufnehmen
            </a>
        </div>
    </div>
</section>

{{-- Neueste Beiträge --}}
<section>
    <div class="container">
        @if ($posts)
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem">
            <h2 style="font-size:1.5rem;font-weight:800">Neueste Beitr&auml;ge</h2>
            <a href="/blog" class="hp-btn hp-btn-outline" style="font-size:.85rem">
                Alle ansehen <i class="bi bi-arrow-right" style="margin-left:.3rem"></i>
            </a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem">
            @foreach ($posts as $post)
            <?php
                $slug    = $post->getAttribute('slug');
                $title   = $post->getAttribute('title');
                $excerpt = $post->autoExcerpt(130);
                $date    = $post->getAttribute('published_at')
                    ? date('d.m.Y', strtotime($post->getAttribute('published_at')))
                    : date('d.m.Y', strtotime($post->getAttribute('created_at')));
            ?>
            <a href="/blog/{{ $slug }}" style="text-decoration:none">
                <div class="hp-card" style="padding:1.5rem;height:100%;display:flex;flex-direction:column">
                    <span class="badge-cat" style="align-self:flex-start;margin-bottom:.75rem">Beitrag</span>
                    <h3 style="font-size:1.1rem;font-weight:700;color:var(--hp-text);margin-bottom:.75rem;line-height:1.3">
                        {{ $title }}
                    </h3>
                    <p style="color:var(--hp-muted);font-size:.875rem;flex:1;margin-bottom:1rem">{{ $excerpt }}</p>
                    <div style="color:var(--hp-muted);font-size:.78rem">
                        <i class="bi bi-calendar3"></i> {{ $date }}
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div style="text-align:center;padding:4rem 0;color:var(--hp-muted)">
            <i class="bi bi-file-text" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.3"></i>
            <p>Noch keine Beitr&auml;ge ver&ouml;ffentlicht.</p>
        </div>
        @endif
    </div>
</section>
@endsection

<!DOCTYPE html>
<html lang="{{ $__locale }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? $__appName }}</title>
    @if ($metaDescription ?? '')
    <meta name="description" content="{{ $metaDescription }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/themes/hubpress/css/theme.css?v={{ filemtime($_SERVER['DOCUMENT_ROOT'] . '/themes/hubpress/css/theme.css') }}">
    @yield('head')
</head>
<body>

{{-- ════════════════════════════════════════════════
     META BAR (ganz oben)
     ════════════════════════════════════════════════ --}}
<div class="hp-meta-bar">
    <div class="container d-flex align-items-center justify-content-between gap-3">

        {{-- Links: Metabar-Menü (DB) --}}
        <div class="meta-contact d-flex align-items-center gap-4">
            @foreach (($__menus['meta-bar']['items'] ?? []) as $item)
                @if ($item['url'] === '#')
                    <span>
                        @if ($item['icon'])
                        <i class="bi {{ $item['icon'] }}"></i>
                        @endif
                        {{ $item['label'] }}
                    </span>
                @else
                    <a href="{{ $item['url'] }}">
                        @if ($item['icon'])
                        <i class="bi {{ $item['icon'] }}"></i>
                        @endif
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </div>

        {{-- Rechts: Socialbar-Icons (DB) + Theme-Toggle --}}
        <div class="d-flex align-items-center gap-3">
            <div class="meta-social d-flex align-items-center gap-1">
                @foreach (($__menus['social-bar']['items'] ?? []) as $item)
                    <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                       aria-label="{{ $item['label'] }}"
                       target="{{ $item['target'] }}"
                       {!! $item['target'] === '_blank' ? 'rel="noopener"' : '' !!}>
                        <i class="bi {{ $item['icon'] }}"></i>
                    </a>
                @endforeach
            </div>
            <span style="width:1px;height:14px;background:rgba(255,255,255,.15)"></span>
            @if ($__user)
                <a href="/admin" style="font-size:.72rem;color:#94a3b8">
                    <i class="bi bi-speedometer2 me-1"></i>Admin
                </a>
            @else
                <a href="/login" style="font-size:.72rem;color:#94a3b8">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Anmelden
                </a>
            @endif
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════
     HAUPTNAVIGATION
     ════════════════════════════════════════════════ --}}
<nav class="hp-navbar">
    <div class="container">

        {{-- Logo --}}
        <a href="/" class="hp-logo">{{ $__appName }}</a>

        {{-- Desktop-Menü (DB: primary-nav) --}}
        <ul class="hp-nav-links">
            @foreach (($__menus['primary-nav']['items'] ?? []) as $item)
                <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @endforeach
        </ul>

        {{-- Rechte Aktionen --}}
        <div class="hp-nav-actions">
            {{-- Suche --}}
            <form action="/suche" method="GET" class="d-none d-md-flex"
                  style="position:relative">
                <input type="search" name="q" placeholder="Suchen&hellip;"
                       style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
                              color:#e2e8f0;padding:.4rem .8rem;border-radius:8px;
                              font-size:.85rem;outline:none;width:180px">
            </form>

            {{-- Dark/Light Toggle --}}
            <button class="hp-theme-toggle" id="hp-theme-toggle" aria-label="Farbschema wechseln">
                <i class="bi bi-sun-fill" id="hp-theme-icon"></i>
            </button>

            {{-- CTA Button --}}
            <a href="/seite/kontakt" class="hp-btn hp-btn-primary d-none d-lg-inline-flex">
                <i class="bi bi-chat-dots-fill"></i> Kontakt
            </a>

            {{-- Hamburger (Mobile) --}}
            <button class="hp-hamburger" id="hp-hamburger" aria-label="Menü" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    {{-- Mobile-Menü (DB: primary-nav) --}}
    <div class="hp-mobile-nav" id="hp-mobile-nav">
        <div class="container">
            <ul>
                @foreach (($__menus['primary-nav']['items'] ?? []) as $item)
                    <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                @endforeach
                @if ($__user)
                <li><a href="/admin"><i class="bi bi-speedometer2 me-2"></i>Admin-Panel</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>

{{-- ════════════════════════════════════════════════
     SOCIAL BAR (links, sticky)
     ════════════════════════════════════════════════ --}}
<div class="hp-social-bar">
    @foreach (($__menus['social-bar']['items'] ?? []) as $item)
        <a href="{{ $item['url'] }}"
           title="{{ $item['label'] }}"
           target="{{ $item['target'] }}"
           {!! $item['target'] === '_blank' ? 'rel="noopener"' : '' !!}>
            <i class="bi {{ $item['icon'] }}"></i>
            <span class="label">{{ $item['label'] }}</span>
        </a>
    @endforeach
</div>

{{-- ════════════════════════════════════════════════
     MAIN CONTENT
     ════════════════════════════════════════════════ --}}
<main class="hp-main" style="margin-right:40px">
    @yield('content')
</main>

{{-- ════════════════════════════════════════════════
     FOOTER
     ════════════════════════════════════════════════ --}}
<footer class="hp-footer">
    <div class="container">
        <div class="hp-footer-top">
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:3rem">

                {{-- Spalte 1: Logo + Beschreibung + Newsletter --}}
                <div>
                    <span class="hp-footer-logo">{{ $__appName }}</span>
                    <p style="color:#64748b;font-size:.875rem;line-height:1.8;margin-bottom:1.5rem">
                        Dein modernes CMS f&uuml;r professionelle Webprojekte.
                        Schnell, sicher und einfach zu bedienen.
                    </p>
                    <h6>Newsletter</h6>
                    <form class="hp-newsletter-form" onsubmit="return false">
                        <input type="email" placeholder="deine@email.de">
                        <button type="submit">
                            <i class="bi bi-send-fill"></i> Anmelden
                        </button>
                    </form>
                </div>

                {{-- Spalte 2+3: Footer-Menü (DB: footer-nav), aufgeteilt in 2 Hälften --}}
                @php
                    $__footerItems = $__menus['footer-nav']['items'] ?? [];
                    $__half        = (int) ceil(count($__footerItems) / 2);
                    $__col2        = array_slice($__footerItems, 0, $__half);
                    $__col3        = array_slice($__footerItems, $__half);
                @endphp
                <div>
                    <h6>Navigation</h6>
                    <ul>
                        @foreach ($__col2 as $item)
                            <li>
                                <a href="{{ $item['url'] }}"
                                   target="{{ $item['target'] }}"
                                   {!! $item['target']==='_blank' ? 'rel="noopener"' : '' !!}>
                                    <i class="bi bi-chevron-right" style="font-size:.6rem;margin-right:.3rem"></i>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h6>Rechtliches</h6>
                    <ul>
                        @foreach ($__col3 as $item)
                            <li>
                                <a href="{{ $item['url'] }}"
                                   target="{{ $item['target'] }}"
                                   {!! $item['target']==='_blank' ? 'rel="noopener"' : '' !!}>
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Spalte 4: Kontakt + Social --}}
                <div>
                    <h6>Kontakt</h6>
                    <ul style="margin-bottom:1.5rem">
                        <li>
                            <a href="tel:+4989123456">
                                <i class="bi bi-telephone me-2"></i>+49 89 123 456
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@hubpress.de">
                                <i class="bi bi-envelope me-2"></i>info@hubpress.de
                            </a>
                        </li>
                        <li>
                            <span style="color:#64748b;font-size:.875rem">
                                <i class="bi bi-geo-alt me-2"></i>M&uuml;nchen, Deutschland
                            </span>
                        </li>
                    </ul>
                    <h6>Folge uns</h6>
                    <div class="hp-footer-social">
                        @foreach (($__menus['social-bar']['items'] ?? []) as $item)
                            <a href="{{ $item['url'] }}" title="{{ $item['label'] }}"
                               target="{{ $item['target'] }}"
                               {!! $item['target']==='_blank' ? 'rel="noopener"' : '' !!}>
                                <i class="bi {{ $item['icon'] }}"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer-Bottom --}}
        <div class="hp-footer-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
            <p style="color:#475569;font-size:.8rem;margin:0">
                &copy; {{ date('Y') }} <strong>{{ $__appName }}</strong> &mdash;
                Alle Rechte vorbehalten. Powered by
                <a href="https://hubercms.io" style="color:#6366f1">HuberCMS</a>
            </p>
            <div style="display:flex;align-items:center;gap:1.5rem">
                <a href="/seite/datenschutz" style="color:#475569;font-size:.8rem">Datenschutz</a>
                <a href="/seite/impressum"   style="color:#475569;font-size:.8rem">Impressum</a>
                <a href="/seite/agb"         style="color:#475569;font-size:.8rem">AGB</a>
            </div>
        </div>
    </div>
</footer>

{{-- Back-to-top Button --}}
<button id="hp-back-to-top"
        style="position:fixed;bottom:1.5rem;right:1.5rem;width:40px;height:40px;
               background:var(--hp-primary);color:#fff;border:none;border-radius:10px;
               cursor:pointer;opacity:0;transition:opacity .3s;z-index:999;
               display:flex;align-items:center;justify-content:center;font-size:1rem"
        aria-label="Nach oben">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="/themes/hubpress/js/theme.js"></script>
@yield('scripts')

{{-- Plugin-Hook: HTML vor </body> injizieren --}}
{!! isset($__events) ? implode('', array_filter($__events->filter('frontend.body.end', null), 'is_string')) : '' !!}
</body>
</html>

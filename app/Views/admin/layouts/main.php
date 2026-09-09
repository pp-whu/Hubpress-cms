<!DOCTYPE html>
<html lang="{{ $__locale }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ $__csrf }}">

    <title>{{ $title ?? 'Admin' }} — {{ $__appName }} Admin</title>

    {{-- Bootstrap 5 CSS (CDN) --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- HuberCMS Admin CSS --}}
    <link rel="stylesheet" href="/assets/css/admin.css?v={{ filemtime($_SERVER['DOCUMENT_ROOT'] . '/assets/css/admin.css') }}">

    @yield('head')
</head>
<body class="admin-layout">

{{-- ============================================================ --}}
{{-- Sidebar --}}
{{-- ============================================================ --}}
<nav id="sidebar" class="sidebar d-flex flex-column">
    <div class="sidebar-header p-3">
        <a href="/admin" class="sidebar-brand text-decoration-none d-flex align-items-center gap-2">
            <i class="bi bi-layers-fill text-primary fs-4"></i>
            <span class="fw-bold">{{ $__appName }}</span>
        </a>
    </div>

    <div class="sidebar-search px-3 pb-2">
        <div class="input-group input-group-sm">
            <span class="input-group-text border-0 bg-transparent">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" class="form-control form-control-sm bg-transparent border-0 text-white"
                   placeholder="Suchen..." id="adminSearch" autocomplete="off">
        </div>
    </div>

    <ul class="sidebar-nav nav flex-column px-2 flex-grow-1">
        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.dashboard') ? 'active' : '' }}"
               href="/admin">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <li class="sidebar-label px-2 mt-3">Inhalt</li>

        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.posts') ? 'active' : '' }}"
               href="/admin/beitraege">
                <i class="bi bi-file-text"></i> Beitr&auml;ge
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.media') ? 'active' : '' }}"
               href="/admin/medien">
                <i class="bi bi-images"></i> Medien
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.pages') ? 'active' : '' }}"
               href="/admin/seiten">
                <i class="bi bi-file-earmark"></i> Seiten
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.comments') ? 'active' : '' }}"
               href="/admin/kommentare">
                <i class="bi bi-chat-dots"></i> Kommentare
            </a>
        </li>

        {{-- ── Design (klappbar wie WP) ───────────────── --}}
        <li class="nav-item sidebar-has-submenu">
            <a class="nav-link sidebar-parent" href="#" onclick="return false;">
                <span><i class="bi bi-palette"></i> Design</span>
                <i class="bi bi-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li>
                    <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.themes') ? 'active' : '' }}"
                       href="/admin/themes">
                        <i class="bi bi-palette2"></i> Themes
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.customizer') ? 'active' : '' }}"
                       href="/admin/customizer">
                        <i class="bi bi-sliders"></i> Customizer
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.widgets') ? 'active' : '' }}"
                       href="/admin/widgets">
                        <i class="bi bi-grid-1x2"></i> Widgets
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.menus') ? 'active' : '' }}"
                       href="/admin/menues">
                        <i class="bi bi-list-nested"></i> Men&uuml;s
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.theme_editor') ? 'active' : '' }}"
                       href="/admin/theme-editor">
                        <i class="bi bi-code-slash"></i> Theme-Editor
                    </a>
                </li>
            </ul>
        </li>

        {{-- ── Plugins (klappbar wie WP) ─────────────── --}}
        <li class="nav-item sidebar-has-submenu">
            <a class="nav-link sidebar-parent" href="#" onclick="return false;">
                <span><i class="bi bi-puzzle"></i> Plugins</span>
                <i class="bi bi-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li>
                    <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.plugins') ? 'active' : '' }}"
                       href="/admin/plugins">
                        <i class="bi bi-puzzle"></i> Installierte Plugins
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.plugin_editor') ? 'active' : '' }}"
                       href="/admin/plugin-editor">
                        <i class="bi bi-file-code"></i> Plugin-Editor
                    </a>
                </li>
            </ul>
        </li>

        {{-- ── Werkzeuge (klappbar wie WP) ───────────── --}}
        <li class="nav-item sidebar-has-submenu">
            <a class="nav-link sidebar-parent" href="#" onclick="return false;">
                <span><i class="bi bi-tools"></i> Werkzeuge</span>
                <i class="bi bi-chevron-right sidebar-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                <li>
                    <a class="nav-link {{ ($currentRoute ?? '') === 'admin.tools' ? 'active' : '' }}"
                       href="/admin/werkzeuge">
                        <i class="bi bi-wrench"></i> Verf&uuml;gbare Werkzeuge
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ ($currentRoute ?? '') === 'admin.tools.import' ? 'active' : '' }}"
                       href="/admin/werkzeuge/import">
                        <i class="bi bi-box-arrow-in-down"></i> Importieren
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ ($currentRoute ?? '') === 'admin.tools.export' ? 'active' : '' }}"
                       href="/admin/werkzeuge/export">
                        <i class="bi bi-box-arrow-up"></i> Exportieren
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ ($currentRoute ?? '') === 'admin.tools.health' ? 'active' : '' }}"
                       href="/admin/werkzeuge/site-health">
                        <i class="bi bi-heart-pulse"></i> Website-Gesundheit
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ ($currentRoute ?? '') === 'admin.tools.export_data' ? 'active' : '' }}"
                       href="/admin/werkzeuge/export-data">
                        <i class="bi bi-person-lines-fill"></i> Pers. Daten exportieren
                    </a>
                </li>
                <li>
                    <a class="nav-link {{ ($currentRoute ?? '') === 'admin.tools.delete_data' ? 'active' : '' }}"
                       href="/admin/werkzeuge/delete-data">
                        <i class="bi bi-person-x"></i> Pers. Daten l&ouml;schen
                    </a>
                </li>
            </ul>
        </li>

        <li class="sidebar-label px-2 mt-3">System</li>

        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.users') ? 'active' : '' }}"
               href="/admin/benutzer">
                <i class="bi bi-people"></i> Benutzer
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.seo') ? 'active' : '' }}"
               href="/admin/seo">
                <i class="bi bi-graph-up-arrow"></i> SEO
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.backup') ? 'active' : '' }}"
               href="/admin/backup">
                <i class="bi bi-cloud-download"></i> Backup
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.logs') ? 'active' : '' }}"
               href="/admin/logs">
                <i class="bi bi-journal-text"></i> Logs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ str_starts_with($currentRoute ?? '', 'admin.settings') ? 'active' : '' }}"
               href="/admin/einstellungen">
                <i class="bi bi-gear"></i> Einstellungen
            </a>
        </li>
    </ul>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer p-3 border-top border-secondary">
        <div class="d-flex align-items-center gap-2">
            <div class="avatar-sm">
                @if ($__user['avatar'] ?? null)
                    <img src="{{ $__user['avatar'] }}" alt="Avatar" class="rounded-circle" width="32" height="32">
                @else
                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                         style="width:32px;height:32px;background:var(--bs-primary)">
                        <span class="text-white fw-bold small">
                            {{ strtoupper(substr($__user['username'] ?? 'A', 0, 1)) }}
                        </span>
                    </div>
                @endif
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <div class="text-truncate small fw-semibold">{{ $__user['username'] ?? '' }}</div>
                <div class="text-truncate text-muted" style="font-size:.7rem">{{ $__user['role'] ?? '' }}</div>
            </div>
            <form method="POST" action="/logout">
                <input type="hidden" name="_token" value="{{ $__csrf }}">
                <button type="submit" class="btn btn-link btn-sm text-muted p-0" title="Abmelden">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- ============================================================ --}}
{{-- Main Content --}}
{{-- ============================================================ --}}
<div class="main-wrapper">

    {{-- Top bar --}}
    <header class="topbar d-flex align-items-center px-4 gap-3">
        <button id="sidebarToggle" class="btn btn-link text-white p-0 me-2 d-lg-none">
            <i class="bi bi-list fs-5"></i>
        </button>

        <nav aria-label="breadcrumb" class="flex-grow-1">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/admin" class="text-decoration-none">Home</a></li>
                @yield('breadcrumb')
            </ol>
        </nav>

        {{-- Flash messages --}}
        @if ($__flash['success'])
            <div class="toast-notification success">
                <i class="bi bi-check-circle-fill"></i> {{ $__flash['success'] }}
            </div>
        @endif
        @if ($__flash['error'])
            <div class="toast-notification error">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ $__flash['error'] }}
            </div>
        @endif

        <div class="d-flex align-items-center gap-2 ms-auto">
            {{-- View site --}}
            <a href="{{ $__appUrl }}/" target="_blank" rel="noopener"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-eye me-1"></i> Website
            </a>

            {{-- Dark/Light toggle --}}
            <button id="themeToggle" class="btn btn-outline-secondary btn-sm" title="Theme wechseln">
                <i class="bi bi-sun-fill"></i>
            </button>
        </div>
    </header>

    {{-- Page Content --}}
    <main class="content p-4">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="admin-footer px-4 py-2 d-flex justify-content-between text-muted small">
        <span>HuberCMS v{{ $__version }}</span>
        <span>PHP {{ PHP_VERSION }} &bull; {{ round((microtime(true) - START_TIME) * 1000) }}ms</span>
        <span>&copy; Hubpress 2026</span>
    </footer>
</div>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>

{{-- HuberCMS Admin JS --}}
<script src="/assets/js/admin.js"></script>

@yield('scripts')
</body>
</html>

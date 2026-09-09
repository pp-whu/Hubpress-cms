@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Customizer</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Customizer</h1>
        <p class="text-muted small mb-0">Theme-Einstellungen und Live-Vorschau</p>
    </div>
    <a href="/" target="_blank" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-eye me-1"></i> Website ansehen
    </a>
</div>

<div class="row g-4">
    {{-- Einstellungs-Panel --}}
    <div class="col-12 col-lg-4">

        {{-- Site Identity --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold small d-flex align-items-center justify-content-between">
                <span><i class="bi bi-house me-2"></i>Website-Identit&auml;t</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/customizer/save" id="customizerForm">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">

                    <div class="mb-3">
                        <label class="form-label small">Website-Name</label>
                        <input type="text" name="site_name" class="form-control form-control-sm"
                               value="{{ $settings['site_name'] ?? $__appName }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Untertitel</label>
                        <input type="text" name="site_tagline" class="form-control form-control-sm"
                               value="{{ $settings['site_tagline'] ?? '' }}"
                               placeholder="Just another HuberCMS site">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Website-Icon (Favicon URL)</label>
                        <input type="text" name="site_icon" class="form-control form-control-sm"
                               value="{{ $settings['site_icon'] ?? '' }}"
                               placeholder="/assets/images/favicon.ico">
                    </div>
                </form>
            </div>
        </div>

        {{-- Farben --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold small">
                <i class="bi bi-palette me-2"></i>Farben
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small">Prim&auml;rfarbe</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="color" name="color_primary" class="form-control form-control-sm"
                               style="width:48px;padding:2px"
                               value="{{ $settings['color_primary'] ?? '#6366f1' }}"
                               form="customizerForm">
                        <input type="text" class="form-control form-control-sm"
                               value="{{ $settings['color_primary'] ?? '#6366f1' }}"
                               readonly>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label small">Akzentfarbe</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="color" name="color_accent" class="form-control form-control-sm"
                               style="width:48px;padding:2px"
                               value="{{ $settings['color_accent'] ?? '#818cf8' }}"
                               form="customizerForm">
                        <input type="text" class="form-control form-control-sm"
                               value="{{ $settings['color_accent'] ?? '#818cf8' }}"
                               readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Homepage --}}
        <div class="card mb-4">
            <div class="card-header fw-semibold small">
                <i class="bi bi-house me-2"></i>Startseite
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small">Startseiten-Anzeige</label>
                    <select name="homepage_display" class="form-select form-select-sm"
                            form="customizerForm">
                        <option value="posts" {{ ($settings['homepage_display'] ?? 'posts') === 'posts' ? 'selected' : '' }}>
                            Letzte Beitr&auml;ge
                        </option>
                        <option value="page" {{ ($settings['homepage_display'] ?? '') === 'page' ? 'selected' : '' }}>
                            Statische Seite
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" form="customizerForm" class="btn btn-primary w-100">
            <i class="bi bi-floppy me-1"></i> &Auml;nderungen speichern
        </button>
    </div>

    {{-- Vorschau --}}
    <div class="col-12 col-lg-8">
        <div class="card" style="height:600px">
            <div class="card-header d-flex align-items-center gap-2 py-2">
                <div class="d-flex gap-1">
                    <div style="width:12px;height:12px;border-radius:50%;background:#ef4444"></div>
                    <div style="width:12px;height:12px;border-radius:50%;background:#f59e0b"></div>
                    <div style="width:12px;height:12px;border-radius:50%;background:#10b981"></div>
                </div>
                <div class="flex-grow-1 mx-3">
                    <div class="form-control form-control-sm text-muted" style="font-size:.78rem">
                        {{ $__appUrl ?? 'https://cms.ddev.site' }}
                    </div>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary py-0" title="Desktop">
                        <i class="bi bi-display"></i>
                    </button>
                    <button class="btn btn-outline-secondary py-0" title="Tablet">
                        <i class="bi bi-tablet"></i>
                    </button>
                    <button class="btn btn-outline-secondary py-0" title="Mobil">
                        <i class="bi bi-phone"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0" style="overflow:hidden">
                <iframe src="/" style="width:100%;height:100%;border:none;background:#fff"
                        id="customizerPreview" title="Vorschau"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

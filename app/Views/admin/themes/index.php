@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Themes</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Themes</h1>
        <p class="text-muted small mb-0">{{ count($themes) }} installierte Themes</p>
    </div>
    <button class="btn btn-primary btn-sm" disabled title="Kommt bald">
        <i class="bi bi-cloud-download me-1"></i> Theme installieren
    </button>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

{{-- Theme Grid --}}
<div class="row g-4">
@foreach ($themes as $slug => $theme)
<div class="col-12 col-md-6 col-xl-4">
    <div class="card h-100 {{ $theme['active'] ? 'border-primary border-2' : '' }}"
         style="transition: all .2s">
        {{-- Screenshot --}}
        <div style="height:180px;background:linear-gradient(135deg,#1e293b,#0f172a);
                    border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:center;
                    position:relative;overflow:hidden">
            @if ($theme['active'])
            <span class="badge bg-primary position-absolute top-0 start-0 m-2">
                <i class="bi bi-check-circle-fill me-1"></i> Aktiv
            </span>
            @endif
            <i class="bi bi-palette fs-1 text-primary opacity-25"></i>
        </div>

        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-1">
                <h5 class="fw-bold mb-0">{{ $theme['name'] ?? $slug }}</h5>
                <span class="badge bg-secondary bg-opacity-15 text-secondary border border-secondary border-opacity-25 small">
                    v{{ $theme['version'] ?? '1.0' }}
                </span>
            </div>
            <p class="text-muted small mb-3">
                {{ $theme['description'] ?? 'Kein Beschreibungstext vorhanden.' }}
            </p>
            <p class="text-muted" style="font-size:.75rem">
                <i class="bi bi-person me-1"></i>{{ $theme['author'] ?? '—' }}
            </p>
        </div>

        <div class="card-footer d-flex gap-2">
            @if ($theme['active'])
                <a href="/admin/customizer" class="btn btn-outline-primary btn-sm flex-grow-1">
                    <i class="bi bi-sliders me-1"></i> Anpassen
                </a>
                <button class="btn btn-outline-secondary btn-sm" disabled>
                    <i class="bi bi-eye"></i>
                </button>
            @else
                <form method="POST" action="/admin/themes/{{ $slug }}/aktivieren"
                      class="flex-grow-1">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-check-lg me-1"></i> Aktivieren
                    </button>
                </form>
                <a href="/" target="_blank" class="btn btn-outline-secondary btn-sm" title="Vorschau">
                    <i class="bi bi-eye"></i>
                </a>
            @endif
        </div>
    </div>
</div>
@endforeach

@if (!$themes)
<div class="col-12">
    <div class="text-center py-5 text-muted">
        <i class="bi bi-palette d-block fs-1 mb-3 opacity-25"></i>
        Keine Themes gefunden. Lege einen Ordner unter <code>/Themes/</code> an.
    </div>
</div>
@endif
</div>
@endsection

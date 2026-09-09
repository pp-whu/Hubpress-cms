@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Plugins</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Plugins</h1>
        <p class="text-muted small mb-0">{{ count($plugins) }} installierte Plugins</p>
    </div>
    <button class="btn btn-primary btn-sm" disabled title="Kommt bald">
        <i class="bi bi-cloud-download me-1"></i> Plugin installieren
    </button>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

@if ($plugins)
<div class="row g-4">
    @foreach ($plugins as $slug => $plugin)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card h-100 {{ $plugin['active'] ? 'border-success border-opacity-50' : '' }}">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;border-radius:10px;background:rgba(99,102,241,.15);
                                    display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="bi bi-puzzle text-primary fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0">{{ $plugin['name'] ?? $slug }}</h5>
                            <span class="text-muted small">v{{ $plugin['version'] ?? '1.0' }}</span>
                        </div>
                    </div>
                    @if ($plugin['active'])
                    <span class="badge badge-status-active">
                        <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>Aktiv
                    </span>
                    @else
                    <span class="badge badge-status-inactive">
                        Inaktiv
                    </span>
                    @endif
                </div>

                <p class="text-muted small mb-3">
                    {{ $plugin['description'] ?? 'Keine Beschreibung vorhanden.' }}
                </p>
                <p class="text-muted mb-0" style="font-size:.75rem">
                    <i class="bi bi-person me-1"></i>{{ $plugin['author'] ?? '—' }}
                </p>
            </div>
            <div class="card-footer d-flex gap-2">
                @if ($plugin['active'])
                <form method="POST" action="/admin/plugins/{{ $slug }}/deaktivieren" class="flex-grow-1">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">
                    <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                        <i class="bi bi-pause-circle me-1"></i> Deaktivieren
                    </button>
                </form>
                @else
                <form method="POST" action="/admin/plugins/{{ $slug }}/aktivieren" class="flex-grow-1">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">
                    <button type="submit" class="btn btn-success btn-sm w-100">
                        <i class="bi bi-play-circle me-1"></i> Aktivieren
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-puzzle d-block fs-1 mb-3 opacity-25"></i>
        <p class="mb-3">Noch keine Plugins installiert.</p>
        <p class="small">Lege einen Plugin-Ordner unter <code>/Plugins/</code> an.</p>
    </div>
</div>
@endif
@endsection

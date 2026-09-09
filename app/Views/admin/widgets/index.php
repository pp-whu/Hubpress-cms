@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Widgets</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Widgets</h1>
        <p class="text-muted small mb-0">Sidebar- und Footer-Widgets verwalten</p>
    </div>
</div>

<div class="row g-4">

    {{-- Verf&uuml;gbare Widgets --}}
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header fw-semibold small">
                <i class="bi bi-grid-1x2 me-2"></i>Verf&uuml;gbare Widgets
            </div>
            <div class="card-body p-2">
                @foreach ($availableWidgets as $widget)
                <div class="d-flex align-items-center justify-content-between p-2 rounded mb-1"
                     style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
                    <div>
                        <div class="fw-semibold small text-light">{{ $widget['name'] }}</div>
                        <div class="text-muted" style="font-size:.72rem">{{ $widget['description'] }}</div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary py-0 px-2 ms-2"
                            onclick="addWidget('{{ $widget['id'] }}', '{{ $widget['name'] }}')">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Widget-Bereiche (Sidebars) --}}
    <div class="col-12 col-lg-8">
        @foreach ($sidebars as $sidebar)
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-semibold small">
                    <i class="bi bi-layout-sidebar me-2"></i>{{ $sidebar['name'] }}
                </span>
                <small class="text-muted">{{ count($sidebar['widgets']) }} Widgets</small>
            </div>

            {{-- Widget-Liste (Drag & Drop) --}}
            <div class="card-body p-2" id="sidebar-{{ $sidebar['id'] }}"
                 style="min-height:80px">
                @foreach ($sidebar['widgets'] as $w)
                <div class="widget-item d-flex align-items-center gap-2 p-2 rounded mb-1"
                     style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2)"
                     data-widget-id="{{ $w['id'] }}">
                    <i class="bi bi-grip-vertical text-muted" style="cursor:grab;font-size:1.1rem"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small text-light">{{ $w['name'] }}</div>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary py-0 px-2"
                            type="button"
                            onclick="toggleWidgetSettings('{{ $w['id'] }}')"
                            title="Einstellungen">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger py-0 px-2"
                            onclick="removeWidget('{{ $w['id'] }}')"
                            title="Entfernen">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                @endforeach

                @if (!$sidebar['widgets'])
                <div class="text-center py-3 text-muted small border border-secondary border-dashed rounded">
                    <i class="bi bi-arrow-down me-1"></i>Widget hierher ziehen
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
function addWidget(id, name) {
    showToast('info', 'Widget "' + name + '" hinzugef&uuml;gt.');
}
function removeWidget(id) {
    if (confirm('Widget entfernen?')) {
        showToast('success', 'Widget entfernt.');
    }
}
function toggleWidgetSettings(id) {
    showToast('info', 'Widget-Einstellungen kommen bald.');
}
</script>
@endsection

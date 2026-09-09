@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Men&uuml;s</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">Men&uuml;s</h1>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

<div class="row g-4">

    {{-- Men&uuml;-Liste --}}
    <div class="col-12 col-lg-4">

        {{-- Neues Men&uuml; erstellen --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold small">
                <i class="bi bi-plus-circle me-2"></i>Neues Men&uuml;
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/menues">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">
                    <div class="d-flex gap-2">
                        <input type="text"
                               name="name"
                               class="form-control form-control-sm"
                               placeholder="Men&uuml;-Name&hellip;"
                               required
                               maxlength="100">
                        <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                            <i class="bi bi-plus-lg"></i> Erstellen
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Vorhandene Men&uuml;s --}}
        <div class="card">
            <div class="card-header fw-semibold small">Vorhandene Men&uuml;s</div>
            <div class="list-group list-group-flush">
                @foreach ($menus as $menu)
                <div class="list-group-item bg-transparent border-secondary d-flex align-items-center justify-content-between py-2">
                    <div>
                        <div class="fw-semibold small text-light">{{ $menu['name'] }}</div>
                        <div class="text-muted" style="font-size:.72rem">
                            @if ($menu['location'])
                                <span class="badge bg-primary bg-opacity-25 text-primary me-1" style="font-size:.65rem">
                                    <i class="bi bi-pin-map"></i> {{ $menu['location'] }}
                                </span>
                            @endif
                            Slug: <code>{{ $menu['slug'] }}</code>
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                onclick="selectMenu({{ $menu['id'] }}, '{{ $menu['name'] }}', '{{ $menu['location'] ?? '' }}')"
                                title="Bearbeiten">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form method="POST" action="/admin/menues/{{ $menu['id'] }}" class="d-inline">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit"
                                    class="btn btn-sm btn-outline-danger py-0 px-2"
                                    data-confirm="Men&uuml; wirklich l&ouml;schen?"
                                    title="L&ouml;schen">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
                @if (!$menus)
                <div class="list-group-item bg-transparent text-muted small text-center py-4">
                    Noch keine Men&uuml;s vorhanden.
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Men&uuml;-Editor --}}
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span class="fw-semibold small" id="menuEditorTitle">
                    <i class="bi bi-list-nested me-2"></i>Men&uuml; ausw&auml;hlen
                </span>
                {{-- Position (Theme-Location) --}}
                <form id="menuLocationForm" method="POST" style="display:none" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">
                    <input type="hidden" name="name" id="locationMenuName">
                    <select name="location" id="locationSelect" class="form-select form-select-sm" style="width:auto;min-width:170px">
                        <option value="">-- Keine Position --</option>
                        <option value="primary-nav">Hauptnavigation</option>
                        <option value="meta-bar">Metabar-Men&uuml;</option>
                        <option value="social-bar">Socialbar-Men&uuml;</option>
                        <option value="footer-nav">Footer-Men&uuml;</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-success text-nowrap">
                        <i class="bi bi-check-lg"></i> Speichern
                    </button>
                </form>
            </div>
            <div class="card-body" id="menuEditorContent">
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-arrow-left me-1"></i>
                    W&auml;hle links ein Men&uuml; aus.
                </div>
            </div>

            {{-- Men&uuml;punkt hinzuf&uuml;gen --}}
            <div id="menuItemForm" style="display:none">
                <div class="card-body border-top border-secondary">
                    <h6 class="fw-semibold small mb-3">Men&uuml;punkt hinzuf&uuml;gen</h6>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="text" id="newItemLabel" class="form-control form-control-sm"
                                   placeholder="Beschriftung">
                        </div>
                        <div class="col-4">
                            <input type="text" id="newItemUrl" class="form-control form-control-sm"
                                   placeholder="URL">
                        </div>
                        <div class="col-3">
                            <input type="text" id="newItemIcon" class="form-control form-control-sm"
                                   placeholder="Icon (bi-house)">
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn btn-primary btn-sm w-100"
                                    onclick="addMenuItem()">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
var activeMenuId   = null;
var activeMenuName = '';
var CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

var LOCATIONS = {
    'primary-nav': 'Hauptnavigation',
    'meta-bar':    'Metabar-Menü',
    'social-bar':  'Socialbar-Menü',
    'footer-nav':  'Footer-Menü'
};

function selectMenu(id, name, location) {
    activeMenuId   = id;
    activeMenuName = name;

    document.getElementById('menuEditorTitle').textContent = name + ' bearbeiten';
    document.getElementById('menuItemForm').style.display  = '';

    // Location-Formular einblenden und vorbelegen
    var form = document.getElementById('menuLocationForm');
    form.style.display = '';
    form.action = '/admin/menues/' + id;
    document.getElementById('locationMenuName').value = name;
    document.getElementById('locationSelect').value   = location || '';

    document.getElementById('menuEditorContent').innerHTML =
        '<div class="text-center py-3 text-muted small">' +
        '<span class="spinner-border spinner-border-sm me-1"></span> Wird geladen&hellip;</div>';

    fetch('/admin/menues/' + id + '/items', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { renderItems(data.items || []); })
    .catch(function() { renderItems([]); });
}

function renderItems(items) {
    if (!items.length) {
        document.getElementById('menuEditorContent').innerHTML =
            '<p class="text-muted small text-center py-3 mb-0">Noch keine Eintr&auml;ge.</p>';
        return;
    }
    var html = '<ul class="list-unstyled mb-0" id="menuItemsList">';
    items.forEach(function(item) {
        html += '<li class="d-flex align-items-center gap-2 py-2 border-bottom border-secondary">' +
            '<i class="bi bi-grip-vertical text-muted" style="cursor:grab"></i>' +
            '<div class="flex-grow-1">' +
            '<div class="fw-semibold small">' +
            (item.icon ? '<i class="bi ' + item.icon + ' me-1 opacity-50"></i>' : '') +
            item.label + '</div>' +
            '<div class="text-muted" style="font-size:.72rem">' + item.url + '</div></div>' +
            '<button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItem(' + item.id + ')">' +
            '<i class="bi bi-x-lg"></i></button></li>';
    });
    html += '</ul>';
    document.getElementById('menuEditorContent').innerHTML = html;
}

function addMenuItem() {
    if (!activeMenuId) return;
    var label = document.getElementById('newItemLabel').value.trim();
    var url   = document.getElementById('newItemUrl').value.trim();
    var icon  = document.getElementById('newItemIcon')  ? document.getElementById('newItemIcon').value.trim()  : '';
    if (!label || !url) { alert('Bitte Beschriftung und URL eingeben.'); return; }

    fetch('/admin/menues/' + activeMenuId + '/items', {
        method: 'POST',
        headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest',
                   'Content-Type': 'application/json' },
        body: JSON.stringify({ label: label, url: url, icon: icon })
    })
    .then(function(r) { return r.json(); })
    .then(function() {
        document.getElementById('newItemLabel').value = '';
        document.getElementById('newItemUrl').value   = '';
        if (document.getElementById('newItemIcon')) document.getElementById('newItemIcon').value = '';
        selectMenu(activeMenuId, activeMenuName, document.getElementById('locationSelect').value);
    })
    .catch(function() { alert('Fehler beim Hinzufügen.'); });
}

function removeItem(id) {
    if (!confirm('Eintrag entfernen?')) return;
    fetch('/admin/menues/items/' + id, {
        method: 'POST',
        headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest',
                   'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_method=DELETE&_token=' + encodeURIComponent(CSRF)
    }).then(function() { selectMenu(activeMenuId, activeMenuName, document.getElementById('locationSelect').value); });
}
</script>
@endsection

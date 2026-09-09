@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="/admin/seiten" class="text-decoration-none">Seiten</a>
    </li>
    <li class="breadcrumb-item active">{{ $page ? 'Bearbeiten' : 'Neue Seite' }}</li>
@endsection

@section('content')
<form method="POST"
      action="{{ $page ? '/admin/seiten/' . $page->getAttribute('id') : '/admin/seiten' }}"
      id="pageForm">

    <input type="hidden" name="_token" value="{{ $__csrf }}">
    @if ($page)
        <input type="hidden" name="_method" value="POST">
    @endif

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 fw-bold mb-0">
            {{ $page ? 'Seite bearbeiten' : 'Neue Seite' }}
        </h1>
        <div class="d-flex gap-2">
            <a href="/admin/seiten" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Zur&uuml;ck
            </a>
            <button type="submit" name="status_action" value="draft" class="btn btn-outline-primary">
                <i class="bi bi-floppy me-1"></i> Als Entwurf speichern
            </button>
            <button type="submit" name="status_action" value="publish" class="btn btn-success">
                <i class="bi bi-send me-1"></i>
                {{ $page && $page->getAttribute('status') === 'published' ? 'Aktualisieren' : 'Veröffentlichen' }}
            </button>
        </div>
    </div>

    @if ($__flash['success'])
    <div class="alert alert-success border-0 mb-4">
        <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
    </div>
    @endif
    @if ($__flash['error'])
    <div class="alert alert-danger border-0 mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $__flash['error'] }}
    </div>
    @endif

    <div class="row g-4 align-items-start">

        {{-- ════ HAUPTINHALT (volle Breite) ════ --}}
        <div class="col-12">

            {{-- Titel + Slug --}}
            <div class="card mb-4">
                <div class="card-body">
                    <input type="text"
                           name="title"
                           id="pageTitle"
                           class="form-control form-control-lg fw-bold border-0 px-0 bg-transparent"
                           placeholder="Seitentitel eingeben&hellip;"
                           value="{{ $page ? $page->getAttribute('title') : '' }}"
                           required
                           autocomplete="off">
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <small class="text-muted">URL:</small>
                        <small class="text-muted" id="slugDisplay">
                            /seite/<span id="slugSpan">{{ $page ? $page->getAttribute('slug') : '' }}</span>
                        </small>
                        <button type="button"
                                class="btn btn-link btn-sm p-0 text-muted"
                                style="font-size:.75rem"
                                id="editSlugBtn">
                            <i class="bi bi-pencil"></i> &Auml;ndern
                        </button>
                    </div>
                    <input type="hidden" name="slug" id="pageSlug"
                           value="{{ $page ? $page->getAttribute('slug') : '' }}">
                </div>
            </div>

            {{-- Block-Editor --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">
                        <i class="bi bi-layout-text-sidebar me-2"></i>Block-Editor
                    </span>
                    <span class="badge bg-primary bg-opacity-25 text-primary" style="font-size:.7rem">15 Block-Typen</span>
                </div>
                <div class="card-body p-3">
                    <textarea name="content" id="hb-content-target" class="d-none">{{ $page ? $page->getAttribute('content') : '' }}</textarea>
                    <div id="hb-canvas" onclick="if(event.target===this)HuberBlocks._deselectAll()"></div>
                </div>
            </div>

        </div>
    </div>

    {{-- ════ FLY-OUT SIDEBAR (rechts, hover) ════ --}}
    <div id="page-flyout">
        {{-- Panel-Inhalt --}}
        <div id="page-flyout-inner">

            {{-- Block-Inserter --}}
            <div class="hb-panel-card mb-3">
                <div class="hb-panel-header"><i class="bi bi-plus-square me-2"></i>Block hinzuf&uuml;gen</div>
                <div id="hb-inserter-list"></div>
            </div>

            {{-- Status & Ver&ouml;ffentlichung --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold small">Ver&ouml;ffentlichung</div>
                <div class="card-body">
                    <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">Status</label>
                    <select name="status" id="statusSelect" class="form-select mb-3">
                        @foreach ($statuses as $s)
                        <option value="{{ $s->value }}"
                            {{ ($page && $page->getAttribute('status') === $s->value) || (!$page && $s->value === 'draft') ? 'selected' : '' }}>
                            {{ $s->label() }}
                        </option>
                        @endforeach
                    </select>
                    <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">Template</label>
                    <select name="template" class="form-select">
                        <option value="default" {{ ($page && $page->getAttribute('template') === 'default') || !$page ? 'selected' : '' }}>Standard</option>
                        <option value="full-width" {{ $page && $page->getAttribute('template') === 'full-width' ? 'selected' : '' }}>Volle Breite</option>
                        <option value="landing" {{ $page && $page->getAttribute('template') === 'landing' ? 'selected' : '' }}>Landing Page</option>
                        <option value="contact" {{ $page && $page->getAttribute('template') === 'contact' ? 'selected' : '' }}>Kontakt</option>
                    </select>
                </div>
            </div>

            {{-- Optionen --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold small">Optionen</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">Reihenfolge</label>
                        <input type="number" name="sort_order" class="form-control form-control-sm"
                               value="{{ $page ? $page->getAttribute('sort_order') : 0 }}" min="0" max="9999">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="show_in_menu" value="1"
                               id="showInMenu" {{ ($page && $page->getAttribute('show_in_menu')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="showInMenu">Im Men&uuml; anzeigen</label>
                    </div>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card">
                <div class="card-header fw-semibold small">SEO</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Meta-Titel <span id="cntTitle" class="text-muted ms-1" style="font-size:.7rem">0&thinsp;/&thinsp;60</span></label>
                        <input type="text" name="meta_title" id="metaTitle" class="form-control form-control-sm"
                               maxlength="60" placeholder="Titel f&uuml;r Suchmaschinen"
                               value="{{ $page ? $page->getAttribute('meta_title') : '' }}">
                    </div>
                    <div>
                        <label class="form-label small">Meta-Beschreibung <span id="cntDesc" class="text-muted ms-1" style="font-size:.7rem">0&thinsp;/&thinsp;160</span></label>
                        <textarea name="meta_description" id="metaDesc" class="form-control form-control-sm"
                                  rows="3" maxlength="160"
                                  placeholder="Kurzbeschreibung f&uuml;r Google (max. 160 Zeichen)">{{ $page ? $page->getAttribute('meta_description') : '' }}</textarea>
                    </div>
                </div>
            </div>

        </div>{{-- /flyout-inner --}}
        <div id="page-flyout-tab" title="Einstellungen"><i class="bi bi-gear-fill"></i></div>
    </div>{{-- /flyout --}}
</form>
@endsection

@section('scripts')
<link rel="stylesheet" href="/assets/css/block-editor.css">
<script src="/assets/js/block-editor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    HuberBlocks.init({ targetId:'hb-content-target', canvasId:'hb-canvas', inserterId:'hb-inserter-list' });
});

var slugLocked = !!document.getElementById('pageSlug').value;
function toSlug(str) {
    var map = {'ä':'ae','ö':'oe','ü':'ue','Ä':'ae','Ö':'oe','Ü':'ue','ß':'ss'};
    return str.replace(/[äöüÄÖÜß]/g, function(c){ return map[c]||c; })
              .toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
}
document.getElementById('pageTitle').addEventListener('input', function() {
    if (!slugLocked) {
        var s = toSlug(this.value);
        document.getElementById('pageSlug').value = s;
        document.getElementById('slugSpan').textContent = s;
    }
});
document.getElementById('editSlugBtn').addEventListener('click', function() {
    var v = prompt('Slug bearbeiten:', document.getElementById('pageSlug').value);
    if (v !== null) {
        var s = toSlug(v);
        document.getElementById('pageSlug').value = s;
        document.getElementById('slugSpan').textContent = s;
        slugLocked = true;
    }
});
function initCounter(fieldId, counterId, max) {
    var field = document.getElementById(fieldId);
    var cnt   = document.getElementById(counterId);
    if (!field || !cnt) return;
    function upd() { var l=field.value.length; cnt.textContent=l+'\u2009/\u2009'+max; cnt.style.color=l>max?'#ef4444':''; }
    field.addEventListener('input', upd); upd();
}
initCounter('metaTitle','cntTitle',60);
initCounter('metaDesc','cntDesc',160);
</script>
@endsection

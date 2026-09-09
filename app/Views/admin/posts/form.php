@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="/admin/beitraege" class="text-decoration-none">Beitr&auml;ge</a>
    </li>
    <li class="breadcrumb-item active">{{ $post ? 'Bearbeiten' : 'Neuer Beitrag' }}</li>
@endsection

@section('content')
<form method="POST"
      action="{{ $post ? '/admin/beitraege/' . $post->getAttribute('id') : '/admin/beitraege' }}"
      id="postForm">

    <input type="hidden" name="_token" value="{{ $__csrf }}">
    @if ($post)
        <input type="hidden" name="_method" value="POST">
    @endif

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 fw-bold mb-0">
            {{ $post ? 'Beitrag bearbeiten' : 'Neuer Beitrag' }}
        </h1>
        <div class="d-flex gap-2">
            <a href="/admin/beitraege" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Zur&uuml;ck
            </a>
            <button type="submit" name="status_action" value="draft" class="btn btn-outline-primary">
                <i class="bi bi-floppy me-1"></i> Als Entwurf speichern
            </button>
            <button type="submit" name="status_action" value="publish" class="btn btn-success">
                <i class="bi bi-send me-1"></i>
                {{ $post && $post->getAttribute('status') === 'published' ? 'Aktualisieren' : 'Veröffentlichen' }}
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
                           id="postTitle"
                           class="form-control form-control-lg fw-bold border-0 px-0 bg-transparent"
                           placeholder="Titel des Beitrags&hellip;"
                           value="{{ $post ? $post->getAttribute('title') : '' }}"
                           required
                           autocomplete="off">
                    <div class="mt-2 d-flex align-items-center gap-2">
                        <small class="text-muted">URL:</small>
                        <small class="text-muted" id="slugDisplay">/blog/<span id="slugSpan">{{ $post ? $post->getAttribute('slug') : '' }}</span></small>
                        <button type="button" class="btn btn-link btn-sm p-0 text-muted" style="font-size:.75rem" id="editSlugBtn">
                            <i class="bi bi-pencil"></i> &Auml;ndern
                        </button>
                    </div>
                    <input type="hidden" name="slug" id="postSlug" value="{{ $post ? $post->getAttribute('slug') : '' }}">
                </div>
            </div>

            {{-- Block-Editor --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <span class="fw-semibold small">
                        <i class="bi bi-layout-text-sidebar me-2"></i>Block-Editor
                    </span>
                    <span class="badge bg-primary bg-opacity-25 text-primary" style="font-size:.7rem">15 Block-Typen</span>
                </div>
                <div class="card-body p-3">
                    <textarea name="content" id="hb-content-target" class="d-none">{{ $post ? $post->getAttribute('content') : '' }}</textarea>
                    <div id="hb-canvas" onclick="if(event.target===this)HuberBlocks._deselectAll()"></div>
                </div>
            </div>

            {{-- Auszug --}}
            <div class="card">
                <div class="card-header fw-semibold small">
                    Auszug
                    <small class="text-muted fw-normal ms-2">Wird in Listen und f&uuml;r SEO genutzt</small>
                </div>
                <div class="card-body">
                    <textarea name="excerpt"
                              class="form-control"
                              rows="3"
                              maxlength="300"
                              placeholder="Optionale Kurzbeschreibung (max. 300 Zeichen)&hellip;">{{ $post ? $post->getAttribute('excerpt') : '' }}</textarea>
                </div>
            </div>

        </div>
    </div>

    {{-- ════ FLY-OUT SIDEBAR ════ --}}
    <div id="page-flyout">
        <div id="page-flyout-inner">

            {{-- Block-Inserter --}}
            <div class="hb-panel-card mb-3">
                <div class="hb-panel-header"><i class="bi bi-plus-square me-2"></i>Block hinzuf&uuml;gen</div>
                <div id="hb-inserter-list"></div>
            </div>

            {{-- Status --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold small">Ver&ouml;ffentlichung</div>
                <div class="card-body">
                    <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">Status</label>
                    <select name="status" id="statusSelect" class="form-select mb-3">
                        @foreach ($statuses as $s)
                        <option value="{{ $s->value }}"
                            {{ ($post && $post->getAttribute('status') === $s->value) || (!$post && $s->value === 'draft') ? 'selected' : '' }}>
                            {{ $s->label() }}
                        </option>
                        @endforeach
                    </select>
                    <div id="scheduledBlock" class="d-none">
                        <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">Ver&ouml;ffentlichen am</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control form-control-sm"
                               value="{{ $post ? $post->getAttribute('scheduled_at') : '' }}">
                    </div>
                </div>
            </div>

            {{-- Optionen --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold small">Optionen</div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_comments" value="1"
                               id="allowComments" {{ (!$post || $post->getAttribute('allow_comments')) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allowComments">Kommentare erlauben</label>
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
                               value="{{ $post ? $post->getAttribute('meta_title') : '' }}">
                    </div>
                    <div>
                        <label class="form-label small">Meta-Beschreibung <span id="cntDesc" class="text-muted ms-1" style="font-size:.7rem">0&thinsp;/&thinsp;160</span></label>
                        <textarea name="meta_description" id="metaDesc" class="form-control form-control-sm"
                                  rows="3" maxlength="160"
                                  placeholder="Kurzbeschreibung f&uuml;r Google (max. 160 Zeichen)">{{ $post ? $post->getAttribute('meta_description') : '' }}</textarea>
                    </div>
                </div>
            </div>

        </div>
        <div id="page-flyout-tab" title="Einstellungen"><i class="bi bi-gear-fill"></i></div>
    </div>
</form>
@endsection

@section('scripts')
<link rel="stylesheet" href="/assets/css/block-editor.css">
<script src="/assets/js/block-editor.js"></script>
<script>
// ── Block Editor initialisieren ───────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    HuberBlocks.init({
        targetId:   'hb-content-target',
        canvasId:   'hb-canvas',
        inserterId: 'hb-inserter-list'
    });
});

// ── Slug aus Titel generieren ─────────────────────────────
var slugLocked = !!document.getElementById('postSlug').value;

function toSlug(str) {
    var map = {'ä':'ae','ö':'oe','ü':'ue','Ä':'ae','Ö':'oe','Ü':'ue','ß':'ss'};
    return str.replace(/[äöüÄÖÜß]/g, function(c){ return map[c]||c; })
              .toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
}
document.getElementById('postTitle').addEventListener('input', function() {
    if (!slugLocked) {
        var s = toSlug(this.value);
        document.getElementById('postSlug').value = s;
        document.getElementById('slugSpan').textContent = s;
    }
});
document.getElementById('editSlugBtn').addEventListener('click', function() {
    var v = prompt('Slug bearbeiten:', document.getElementById('postSlug').value);
    if (v !== null) {
        var s = toSlug(v);
        document.getElementById('postSlug').value = s;
        document.getElementById('slugSpan').textContent = s;
        slugLocked = true;
    }
});

// ── Status → Scheduled Block ──────────────────────────────
var statusSel = document.getElementById('statusSelect');
if (statusSel) {
    statusSel.addEventListener('change', function() {
        var block = document.getElementById('scheduledBlock');
        if (block) block.classList.toggle('d-none', this.value !== 'scheduled');
    });
}

// ── Zeichen-Zähler ────────────────────────────────────────
function initCounter(fieldId, counterId, max) {
    var field = document.getElementById(fieldId);
    var cnt   = document.getElementById(counterId);
    if (!field || !cnt) return;
    function update() {
        var len = field.value.length;
        cnt.textContent = len + '\u2009/\u2009' + max;
        cnt.style.color = len > max ? '#ef4444' : '';
    }
    field.addEventListener('input', update);
    update();
}
initCounter('metaTitle', 'cntTitle', 60);
initCounter('metaDesc',  'cntDesc',  160);
</script>
@endsection

@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Medien</li>
@endsection

@section('head')
<style>
/* ── Toolbar ──────────────────────────────────────────── */
.media-toolbar {
    background: var(--card-bg,rgba(30,41,59,.8));
    border: 1px solid var(--card-border,rgba(255,255,255,.07));
    border-radius: 12px;
    padding: .75rem 6rem;
    margin-bottom: 1.25rem;
    margin-left: 2rem;
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
}

/* ── Upload-Zone ─────────────────────────────────────── */
#dropZone {
    border: 2px dashed rgba(99,102,241,.4);
    border-radius: 12px;
    background: rgba(99,102,241,.04);
    padding: 2.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    margin-bottom: 1.25rem;
    display: none;
}
#dropZone.show { display: block; }
#dropZone.drag-over {
    border-color: #6366f1;
    background: rgba(99,102,241,.1);
}

/* ── Grid ────────────────────────────────────────────── */
#mediaGrid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px,1fr));
    gap: .75rem;
}

.media-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    background: rgba(255,255,255,.04);
    border: 2px solid transparent;
    transition: all .18s;
    aspect-ratio: 1;
}
.media-item:hover { border-color: rgba(99,102,241,.5); transform: scale(1.02); }
.media-item.selected { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.25); }

.media-item img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.media-item .media-icon {
    width: 100%; height: 100%;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: .5rem; color: #94a3b8;
    font-size: 2rem;
}
.media-item .media-icon small { font-size: .65rem; text-transform: uppercase; letter-spacing: .08em; }

.media-item .media-check {
    position: absolute; top: 6px; left: 6px;
    width: 20px; height: 20px;
    background: #6366f1; border-radius: 50%;
    display: none; align-items: center; justify-content: center;
    color: #fff; font-size: .7rem;
}
.media-item.selected .media-check { display: flex; }

/* ── List view ───────────────────────────────────────── */
#mediaGrid.list-view {
    grid-template-columns: 1fr;
    gap: .25rem;
}
#mediaGrid.list-view .media-item {
    aspect-ratio: unset;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: .5rem .75rem;
    border-radius: 8px;
}
#mediaGrid.list-view .media-item img,
#mediaGrid.list-view .media-item .media-icon {
    width: 48px; height: 48px;
    flex-shrink: 0; border-radius: 6px;
    font-size: 1.2rem;
}
#mediaGrid.list-view .media-info-inline { display: flex !important; flex-direction: column; flex: 1; min-width: 0; }
#mediaGrid.list-view .media-check { top: 50%; transform: translateY(-50%); left: 6px; }

/* ── Detail Panel ────────────────────────────────────── */
#detailPanel {
    position: fixed;
    right: -340px;
    top: 0; bottom: 0;
    width: 320px;
    background: #0d1117;
    border-left: 1px solid rgba(255,255,255,.08);
    z-index: 1050;
    transition: right .25s ease;
    display: flex; flex-direction: column;
    overflow-y: auto;
}
#detailPanel.open { right: 0; }

#detailPanel .dp-header {
    padding: 1rem;
    border-bottom: 1px solid rgba(255,255,255,.08);
    display: flex; justify-content: space-between; align-items: center;
}
#detailPanel .dp-body { padding: 1rem; flex: 1; }
#detailPanel .dp-preview {
    width: 100%; aspect-ratio: 1;
    object-fit: contain;
    border-radius: 8px;
    background: rgba(255,255,255,.04);
    margin-bottom: 1rem;
}
#detailPanel .dp-preview-icon {
    width: 100%; aspect-ratio: 1;
    display: flex; align-items: center; justify-content: center;
    font-size: 4rem; color: #64748b;
    background: rgba(255,255,255,.04);
    border-radius: 8px; margin-bottom: 1rem;
}

/* Upload progress */
.upload-progress-item {
    background: rgba(255,255,255,.04);
    border-radius: 8px;
    padding: .5rem .75rem;
    margin-top: .5rem;
    font-size: .8rem;
}

/* Overlay when panel is open */
#detailOverlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.4);
    z-index: 1040;
}
#detailOverlay.show { display: block; }
</style>
@endsection

@section('content')

{{-- ── Toolbar ─────────────────────────────────────── --}}
<div class="media-toolbar">
    {{-- Upload button --}}
    <button type="button" class="btn btn-primary" id="btnUpload" onclick="toggleDropZone()">
        <i class="bi bi-cloud-upload me-1"></i> Hochladen
    </button>

    {{-- Type filter --}}
    <div class="d-flex gap-1">
        <a href="/admin/medien"
           class="btn btn-sm {{ $filterType === '' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Alle
        </a>
        <a href="/admin/medien?type=image"
           class="btn btn-sm {{ $filterType === 'image' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-image me-1"></i>Bilder
        </a>
        <a href="/admin/medien?type=video"
           class="btn btn-sm {{ $filterType === 'video' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-play-circle me-1"></i>Videos
        </a>
        <a href="/admin/medien?type=audio"
           class="btn btn-sm {{ $filterType === 'audio' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-music-note me-1"></i>Audio
        </a>
        <a href="/admin/medien?type=document"
           class="btn btn-sm {{ $filterType === 'document' ? 'btn-primary' : 'btn-outline-secondary' }}">
            <i class="bi bi-file-earmark me-1"></i>Dokumente
        </a>
    </div>

    {{-- Spacer --}}
    <div class="flex-grow-1"></div>

    {{-- Stats --}}
    <small class="text-muted d-none d-md-block">{{ $total }} Dateien</small>

    {{-- Search --}}
    <div style="width:200px">
        <input type="text" id="mediaSearch" class="form-control form-control-sm"
               placeholder="Suchen&hellip;" autocomplete="off">
    </div>

    {{-- View toggle --}}
    <div class="btn-group btn-group-sm">
        <button type="button" class="btn btn-outline-secondary active" id="btnGrid"
                onclick="setView('grid')" title="Rasteransicht">
            <i class="bi bi-grid-3x3-gap"></i>
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btnList"
                onclick="setView('list')" title="Listenansicht">
            <i class="bi bi-list-ul"></i>
        </button>
    </div>
</div>

{{-- ── Drag & Drop Upload Zone ────────────────────── --}}
<div id="dropZone" data-dropzone>
    <i class="bi bi-cloud-upload fs-1 text-primary mb-2 d-block"></i>
    <p class="mb-1 fw-semibold">Dateien hier ablegen oder klicken zum Ausw&auml;hlen</p>
    <p class="text-muted small mb-3">Unterst&uuml;tzte Formate: JPG, PNG, WebP, GIF, PDF, MP4, MP3&hellip;</p>
    <input type="file" id="fileInput" multiple accept="image/*,video/*,audio/*,.pdf,.svg,.zip"
           class="d-none">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('fileInput').click()">
        <i class="bi bi-folder2-open me-1"></i> Dateien ausw&auml;hlen
    </button>
    <div id="uploadProgress" class="mt-3" style="max-width:400px;margin:0 auto"></div>
</div>

{{-- ── Flash ───────────────────────────────────────── --}}
@if ($__flash['success'])
<div class="alert alert-success border-0 mb-3">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

{{-- ── Media Grid ──────────────────────────────────── --}}
@if ($media)
<div id="mediaGrid">
    @foreach ($media as $item)
    <div class="media-item"
         data-id="{{ $item['id'] }}"
         data-type="{{ $item['type'] }}"
         data-url="{{ $item['public_url'] }}"
         data-filename="{{ $item['filename'] }}"
         data-size="{{ $item['size'] }}"
         data-mime="{{ $item['mime_type'] }}"
         data-alt="{{ $item['alt_text'] }}"
         data-caption="{{ $item['caption'] }}"
         data-width="{{ $item['width'] }}"
         data-height="{{ $item['height'] }}"
         data-date="{{ date('d.m.Y H:i', strtotime($item['created_at'])) }}"
         data-thumb="{{ $item['thumbnail_path'] ? '/uploads/' . $item['thumbnail_path'] : $item['public_url'] }}"
         onclick="openDetail(this)">

        <div class="media-check"><i class="bi bi-check"></i></div>

        @if ($item['type'] === 'image')
            <img src="{{ $item['thumbnail_path'] ? '/uploads/' . $item['thumbnail_path'] : $item['public_url'] }}"
                 alt="{{ $item['alt_text'] }}"
                 loading="lazy">
        @elseif ($item['type'] === 'video')
            <div class="media-icon">
                <i class="bi bi-play-circle-fill text-info"></i>
                <small>VIDEO</small>
            </div>
        @elseif ($item['type'] === 'audio')
            <div class="media-icon">
                <i class="bi bi-music-note-beamed text-warning"></i>
                <small>AUDIO</small>
            </div>
        @elseif ($item['type'] === 'document')
            <div class="media-icon">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                <small>{{ strtoupper(pathinfo($item['filename'], PATHINFO_EXTENSION)) }}</small>
            </div>
        @else
            <div class="media-icon">
                <i class="bi bi-file-earmark-zip-fill text-secondary"></i>
                <small>{{ strtoupper(pathinfo($item['filename'], PATHINFO_EXTENSION)) }}</small>
            </div>
        @endif

        {{-- List-view info (hidden in grid) --}}
        <div class="media-info-inline d-none">
            <span class="fw-semibold text-light text-truncate small">{{ $item['filename'] }}</span>
            <span class="text-muted" style="font-size:.72rem">
                {{ $item['mime_type'] }} &bull;
                {{ $item['size'] > 1048576 ? round($item['size']/1048576,1).'&nbsp;MB' : round($item['size']/1024,1).'&nbsp;KB' }}
            </span>
        </div>
    </div>
    @endforeach
</div>

{{-- Pagination --}}
@if ($total > $perPage)
<div class="d-flex justify-content-center gap-2 mt-4">
    @if ($page > 1)
        <a href="/admin/medien?page={{ $page - 1 }}&type={{ $filterType }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-chevron-left"></i> Zur&uuml;ck
        </a>
    @endif
    <span class="btn btn-outline-secondary btn-sm disabled">
        Seite {{ $page }} von {{ ceil($total / $perPage) }}
    </span>
    @if ($page * $perPage < $total)
        <a href="/admin/medien?page={{ $page + 1 }}&type={{ $filterType }}"
           class="btn btn-outline-secondary btn-sm">
            Weiter <i class="bi bi-chevron-right"></i>
        </a>
    @endif
</div>
@endif

@else
{{-- Leer-State --}}
<div class="text-center py-5 text-muted">
    <i class="bi bi-images d-block fs-1 mb-3 opacity-25"></i>
    <p class="mb-3">Keine Mediendateien gefunden.</p>
    <button type="button" class="btn btn-primary" onclick="toggleDropZone()">
        <i class="bi bi-cloud-upload me-1"></i> Erste Datei hochladen
    </button>
</div>
@endif

{{-- ── Detail Panel (Overlay) ─────────────────────── --}}
<div id="detailOverlay" onclick="closeDetail()"></div>

<div id="detailPanel">
    <div class="dp-header">
        <span class="fw-semibold small">Datei-Details</span>
        <button type="button" class="btn btn-link p-0 text-muted" onclick="closeDetail()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="dp-body">
        <div id="dpPreviewWrap"></div>

        <h6 class="fw-semibold mb-1 text-truncate" id="dpFilename"></h6>
        <p class="text-muted small mb-3" id="dpMeta"></p>

        {{-- Alt text --}}
        <div class="mb-3">
            <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">
                Alt-Text
            </label>
            <input type="text" id="dpAlt" class="form-control form-control-sm"
                   placeholder="Bildbeschreibung f&uuml;r Barrierefreiheit&hellip;">
        </div>

        {{-- Caption --}}
        <div class="mb-3">
            <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">
                Bildunterschrift
            </label>
            <input type="text" id="dpCaption" class="form-control form-control-sm"
                   placeholder="Optionale Bildunterschrift&hellip;">
        </div>

        {{-- URL --}}
        <div class="mb-3">
            <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">
                URL
            </label>
            <div class="input-group input-group-sm">
                <input type="text" id="dpUrl" class="form-control" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyUrl()" title="Kopieren">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        </div>

        {{-- Dimensions --}}
        <p class="text-muted small mb-3" id="dpDimensions"></p>

        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm flex-grow-1" id="btnSaveAlt" onclick="saveAlt()">
                <i class="bi bi-floppy me-1"></i> Speichern
            </button>
            <a id="dpViewLink" href="#" target="_blank"
               class="btn btn-outline-secondary btn-sm" title="In neuem Tab &ouml;ffnen">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm" id="btnDelete" onclick="deleteMedia()">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var currentId = null;
var CSRF = '{{ $__csrf }}';

// ── Upload-Zone toggling ─────────────────────────────
function toggleDropZone() {
    var z = document.getElementById('dropZone');
    z.classList.toggle('show');
}

// Drag & Drop
var dz = document.getElementById('dropZone');
dz.addEventListener('dragover', function(e) { e.preventDefault(); dz.classList.add('drag-over'); });
dz.addEventListener('dragleave', function() { dz.classList.remove('drag-over'); });
dz.addEventListener('drop', function(e) {
    e.preventDefault();
    dz.classList.remove('drag-over');
    uploadFiles(e.dataTransfer.files);
});
dz.addEventListener('click', function(e) {
    if (e.target === dz || e.target.tagName === 'P' || e.target.tagName === 'I') {
        document.getElementById('fileInput').click();
    }
});
document.getElementById('fileInput').addEventListener('change', function() {
    uploadFiles(this.files);
});

function uploadFiles(files) {
    var progress = document.getElementById('uploadProgress');
    Array.from(files).forEach(function(file) {
        var bar = document.createElement('div');
        bar.className = 'upload-progress-item';
        bar.innerHTML = '<div class="d-flex justify-content-between mb-1">' +
            '<span class="text-truncate" style="max-width:200px">' + file.name + '</span>' +
            '<span class="text-muted" id="s_' + Date.now() + '">0%</span></div>' +
            '<div class="progress" style="height:4px">' +
            '<div class="progress-bar bg-primary" style="width:0" id="p_' + Date.now() + '"></div></div>';
        progress.appendChild(bar);

        var statusEl = bar.querySelector('span:last-child');
        var barEl    = bar.querySelector('.progress-bar');

        var fd = new FormData();
        fd.append('file', file);
        fd.append('_token', CSRF);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/admin/medien/upload');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-Token', CSRF);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                var pct = Math.round(e.loaded / e.total * 100);
                barEl.style.width = pct + '%';
                statusEl.textContent = pct + '%';
            }
        };

        xhr.onload = function() {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    barEl.classList.replace('bg-primary', 'bg-success');
                    statusEl.textContent = 'Fertig';
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    barEl.classList.replace('bg-primary', 'bg-danger');
                    statusEl.textContent = res.error || 'Fehler';
                }
            } catch(e) {
                barEl.classList.replace('bg-primary', 'bg-danger');
                statusEl.textContent = 'Fehler';
            }
        };

        xhr.send(fd);
    });
}

// ── View toggle ──────────────────────────────────────
var currentView = localStorage.getItem('mediaView') || 'grid';
setView(currentView, true);

function setView(v, silent) {
    currentView = v;
    if (!silent) localStorage.setItem('mediaView', v);
    var grid = document.getElementById('mediaGrid');
    var btnG = document.getElementById('btnGrid');
    var btnL = document.getElementById('btnList');
    if (!grid) return;
    if (v === 'list') {
        grid.classList.add('list-view');
        btnL.classList.add('active'); btnG.classList.remove('active');
    } else {
        grid.classList.remove('list-view');
        btnG.classList.add('active'); btnL.classList.remove('active');
    }
}

// ── Live search ──────────────────────────────────────
document.getElementById('mediaSearch').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.media-item').forEach(function(el) {
        el.style.display = el.dataset.filename.toLowerCase().indexOf(q) > -1 ? '' : 'none';
    });
});

// ── Detail Panel ─────────────────────────────────────
function openDetail(el) {
    currentId = el.dataset.id;

    // Mark selected
    document.querySelectorAll('.media-item').forEach(function(i) { i.classList.remove('selected'); });
    el.classList.add('selected');

    // Preview
    var wrap = document.getElementById('dpPreviewWrap');
    if (el.dataset.type === 'image') {
        wrap.innerHTML = '<img src="' + el.dataset.url + '" class="dp-preview" alt="">';
    } else {
        var icons = {video:'bi-play-circle-fill text-info', audio:'bi-music-note-beamed text-warning',
                     document:'bi-file-earmark-pdf-fill text-danger', archive:'bi-file-earmark-zip-fill text-secondary'};
        var icon = icons[el.dataset.type] || 'bi-file-earmark';
        wrap.innerHTML = '<div class="dp-preview-icon"><i class="bi ' + icon + '"></i></div>';
    }

    document.getElementById('dpFilename').textContent  = el.dataset.filename;
    document.getElementById('dpUrl').value             = el.dataset.url;
    document.getElementById('dpAlt').value             = el.dataset.alt;
    document.getElementById('dpCaption').value         = el.dataset.caption;
    document.getElementById('dpViewLink').href         = el.dataset.url;

    var size = el.dataset.size > 1048576
        ? (el.dataset.size / 1048576).toFixed(1) + ' MB'
        : (el.dataset.size / 1024).toFixed(1) + ' KB';
    document.getElementById('dpMeta').textContent = el.dataset.mime + ' \u2022 ' + size + ' \u2022 ' + el.dataset.date;

    var dim = el.dataset.width && el.dataset.height
        ? el.dataset.width + ' \u00d7 ' + el.dataset.height + ' px'
        : '';
    document.getElementById('dpDimensions').textContent = dim;

    document.getElementById('detailPanel').classList.add('open');
    document.getElementById('detailOverlay').classList.add('show');
}

function closeDetail() {
    document.getElementById('detailPanel').classList.remove('open');
    document.getElementById('detailOverlay').classList.remove('show');
    document.querySelectorAll('.media-item').forEach(function(i) { i.classList.remove('selected'); });
    currentId = null;
}

function copyUrl() {
    navigator.clipboard.writeText(document.getElementById('dpUrl').value).then(function() {
        showToast('success', 'URL kopiert!');
    });
}

function saveAlt() {
    if (!currentId) return;
    fetch('/admin/medien/' + currentId + '/alt', {
        method: 'POST',
        headers: {'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest',
                  'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_token=' + encodeURIComponent(CSRF) +
              '&alt_text=' + encodeURIComponent(document.getElementById('dpAlt').value) +
              '&caption='  + encodeURIComponent(document.getElementById('dpCaption').value)
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) showToast('success', 'Gespeichert.');
        else showToast('error', 'Fehler beim Speichern.');
    });
}

function deleteMedia() {
    if (!currentId) return;
    if (!confirm('Datei wirklich l\u00f6schen? Diese Aktion kann nicht r\u00fcckg\u00e4ngig gemacht werden.')) return;

    fetch('/admin/medien/' + currentId, {
        method: 'POST',
        headers: {'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest',
                  'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_token=' + encodeURIComponent(CSRF) + '&_method=DELETE'
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            var el = document.querySelector('.media-item[data-id="' + currentId + '"]');
            if (el) el.remove();
            closeDetail();
            showToast('success', 'Datei gel\u00f6scht.');
        }
    });
}

// Keyboard: Escape closes panel
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDetail();
});
</script>
@endsection

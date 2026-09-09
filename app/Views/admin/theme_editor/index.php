@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Theme-Editor</li>
@endsection

@section('head')
<style>
#themeEditorCode {
    font-family: 'Cascadia Code', 'Fira Code', 'Courier New', monospace;
    font-size: .85rem;
    line-height: 1.6;
    min-height: 500px;
    resize: vertical;
    background: #0d1117;
    color: #e6edf3;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 0 0 8px 8px;
}
.file-tree-item {
    padding: .3rem .75rem;
    border-radius: 6px;
    cursor: pointer;
    font-size: .82rem;
    color: #94a3b8;
    transition: all .15s;
    white-space: nowrap;
}
.file-tree-item:hover { background: rgba(255,255,255,.06); color: #e2e8f0; }
.file-tree-item.active { background: rgba(99,102,241,.15); color: #818cf8; }
.file-tree-item i { margin-right: .4rem; }
</style>
@endsection

@section('content')
<div style="padding-top:3rem; padding-left:8rem">
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Theme-Editor</h1>
        <p class="text-muted small mb-0">
            <i class="bi bi-exclamation-triangle text-warning me-1"></i>
            Direktes Bearbeiten von Theme-Dateien — Vorsicht bei &Auml;nderungen.
        </p>
    </div>
    <div class="d-flex gap-2">
        <span class="badge align-self-center"
              style="background:rgba(99,102,241,.25); color:#a5b4fc; border:1px solid rgba(99,102,241,.5); font-size:.8rem; padding:.4em .8em; border-radius:8px">
            <i class="bi bi-palette me-1"></i>Aktives Theme: <strong>{{ $activeTheme }}</strong>
        </span>
    </div>
</div>

<div class="row g-4">
    {{-- Datei-Baum --}}
    <div class="col-12 col-lg-3">
        <div class="card">
            <div class="card-header fw-semibold small">
                <i class="bi bi-folder2-open me-2"></i>Theme-Dateien
            </div>
            <div class="card-body p-2">
                @foreach ($files as $file)
                <div class="file-tree-item {{ $selectedFile === $file['path'] ? 'active' : '' }}"
                     onclick="loadFile('{{ $file['path'] }}')">
                    <i class="bi {{ str_ends_with($file['name'], '.css') ? 'bi-filetype-css text-info' : (str_ends_with($file['name'], '.js') ? 'bi-filetype-js text-warning' : 'bi-filetype-php text-success') }}"></i>
                    {{ $file['name'] }}
                </div>
                @endforeach
                @if (!$files)
                <p class="text-muted small text-center py-3 mb-0">Keine Dateien gefunden.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Editor --}}
    <div class="col-12 col-lg-9">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between py-2">
                <span class="fw-semibold small font-monospace" id="editorFilename">
                    {{ $selectedFile ?? 'Datei ausw&auml;hlen' }}
                </span>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveFile()"
                        id="saveFileBtn" {{ !$selectedFile ? 'disabled' : '' }}>
                    <i class="bi bi-floppy me-1"></i> Speichern
                </button>
            </div>
            <form method="POST" action="/admin/theme-editor/save" id="editorForm">
                <input type="hidden" name="_token" value="{{ $__csrf }}">
                <input type="hidden" name="file" id="currentFile" value="{{ $selectedFile ?? '' }}">
                <textarea name="content" id="themeEditorCode"
                          class="form-control border-0"
                          spellcheck="false"
                          autocomplete="off">{{ $content ?? '' }}</textarea>
            </form>
        </div>

        <div class="mt-2 text-muted small">
            <i class="bi bi-info-circle me-1"></i>
            Tipp: Erstelle ein Child-Theme um Original-Dateien zu erhalten.
        </div>
    </div>
</div>
</div>{{-- /padding-left --}}
@endsection

@section('scripts')
<script>
function loadFile(path) {
    document.querySelectorAll('.file-tree-item').forEach(el => el.classList.remove('active'));
    event.target.closest('.file-tree-item').classList.add('active');
    window.location.href = '/admin/theme-editor?file=' + encodeURIComponent(path);
}

function saveFile() {
    document.getElementById('editorForm').submit();
}

// Tab-Einrückung im Textarea
document.getElementById('themeEditorCode').addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        var start = this.selectionStart;
        var end = this.selectionEnd;
        this.value = this.value.slice(0, start) + '    ' + this.value.slice(end);
        this.selectionStart = this.selectionEnd = start + 4;
    }
});
</script>
@endsection

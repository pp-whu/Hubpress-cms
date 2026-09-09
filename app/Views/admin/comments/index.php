@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Kommentare</li>
@endsection

@section('head')
<style>
/* ── Kommentare-Tabelle ──────────────────────────────── */
.comment-row { transition: background .15s; }
.comment-row:hover { background: rgba(255,255,255,.03); }

/* Inline-Aktionen (immer sichtbar auf Mobile, Hover auf Desktop) */
.comment-actions {
    display: none;
    gap: .35rem;
    margin-top: .4rem;
    flex-wrap: wrap;
}
.comment-row:hover .comment-actions { display: flex; }

.comment-content {
    font-size: .875rem;
    line-height: 1.5;
    color: #e2e8f0;
}
.comment-excerpt {
    color: #94a3b8;
    font-size: .8rem;
    margin-top: .25rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Quick-Edit Bereich */
.quick-edit-area {
    display: none;
    background: rgba(99,102,241,.06);
    border: 1px solid rgba(99,102,241,.2);
    border-radius: 8px;
    padding: 1rem;
    margin-top: .5rem;
}
.quick-edit-area.open { display: block; }

/* Status-Indikatoren */
.status-bar {
    width: 3px;
    border-radius: 2px;
    flex-shrink: 0;
    align-self: stretch;
    min-height: 40px;
}
.status-bar.pending  { background: #f59e0b; }
.status-bar.approved { background: #10b981; }
.status-bar.spam     { background: #ef4444; }
.status-bar.trashed  { background: #64748b; }

/* Avatar */
.comment-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #818cf8);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .9rem; color: #fff;
    flex-shrink: 0;
}

/* Filter tabs */
.comment-filter-tabs { display: flex; gap: .25rem; flex-wrap: wrap; }
.comment-filter-tabs a {
    padding: .35rem .75rem;
    border-radius: 6px;
    font-size: .8rem;
    text-decoration: none;
    color: #94a3b8;
    transition: all .15s;
}
.comment-filter-tabs a:hover  { background: rgba(255,255,255,.06); color: #e2e8f0; }
.comment-filter-tabs a.active { background: rgba(99,102,241,.2); color: #818cf8; font-weight: 600; }
.comment-filter-tabs .count   { opacity: .6; margin-left: .2rem; }
</style>
@endsection

@section('content')

{{-- ══ Header ══════════════════════════════════════════ --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Kommentare</h1>
        @if ($counts['pending'] > 0)
        <p class="text-warning small mb-0">
            <i class="bi bi-clock me-1"></i>{{ $counts['pending'] }} ausstehend
        </p>
        @else
        <p class="text-muted small mb-0">Alle Kommentare verwalten</p>
        @endif
    </div>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

{{-- ══ Filter-Tabs + Suche ═════════════════════════════ --}}
<div class="card mb-4">
    <div class="card-body py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">

        <div class="comment-filter-tabs">
            <a href="/admin/kommentare"
               class="{{ $filterStatus === '' ? 'active' : '' }}">
                Alle <span class="count">({{ $counts['all'] }})</span>
            </a>
            <a href="/admin/kommentare?status=pending"
               class="{{ $filterStatus === 'pending' ? 'active' : '' }}">
                <i class="bi bi-clock me-1 text-warning"></i>Ausstehend
                <span class="count">({{ $counts['pending'] }})</span>
            </a>
            <a href="/admin/kommentare?status=approved"
               class="{{ $filterStatus === 'approved' ? 'active' : '' }}">
                <i class="bi bi-check-circle me-1 text-success"></i>Genehmigt
                <span class="count">({{ $counts['approved'] }})</span>
            </a>
            <a href="/admin/kommentare?status=spam"
               class="{{ $filterStatus === 'spam' ? 'active' : '' }}">
                <i class="bi bi-shield-exclamation me-1 text-danger"></i>Spam
                <span class="count">({{ $counts['spam'] }})</span>
            </a>
            <a href="/admin/kommentare?status=trashed"
               class="{{ $filterStatus === 'trashed' ? 'active' : '' }}">
                <i class="bi bi-trash me-1"></i>Papierkorb
                <span class="count">({{ $counts['trashed'] }})</span>
            </a>
        </div>

        {{-- Suche --}}
        <form method="GET" action="/admin/kommentare" class="d-flex gap-2">
            @if ($filterStatus)
                <input type="hidden" name="status" value="{{ $filterStatus }}">
            @endif
            <input type="text" name="s" value="{{ $search }}"
                   class="form-control form-control-sm"
                   placeholder="Kommentare suchen&hellip;"
                   style="width:220px">
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
</div>

{{-- ══ Kommentare-Liste ════════════════════════════════ --}}
<div class="card">
    @if ($comments)

    {{-- Bulk-Aktionen --}}
    <div class="card-header d-flex align-items-center gap-3 py-2">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="selectAll">
        </div>
        <form id="bulkForm" method="POST" action="/admin/kommentare/bulk" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="_token" value="{{ $__csrf }}">
            <input type="hidden" name="ids" id="bulkIds">
            <select name="action" class="form-select form-select-sm" style="width:auto">
                <option value="">Massenaktionen</option>
                <option value="approve">Genehmigen</option>
                <option value="pending">Als ausstehend markieren</option>
                <option value="spam">Als Spam markieren</option>
                <option value="trash">In Papierkorb</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                Ausf&uuml;hren
            </button>
        </form>
        <span class="text-muted small ms-auto" id="selectedInfo" style="display:none">
            <span id="selectedCount">0</span> ausgew&auml;hlt
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:32px"></th>
                    <th style="width:4px"></th>
                    <th>Autor</th>
                    <th>Kommentar</th>
                    <th>Beitrag</th>
                    <th class="text-nowrap">Datum</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($comments as $c)
            <tr class="comment-row" id="comment-{{ $c['id'] }}">
                {{-- Checkbox --}}
                <td class="py-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input comment-check"
                               value="{{ $c['id'] }}">
                    </div>
                </td>

                {{-- Status-Balken --}}
                <td class="px-0 py-3">
                    <div class="status-bar {{ $c['status'] }}"></div>
                </td>

                {{-- Autor --}}
                <td class="py-3" style="min-width:140px">
                    <div class="comment-avatar mb-2">
                        {{ strtoupper(substr($c['author_name'] ?? $c['user_name'] ?? 'A', 0, 1)) }}
                    </div>
                    <div class="fw-semibold small text-light">
                        {{ $c['author_name'] ?? $c['user_name'] ?? 'Anonym' }}
                    </div>
                    @if ($c['author_email'])
                    <div class="text-muted" style="font-size:.72rem;word-break:break-all">
                        {{ $c['author_email'] }}
                    </div>
                    @endif
                    @if ($c['ip_address'])
                    <div class="text-muted" style="font-size:.7rem">
                        <i class="bi bi-globe me-1"></i>{{ $c['ip_address'] }}
                    </div>
                    @endif
                </td>

                {{-- Kommentar-Text --}}
                <td class="py-3">
                    {{-- Status-Badge --}}
                    @if ($c['status'] === 'pending')
                        <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 mb-1">
                            <i class="bi bi-clock me-1" style="font-size:.6rem;vertical-align:middle"></i>Ausstehend
                        </span>
                    @elseif ($c['status'] === 'spam')
                        <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 mb-1">
                            <i class="bi bi-shield-exclamation me-1" style="font-size:.6rem;vertical-align:middle"></i>Spam
                        </span>
                    @elseif ($c['status'] === 'trashed')
                        <span class="badge bg-secondary bg-opacity-15 text-secondary border border-secondary border-opacity-25 mb-1">
                            <i class="bi bi-trash me-1" style="font-size:.6rem;vertical-align:middle"></i>Papierkorb
                        </span>
                    @endif

                    <div class="comment-excerpt">{{ $c['content'] }}</div>

                    {{-- Quick-Edit --}}
                    <div class="quick-edit-area" id="qe-{{ $c['id'] }}">
                        <form method="POST" action="/admin/kommentare/{{ $c['id'] }}/edit">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <textarea name="content" class="form-control form-control-sm mb-2"
                                      rows="3">{{ $c['content'] }}</textarea>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-floppy me-1"></i>Speichern
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="closeQuickEdit('{{ $c['id'] }}')">
                                    Abbrechen
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Inline-Aktionen (sichtbar beim Hover) --}}
                    <div class="comment-actions">
                        @if ($c['status'] !== 'approved')
                        <form method="POST" action="/admin/kommentare/{{ $c['id'] }}/approve" class="d-inline">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <button type="submit" class="btn btn-xs btn-success py-0 px-2"
                                    style="font-size:.72rem">
                                <i class="bi bi-check-lg me-1"></i>Genehmigen
                            </button>
                        </form>
                        @endif

                        @if ($c['status'] === 'approved')
                        <form method="POST" action="/admin/kommentare/{{ $c['id'] }}/unapprove" class="d-inline">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <button type="submit" class="btn btn-xs btn-outline-warning py-0 px-2"
                                    style="font-size:.72rem">
                                <i class="bi bi-x-lg me-1"></i>Ablehnen
                            </button>
                        </form>
                        @endif

                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2"
                                style="font-size:.72rem"
                                onclick="openQuickEdit('{{ $c['id'] }}')">
                            <i class="bi bi-pencil me-1"></i>Schnell bearbeiten
                        </button>

                        @if ($c['status'] !== 'spam')
                        <form method="POST" action="/admin/kommentare/{{ $c['id'] }}/spam" class="d-inline">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2"
                                    style="font-size:.72rem">
                                <i class="bi bi-shield-exclamation me-1"></i>Spam
                            </button>
                        </form>
                        @endif

                        @if ($c['status'] === 'trashed')
                        <form method="POST" action="/admin/kommentare/{{ $c['id'] }}/restore" class="d-inline">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <button type="submit" class="btn btn-xs btn-outline-secondary py-0 px-2"
                                    style="font-size:.72rem">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Wiederherstellen
                            </button>
                        </form>
                        @endif

                        <form method="POST" action="/admin/kommentare/{{ $c['id'] }}" class="d-inline">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-xs btn-outline-danger py-0 px-2"
                                    style="font-size:.72rem"
                                    data-confirm="Kommentar wirklich l&ouml;schen?">
                                <i class="bi bi-trash me-1"></i>L&ouml;schen
                            </button>
                        </form>
                    </div>
                </td>

                {{-- Beitrag --}}
                <td class="py-3" style="min-width:140px">
                    @if ($c['post_title'])
                    <a href="/blog/{{ $c['post_slug'] }}" target="_blank"
                       class="text-decoration-none text-light small fw-semibold"
                       style="display:block;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                       title="{{ $c['post_title'] }}">
                        {{ $c['post_title'] }}
                    </a>
                    <a href="/admin/beitraege/{{ $c['post_id'] }}/bearbeiten"
                       class="text-muted text-decoration-none"
                       style="font-size:.7rem">
                        <i class="bi bi-pencil me-1"></i>Beitrag bearbeiten
                    </a>
                    @else
                    <span class="text-muted small">&mdash;</span>
                    @endif
                </td>

                {{-- Datum --}}
                <td class="py-3 text-muted small text-nowrap">
                    {{ date('d.m.Y', strtotime($c['created_at'])) }}<br>
                    <span style="font-size:.7rem">{{ date('H:i', strtotime($c['created_at'])) }} Uhr</span>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @else
    <div class="text-center py-5 text-muted">
        <i class="bi bi-chat-square-text d-block fs-1 mb-3 opacity-25"></i>
        @if ($search)
            Keine Kommentare f&uuml;r &bdquo;{{ $search }}&ldquo; gefunden.
        @elseif ($filterStatus)
            Keine Kommentare mit Status &bdquo;{{ $filterStatus }}&ldquo; vorhanden.
        @else
            Noch keine Kommentare vorhanden.
        @endif
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
// ── Alle auswählen ─────────────────────────────────────
document.getElementById('selectAll').addEventListener('change', function () {
    var checked = this.checked;
    document.querySelectorAll('.comment-check').forEach(function (cb) { cb.checked = checked; });
    updateBulkInfo();
});
document.querySelectorAll('.comment-check').forEach(function (cb) {
    cb.addEventListener('change', updateBulkInfo);
});

function updateBulkInfo() {
    var checked = document.querySelectorAll('.comment-check:checked');
    var info = document.getElementById('selectedInfo');
    info.style.display = checked.length > 0 ? '' : 'none';
    document.getElementById('selectedCount').textContent = checked.length;
    document.getElementById('bulkIds').value = Array.from(checked).map(function (c) { return c.value; }).join(',');
}

// ── Quick-Edit ─────────────────────────────────────────
function openQuickEdit(id) {
    document.querySelectorAll('.quick-edit-area').forEach(function (el) { el.classList.remove('open'); });
    var el = document.getElementById('qe-' + id);
    if (el) el.classList.add('open');
}
function closeQuickEdit(id) {
    var el = document.getElementById('qe-' + id);
    if (el) el.classList.remove('open');
}
</script>
@endsection

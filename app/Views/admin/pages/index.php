@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Seiten</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Seiten</h1>
        <p class="text-muted small mb-0">{{ count($pages) }} Seiten insgesamt</p>
    </div>
    <a href="/admin/seiten/neu" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Neue Seite
    </a>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

{{-- Filter & Suche --}}
<div class="card mb-4">
    <div class="card-body py-2 d-flex align-items-center gap-2 flex-wrap">
        <a href="/admin/seiten"
           class="btn btn-sm {{ $filterStatus === '' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Alle
        </a>
        <a href="/admin/seiten?status=published"
           class="btn btn-sm {{ $filterStatus === 'published' ? 'btn-success' : 'btn-outline-secondary' }}">
            <i class="bi bi-circle-fill text-success me-1" style="font-size:.4rem;vertical-align:middle"></i>Ver&ouml;ffentlicht
        </a>
        <a href="/admin/seiten?status=draft"
           class="btn btn-sm {{ $filterStatus === 'draft' ? 'btn-secondary' : 'btn-outline-secondary' }}">
            <i class="bi bi-circle-fill text-secondary me-1" style="font-size:.4rem;vertical-align:middle"></i>Entwurf
        </a>
        <a href="/admin/seiten?status=trashed"
           class="btn btn-sm {{ $filterStatus === 'trashed' ? 'btn-danger' : 'btn-outline-secondary' }}">
            <i class="bi bi-circle-fill text-danger me-1" style="font-size:.4rem;vertical-align:middle"></i>Papierkorb
        </a>
        <div class="ms-auto" style="width:240px">
            <input type="text" id="pageSearch" class="form-control form-control-sm"
                   placeholder="Seiten suchen &hellip;" autocomplete="off">
        </div>
    </div>
</div>

{{-- Tabelle --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>URL</th>
                    <th>Template</th>
                    <th>Status</th>
                    <th>Datum</th>
                    <th class="text-end" style="width:100px">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($pages as $page)
                <tr class="page-row" data-search="{{ $page['title'] }}">
                    <td>
                        <a href="/admin/seiten/{{ $page['id'] }}/bearbeiten"
                           class="fw-semibold text-decoration-none text-light"
                           style="display:block;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                           title="{{ $page['title'] }}">
                            {{-- Hierarchie-Einr&uuml;ckung f&uuml;r Unterseiten --}}
                            @if ($page['parent_id'])
                                <i class="bi bi-arrow-return-right text-muted me-1" style="font-size:.8rem"></i>
                            @endif
                            {{ $page['title'] }}
                        </a>
                    </td>
                    <td>
                        <a href="/seite/{{ $page['slug'] }}" target="_blank"
                           class="text-muted small text-decoration-none text-truncate d-inline-block"
                           style="max-width:160px" title="/seite/{{ $page['slug'] }}">
                            /{{ $page['slug'] }}
                        </a>
                    </td>
                    <td>
                        <span style="background:rgba(99,102,241,.18);color:#a5b4fc;border:1px solid rgba(99,102,241,.35);border-radius:6px;padding:.25em .65em;font-size:.72rem;font-weight:600">
                            {{ $page['template'] ?? 'default' }}
                        </span>
                    </td>
                    <td>
                        @if ($page['status'] === 'published')
                            <span class="badge text-success border border-success"
                                  style="background:rgba(25,135,84,.12)">
                                <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>Ver&ouml;ffentlicht
                            </span>
                        @elseif ($page['status'] === 'draft')
                            <span class="badge text-secondary border border-secondary"
                                  style="background:rgba(108,117,125,.12)">
                                <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>Entwurf
                            </span>
                        @else
                            <span class="badge text-danger border border-danger"
                                  style="background:rgba(220,53,69,.12)">
                                <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>Papierkorb
                            </span>
                        @endif
                    </td>
                    <td class="text-muted small text-nowrap">
                        {{ date('d.m.Y', strtotime($page['created_at'])) }}
                    </td>
                    <td>
                        <div class="d-flex gap-1 justify-content-end">
                            @if ($page['status'] === 'published')
                                <a href="/seite/{{ $page['slug'] }}" target="_blank"
                                   class="btn btn-sm btn-outline-secondary" title="Ansehen">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                            <a href="/admin/seiten/{{ $page['id'] }}/bearbeiten"
                               class="btn btn-sm btn-outline-primary" title="Bearbeiten">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST"
                                  action="/admin/seiten/{{ $page['id'] }}"
                                  class="d-inline">
                                <input type="hidden" name="_token" value="{{ $__csrf }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="In Papierkorb"
                                        data-confirm="Seite in den Papierkorb verschieben?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            @if (!$pages)
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-file-earmark d-block fs-1 mb-3 opacity-25"></i>
                        Noch keine Seiten vorhanden.
                        <div class="mt-3">
                            <a href="/admin/seiten/neu" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Erste Seite erstellen
                            </a>
                        </div>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('pageSearch').addEventListener('input', function () {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.page-row').forEach(function (row) {
        row.style.display = row.dataset.search.toLowerCase().indexOf(q) > -1 ? '' : 'none';
    });
});
</script>
@endsection

@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Beitr&auml;ge</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Beitr&auml;ge</h1>
        <p class="text-muted small mb-0">{{ count($posts) }} Eintr&auml;ge</p>
    </div>
    <a href="/admin/beitraege/neu" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Neuer Beitrag
    </a>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

{{-- Status-Filter --}}
<div class="card mb-4">
    <div class="card-body py-2 d-flex align-items-center gap-2 flex-wrap">
        <a href="/admin/beitraege"
           class="btn btn-sm {{ $filterStatus === '' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Alle
        </a>
        <a href="/admin/beitraege?status=published"
           class="btn btn-sm {{ $filterStatus === 'published' ? 'btn-success' : 'btn-outline-secondary' }}">
            <i class="bi bi-circle-fill text-success me-1" style="font-size:.4rem;vertical-align:middle"></i>Ver&ouml;ffentlicht
        </a>
        <a href="/admin/beitraege?status=draft"
           class="btn btn-sm {{ $filterStatus === 'draft' ? 'btn-secondary' : 'btn-outline-secondary' }}">
            <i class="bi bi-circle-fill text-secondary me-1" style="font-size:.4rem;vertical-align:middle"></i>Entwurf
        </a>
        <a href="/admin/beitraege?status=scheduled"
           class="btn btn-sm {{ $filterStatus === 'scheduled' ? 'btn-info' : 'btn-outline-secondary' }}">
            <i class="bi bi-circle-fill text-info me-1" style="font-size:.4rem;vertical-align:middle"></i>Geplant
        </a>
        <a href="/admin/beitraege?status=trashed"
           class="btn btn-sm {{ $filterStatus === 'trashed' ? 'btn-danger' : 'btn-outline-secondary' }}">
            <i class="bi bi-circle-fill text-danger me-1" style="font-size:.4rem;vertical-align:middle"></i>Papierkorb
        </a>
        <div class="ms-auto" style="width:240px">
            <input type="text" id="postSearch" class="form-control form-control-sm"
                   placeholder="Beitr&auml;ge suchen &hellip;" autocomplete="off">
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
                    <th>Autor</th>
                    <th>Status</th>
                    <th>Datum</th>
                    <th class="text-end" style="width:100px">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($posts as $post)
                <tr class="post-row" data-search="{{ $post['title'] }}">
                    <td>
                        <a href="/admin/beitraege/{{ $post['id'] }}/bearbeiten"
                           class="fw-semibold text-decoration-none text-light"
                           style="display:block;max-width:320px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                           title="{{ $post['title'] }}">
                            {{ $post['title'] }}
                        </a>
                        @if ($post['excerpt'])
                        <small class="text-muted"
                               style="display:block;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            {{ $post['excerpt'] }}
                        </small>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $post['author_name'] ?? '&mdash;' }}</td>
                    <td>
                        @if ($post['status'] === 'published')
                            <span class="badge text-success border border-success"
                                  style="background:rgba(25,135,84,.12)">
                                <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>
                                Ver&ouml;ffentlicht
                            </span>
                        @elseif ($post['status'] === 'draft')
                            <span class="badge text-secondary border border-secondary"
                                  style="background:rgba(108,117,125,.12)">
                                <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>
                                Entwurf
                            </span>
                        @elseif ($post['status'] === 'scheduled')
                            <span class="badge text-info border border-info"
                                  style="background:rgba(13,202,240,.12)">
                                <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>
                                Geplant
                            </span>
                        @else
                            <span class="badge text-danger border border-danger"
                                  style="background:rgba(220,53,69,.12)">
                                <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>
                                Papierkorb
                            </span>
                        @endif
                    </td>
                    <td class="text-muted small text-nowrap">
                        {{ date('d.m.Y', strtotime($post['created_at'])) }}
                    </td>
                    <td>
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="/admin/beitraege/{{ $post['id'] }}/bearbeiten"
                               class="btn btn-sm btn-outline-primary" title="Bearbeiten">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST"
                                  action="/admin/beitraege/{{ $post['id'] }}"
                                  class="d-inline">
                                <input type="hidden" name="_token" value="{{ $__csrf }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        title="In Papierkorb"
                                        data-confirm="Beitrag in den Papierkorb verschieben?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            @if (!$posts)
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="bi bi-file-text d-block fs-1 mb-3 opacity-25"></i>
                        Noch keine Beitr&auml;ge vorhanden.
                        <div class="mt-3">
                            <a href="/admin/beitraege/neu" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> Ersten Beitrag erstellen
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
document.getElementById('postSearch').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.post-row').forEach(function (row) {
        row.style.display = row.dataset.search.toLowerCase().indexOf(q) > -1 ? '' : 'none';
    });
});
</script>
@endsection

@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Backup</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Backup</h1>
        <p class="text-muted small mb-0">{{ count($backups) }} Backups vorhanden</p>
    </div>
    <form method="POST" action="/admin/backup/erstellen">
        <input type="hidden" name="_token" value="{{ $__csrf }}">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-cloud-download me-1"></i> Backup erstellen
        </button>
    </form>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Dateiname</th>
                    <th>Gr&ouml;&szlig;e</th>
                    <th>Erstellt</th>
                    <th class="text-end" style="width:120px">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($backups as $backup)
            <tr>
                <td>
                    <i class="bi bi-file-earmark-zip text-warning me-2"></i>
                    <span class="font-monospace small">{{ $backup['name'] }}</span>
                </td>
                <td class="text-muted small">
                    {{ $backup['size'] > 1048576 ? round($backup['size']/1048576, 1) . ' MB' : round($backup['size']/1024, 1) . ' KB' }}
                </td>
                <td class="text-muted small">
                    {{ date('d.m.Y H:i', $backup['date']) }}
                </td>
                <td>
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="/admin/backup/{{ $backup['name'] }}/download"
                           class="btn btn-sm btn-outline-primary" title="Herunterladen">
                            <i class="bi bi-download"></i>
                        </a>
                        <form method="POST" action="/admin/backup/{{ $backup['name'] }}" class="d-inline">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    data-confirm="Backup wirklich l&ouml;schen?"
                                    title="L&ouml;schen">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            @if (!$backups)
            <tr>
                <td colspan="4" class="text-center py-5 text-muted">
                    <i class="bi bi-cloud d-block fs-1 mb-3 opacity-25"></i>
                    Noch keine Backups vorhanden.
                </td>
            </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/admin/logs" class="text-decoration-none">Logs</a></li>
    <li class="breadcrumb-item active">{{ $channel }}</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0 text-capitalize">{{ $channel }}-Log</h1>
        <p class="text-muted small mb-0">{{ count($lines) }} Eintr&auml;ge (letzte 200)</p>
    </div>
    <a href="/admin/logs" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Alle Logs
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        @if ($lines)
        <div style="max-height:600px;overflow-y:auto">
            <table class="table table-hover mb-0" style="font-size:.78rem;font-family:'Cascadia Code','Fira Code',monospace">
                <tbody>
                @foreach ($lines as $line)
                <tr>
                    <td class="py-1 px-3"
                        style="color: {{ str_contains($line, '.ERROR') || str_contains($line, '.CRITICAL') ? '#fca5a5' : (str_contains($line, '.WARNING') ? '#fcd34d' : '#94a3b8') }}">
                        {{ $line }}
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-journal-text d-block fs-1 mb-3 opacity-25"></i>
            Keine Log-Eintr&auml;ge f&uuml;r heute.
        </div>
        @endif
    </div>
</div>
@endsection

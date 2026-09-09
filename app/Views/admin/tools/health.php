@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/admin/werkzeuge" class="text-decoration-none">Werkzeuge</a></li>
    <li class="breadcrumb-item active">Website-Gesundheit</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Website-Gesundheit</h1>
        <p class="text-muted small mb-0">Systempr&uuml;fung und Sicherheitsstatus</p>
    </div>
    {{-- Score --}}
    <div class="text-center">
        <div style="width:72px;height:72px;border-radius:50%;background:conic-gradient(
            {{ $score >= 80 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444') }} {{ $score }}%,
            rgba(255,255,255,.1) 0);
            display:flex;align-items:center;justify-content:center">
            <div style="width:58px;height:58px;border-radius:50%;background:#0f172a;display:flex;align-items:center;justify-content:center">
                <span class="fw-bold" style="color:{{ $score >= 80 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444') }}">
                    {{ $score }}%
                </span>
            </div>
        </div>
        <div class="text-muted small mt-1">Gesundheit</div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Pr&uuml;fpunkt</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($checks as $check)
            <tr>
                <td class="fw-semibold small">{{ $check['label'] }}</td>
                <td>
                    @if ($check['severity'] === 'good')
                        <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                            <i class="bi bi-check-circle-fill me-1"></i> OK
                        </span>
                    @elseif ($check['severity'] === 'warning')
                        <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Warnung
                        </span>
                    @else
                        <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25">
                            <i class="bi bi-x-circle-fill me-1"></i> Kritisch
                        </span>
                    @endif
                </td>
                <td class="text-muted small">{{ $check['detail'] }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

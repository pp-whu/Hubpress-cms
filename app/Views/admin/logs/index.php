@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Logs</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">System-Logs</h1>
</div>

<div class="row g-4">
    @foreach ($channels as $channel)
    <div class="col-12 col-md-6 col-lg-4">
        <a href="/admin/logs/{{ $channel }}" class="text-decoration-none">
            <div class="card" style="transition:all .2s">
                <div class="card-body d-flex align-items-center gap-3">
                    <div style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
                         background:{{ $channel === 'auth' ? 'rgba(251,191,36,.15)' : ($channel === 'app' ? 'rgba(99,102,241,.15)' : 'rgba(52,211,153,.15)') }}">
                        <i class="bi {{ $channel === 'auth' ? 'bi-shield-lock text-warning' : ($channel === 'app' ? 'bi-app text-primary' : 'bi-journal-text text-success') }} fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-semibold text-light text-capitalize">{{ $channel }}</div>
                        <div class="text-muted small">Log-Kanal ansehen</div>
                    </div>
                    <i class="bi bi-chevron-right text-muted ms-auto"></i>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>
@endsection

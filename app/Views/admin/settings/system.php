@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/admin/einstellungen" class="text-decoration-none">Einstellungen</a></li>
    <li class="breadcrumb-item active">Systeminformationen</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">Systeminformationen</h1>
</div>

<div class="row g-4">
    @foreach ($info as $label => $value)
    @if ($label === 'Loaded Extensions')
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-semibold small">Geladene PHP-Erweiterungen</div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    @foreach (explode(', ', $value) as $ext)
                    <span class="badge bg-secondary bg-opacity-15 text-secondary border border-secondary border-opacity-25 small">
                        {{ $ext }}
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body d-flex align-items-center justify-content-between">
                <span class="text-muted small">{{ $label }}</span>
                <span class="fw-semibold small font-monospace">{{ $value }}</span>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>
@endsection

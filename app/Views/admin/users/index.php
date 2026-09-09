@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Benutzer</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Benutzer</h1>
        <p class="text-muted small mb-0">{{ count($users) }} registrierte Benutzer</p>
    </div>
    <a href="/admin/benutzer/neu" class="btn btn-primary btn-sm">
        <i class="bi bi-person-plus me-1"></i> Neuer Benutzer
    </a>
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
                    <th>Benutzer</th>
                    <th>Rolle</th>
                    <th>Status</th>
                    <th>Registriert</th>
                    <th class="text-end" style="width:100px">Aktionen</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($users as $user)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#818cf8);
                                    display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-weight:700;font-size:.85rem">
                            {{ strtoupper(substr($user['username'], 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-semibold small text-light">{{ $user['username'] }}</div>
                            <div class="text-muted" style="font-size:.72rem">{{ $user['email'] }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-25">
                        {{ $user['role'] }}
                    </span>
                </td>
                <td>
                    @if ($user['is_active'])
                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25">
                        <i class="bi bi-circle-fill me-1" style="font-size:.4rem;vertical-align:middle"></i>Aktiv
                    </span>
                    @else
                    <span class="badge bg-secondary bg-opacity-15 text-secondary border border-secondary border-opacity-25">
                        Inaktiv
                    </span>
                    @endif
                </td>
                <td class="text-muted small">
                    {{ date('d.m.Y', strtotime($user['created_at'])) }}
                </td>
                <td>
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="/admin/benutzer/{{ $user['id'] }}"
                           class="btn btn-sm btn-outline-primary" title="Bearbeiten">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="/admin/benutzer/{{ $user['id'] }}" class="d-inline">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    data-confirm="Benutzer wirklich l&ouml;schen?"
                                    title="L&ouml;schen">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            @if (!$users)
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">Keine Benutzer gefunden.</td>
            </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

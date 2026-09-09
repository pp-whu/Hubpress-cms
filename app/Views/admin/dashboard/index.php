@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-0 fw-bold">Dashboard</h1>
        <p class="text-muted small mb-0">Willkommen zurück, {{ $__user['username'] ?? '' }}!</p>
    </div>
    <div class="d-flex gap-2">
        <form method="post" action="/admin/update/install" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm"
                    title="Setze HUBERCMS_UPDATE_URL in .env, falls noch nicht konfiguriert.">
                <i class="bi bi-cloud-arrow-down"></i> Auf neueste Version aktualisieren
            </button>
        </form>
        <a href="/admin/beitraege/neu" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Beitrag erstellen
        </a>
    </div>
</div>

{{-- Stats Grid --}}
<div class="row g-4 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $stats['published_posts'] ?? 0 }}</div>
                    <div class="stat-label">Beiträge</div>
                </div>
                <div class="stat-icon bg-primary bg-opacity-15 text-primary">
                    <i class="bi bi-file-text"></i>
                </div>
            </div>
            <div class="mt-2 small text-muted">{{ $stats['total_posts'] ?? 0 }} gesamt</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $stats['total_pages'] ?? 0 }}</div>
                    <div class="stat-label">Seiten</div>
                </div>
                <div class="stat-icon bg-success bg-opacity-15 text-success">
                    <i class="bi bi-file-earmark"></i>
                </div>
            </div>
            <div class="mt-2 small text-muted">Statische Seiten</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $stats['active_users'] ?? 0 }}</div>
                    <div class="stat-label">Benutzer</div>
                </div>
                <div class="stat-icon bg-warning bg-opacity-15 text-warning">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="mt-2 small text-muted">{{ $stats['total_users'] ?? 0 }} registriert</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value">{{ $stats['pending_comments'] ?? 0 }}</div>
                    <div class="stat-label">Kommentare</div>
                </div>
                <div class="stat-icon bg-danger bg-opacity-15 text-danger">
                    <i class="bi bi-chat-dots"></i>
                </div>
            </div>
            <div class="mt-2 small text-muted">Ausstehend</div>
        </div>
    </div>
</div>

{{-- Recent Activity + System Info --}}
<div class="row g-4">

    {{-- Recent Posts --}}
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Letzte Beiträge</h6>
                <a href="/admin/beitraege" class="btn btn-link btn-sm text-muted p-0">Alle anzeigen</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Titel</th>
                                <th>Autor</th>
                                <th>Status</th>
                                <th>Datum</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($recentPosts as $post)
                            <tr>
                                <td>
                                    <a href="/admin/beitraege/{{ $post['id'] }}/bearbeiten"
                                       class="text-decoration-none text-light text-truncate d-block"
                                       style="max-width:200px">
                                        {{ $post['title'] }}
                                    </a>
                                </td>
                                <td class="text-muted">{{ $post['author'] ?? '—' }}</td>
                                <td>
                                    @if ($post['status'] === 'published')
                                        <span class="badge bg-success">Veröffentlicht</span>
                                    @elseif ($post['status'] === 'draft')
                                        <span class="badge bg-secondary">Entwurf</span>
                                    @else
                                        <span class="badge bg-info">{{ $post['status'] }}</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ date('d.m.Y', strtotime($post['created_at'])) }}
                                </td>
                            </tr>
                        @endforeach
                        @if (!$recentPosts)
                            <tr><td colspan="4" class="text-center text-muted py-4">Keine Beiträge vorhanden.</td></tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- System Info --}}
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">Systeminfo</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    @foreach ($systemInfo as $label => $value)
                    <dt class="col-6 text-muted fw-normal">{{ ucfirst(str_replace('_', ' ', $label)) }}</dt>
                    <dd class="col-6 fw-semibold text-end mb-1">{{ $value }}</dd>
                    @endforeach
                </dl>
            </div>
        </div>

        {{-- Recent Users --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Neue Benutzer</h6>
                <a href="/admin/benutzer" class="btn btn-link btn-sm text-muted p-0">Alle</a>
            </div>
            <ul class="list-group list-group-flush">
                @foreach ($recentUsers as $user)
                <li class="list-group-item bg-transparent border-secondary d-flex align-items-center gap-2 py-2">
                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-25"
                         style="width:32px;height:32px;flex-shrink:0">
                        <span class="fw-bold small text-primary">
                            {{ strtoupper(substr($user['username'], 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold text-truncate small">{{ $user['username'] }}</div>
                        <div class="text-muted text-truncate" style="font-size:.7rem">{{ $user['email'] }}</div>
                    </div>
                    <span class="badge bg-secondary small">{{ $user['role'] }}</span>
                </li>
                @endforeach
                @if (!$recentUsers)
                <li class="list-group-item bg-transparent text-center text-muted py-3 small">Keine Benutzer</li>
                @endif
            </ul>
        </div>
    </div>

</div>
@endsection

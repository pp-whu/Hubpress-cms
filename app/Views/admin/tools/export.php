@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/admin/werkzeuge" class="text-decoration-none">Werkzeuge</a></li>
    <li class="breadcrumb-item active">Exportieren</li>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-0">Exportieren</h1>
    <p class="text-muted small mb-0">Inhalte als JSON-Datei herunterladen</p>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header fw-semibold small">Export-Optionen</div>
            <div class="card-body">
                <form method="POST" action="/admin/werkzeuge/export">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">

                    <div class="mb-4">
                        <label class="form-label">Was soll exportiert werden?</label>

                        <div class="d-flex flex-column gap-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type"
                                       id="expAll" value="all" checked>
                                <label class="form-check-label" for="expAll">
                                    <strong>Alles</strong>
                                    <span class="text-muted small ms-1">Beitr&auml;ge, Seiten, Medien, Benutzer, Einstellungen</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type" id="expPosts" value="posts">
                                <label class="form-check-label" for="expPosts">
                                    Beitr&auml;ge <span class="badge bg-secondary bg-opacity-15 text-secondary">{{ $stats['posts'] }}</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type" id="expPages" value="pages">
                                <label class="form-check-label" for="expPages">
                                    Seiten <span class="badge bg-secondary bg-opacity-15 text-secondary">{{ $stats['pages'] }}</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type" id="expMedia" value="media">
                                <label class="form-check-label" for="expMedia">
                                    Medien-Metadaten <span class="badge bg-secondary bg-opacity-15 text-secondary">{{ $stats['media'] }}</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type" id="expUsers" value="users">
                                <label class="form-check-label" for="expUsers">
                                    Benutzer <span class="badge bg-secondary bg-opacity-15 text-secondary">{{ $stats['users'] }}</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="export_type" id="expSettings" value="settings">
                                <label class="form-check-label" for="expSettings">Einstellungen</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Export herunterladen
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header fw-semibold small">Inhalt-&Uuml;bersicht</div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-6 text-muted fw-normal">Beitr&auml;ge</dt>
                    <dd class="col-6 fw-semibold text-end">{{ $stats['posts'] }}</dd>
                    <dt class="col-6 text-muted fw-normal">Seiten</dt>
                    <dd class="col-6 fw-semibold text-end">{{ $stats['pages'] }}</dd>
                    <dt class="col-6 text-muted fw-normal">Kommentare</dt>
                    <dd class="col-6 fw-semibold text-end">{{ $stats['comments'] }}</dd>
                    <dt class="col-6 text-muted fw-normal">Medien</dt>
                    <dd class="col-6 fw-semibold text-end">{{ $stats['media'] }}</dd>
                    <dt class="col-6 text-muted fw-normal">Benutzer</dt>
                    <dd class="col-6 fw-semibold text-end">{{ $stats['users'] }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

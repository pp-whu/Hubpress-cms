@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/admin/werkzeuge" class="text-decoration-none">Werkzeuge</a></li>
    <li class="breadcrumb-item active">Pers. Daten exportieren</li>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-0">Pers&ouml;nliche Daten exportieren</h1>
    <p class="text-muted small mb-0">DSGVO Art. 20 &mdash; Recht auf Daten&uuml;bertragbarkeit</p>
</div>

@if ($__flash['error'])
<div class="alert alert-danger border-0 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $__flash['error'] }}
</div>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header fw-semibold small">E-Mail-Adresse eingeben</div>
            <div class="card-body">
                <p class="text-muted small mb-4">
                    Gib die E-Mail-Adresse des Benutzers ein, dessen Daten exportiert werden sollen.
                    Die Daten werden als JSON-Datei heruntergeladen.
                </p>
                <form method="POST" action="/admin/werkzeuge/export-data">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">
                    <div class="mb-3">
                        <label class="form-label small">E-Mail-Adresse</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="nutzer@example.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Daten exportieren
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header fw-semibold small">Was wird exportiert?</div>
            <div class="card-body">
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="py-1 border-bottom border-secondary">
                        <i class="bi bi-check text-success me-2"></i>Benutzer-Profildaten (Name, E-Mail, Rolle)
                    </li>
                    <li class="py-1 border-bottom border-secondary">
                        <i class="bi bi-check text-success me-2"></i>Kommentare des Benutzers
                    </li>
                    <li class="py-1">
                        <i class="bi bi-check text-success me-2"></i>Erstellungsdatum des Kontos
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

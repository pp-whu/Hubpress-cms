@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/admin/werkzeuge" class="text-decoration-none">Werkzeuge</a></li>
    <li class="breadcrumb-item active">Pers. Daten l&ouml;schen</li>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-0">Pers&ouml;nliche Daten l&ouml;schen</h1>
    <p class="text-muted small mb-0">DSGVO Art. 17 &mdash; Recht auf L&ouml;schung</p>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif
@if ($__flash['error'])
<div class="alert alert-danger border-0 mb-4">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $__flash['error'] }}
</div>
@endif

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="alert border-0"
             style="background:rgba(239,68,68,.1);color:#fca5a5;border:1px solid rgba(239,68,68,.25)!important">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Achtung:</strong> Diese Aktion anonymisiert alle pers&ouml;nlichen Daten des Benutzers
            unwiderruflich. Der Benutzer-Account bleibt erhalten, aber Name, E-Mail und Profildaten
            werden gel&ouml;scht.
        </div>

        <div class="card">
            <div class="card-header fw-semibold small">Daten anonymisieren</div>
            <div class="card-body">
                <form method="POST" action="/admin/werkzeuge/delete-data">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">
                    <div class="mb-3">
                        <label class="form-label small">E-Mail-Adresse des Benutzers</label>
                        <input type="email" name="email" class="form-control"
                               placeholder="nutzer@example.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small">
                            Best&auml;tigung: Gib <code>LOESCHEN</code> ein
                        </label>
                        <input type="text" name="confirm" class="form-control"
                               placeholder="LOESCHEN" autocomplete="off" required>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-person-x me-1"></i> Daten unwiderruflich l&ouml;schen
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header fw-semibold small">Was wird anonymisiert?</div>
            <div class="card-body">
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="py-1 border-bottom border-secondary">
                        <i class="bi bi-x text-danger me-2"></i>E-Mail-Adresse (ersetzt durch anonyme Adresse)
                    </li>
                    <li class="py-1 border-bottom border-secondary">
                        <i class="bi bi-x text-danger me-2"></i>Vor- und Nachname
                    </li>
                    <li class="py-1 border-bottom border-secondary">
                        <i class="bi bi-x text-danger me-2"></i>Profilbild und Biografie
                    </li>
                    <li class="py-1">
                        <i class="bi bi-x text-danger me-2"></i>Autor-Name in Kommentaren
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="/admin/werkzeuge" class="text-decoration-none">Werkzeuge</a></li>
    <li class="breadcrumb-item active">Importieren</li>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-0">Importieren</h1>
    <p class="text-muted small mb-0">Inhalte aus anderen Quellen importieren</p>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header fw-semibold small">
                <i class="bi bi-filetype-json me-2"></i>HuberCMS JSON-Import
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Importiere eine JSON-Datei, die zuvor mit dem HuberCMS-Export-Werkzeug erstellt wurde.
                </p>
                <form method="POST" action="/admin/werkzeuge/import"
                      enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ $__csrf }}">
                    <input type="hidden" name="import_type" value="json">
                    <div class="mb-3">
                        <label class="form-label small">JSON-Datei ausw&auml;hlen</label>
                        <input type="file" name="import_file"
                               class="form-control form-control-sm"
                               accept=".json" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-1"></i> Importieren
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header fw-semibold small">
                <i class="bi bi-wordpress me-2"></i>WordPress-Import
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Importiere Beitr&auml;ge und Seiten aus einem WordPress WXR-Export.
                </p>
                <div class="alert border-0 py-2 small"
                     style="background:rgba(245,158,11,.1);color:#fbbf24;border:1px solid rgba(245,158,11,.2)!important">
                    <i class="bi bi-info-circle me-1"></i>
                    In Vorbereitung &mdash; kommt in einer zuk&uuml;nftigen Version.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

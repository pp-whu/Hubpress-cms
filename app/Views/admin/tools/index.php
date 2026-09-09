@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Werkzeuge</li>
@endsection

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-0">Werkzeuge</h1>
    <p class="text-muted small mb-0">Verwaltungswerkzeuge f&uuml;r HuberCMS</p>
</div>

<div class="row g-4">
    <div class="col-12 col-md-6 col-lg-4">
        <a href="/admin/werkzeuge/import" class="text-decoration-none">
            <div class="card h-100" style="transition:all .2s">
                <div class="card-body d-flex gap-3 align-items-start">
                    <div style="width:44px;height:44px;border-radius:10px;background:rgba(99,102,241,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-box-arrow-in-down text-primary fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-1 text-light">Importieren</h5>
                        <p class="text-muted small mb-0">Inhalte aus anderen CMS oder Dateiformaten importieren.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
        <a href="/admin/werkzeuge/export" class="text-decoration-none">
            <div class="card h-100" style="transition:all .2s">
                <div class="card-body d-flex gap-3 align-items-start">
                    <div style="width:44px;height:44px;border-radius:10px;background:rgba(16,185,129,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-box-arrow-up text-success fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-1 text-light">Exportieren</h5>
                        <p class="text-muted small mb-0">Beitr&auml;ge, Seiten, Medien und Einstellungen als JSON exportieren.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
        <a href="/admin/werkzeuge/site-health" class="text-decoration-none">
            <div class="card h-100" style="transition:all .2s">
                <div class="card-body d-flex gap-3 align-items-start">
                    <div style="width:44px;height:44px;border-radius:10px;background:rgba(245,158,11,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-heart-pulse text-warning fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-1 text-light">Website-Gesundheit</h5>
                        <p class="text-muted small mb-0">Systempr&uuml;fung: PHP, Erweiterungen, Berechtigungen, Sicherheit.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
        <a href="/admin/werkzeuge/export-data" class="text-decoration-none">
            <div class="card h-100" style="transition:all .2s">
                <div class="card-body d-flex gap-3 align-items-start">
                    <div style="width:44px;height:44px;border-radius:10px;background:rgba(59,130,246,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-person-lines-fill text-info fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-1 text-light">Pers. Daten exportieren</h5>
                        <p class="text-muted small mb-0">DSGVO: Alle gespeicherten Daten eines Nutzers exportieren.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-md-6 col-lg-4">
        <a href="/admin/werkzeuge/delete-data" class="text-decoration-none">
            <div class="card h-100" style="transition:all .2s">
                <div class="card-body d-flex gap-3 align-items-start">
                    <div style="width:44px;height:44px;border-radius:10px;background:rgba(239,68,68,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="bi bi-person-x text-danger fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-1 text-light">Pers. Daten l&ouml;schen</h5>
                        <p class="text-muted small mb-0">DSGVO: Pers&ouml;nliche Daten eines Nutzers anonymisieren.</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection

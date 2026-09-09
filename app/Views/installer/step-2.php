<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datenbank konfigurieren — HuberCMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: radial-gradient(ellipse at top,#1e1b4b 0%,#0f172a 60%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .installer-card { background: rgba(30,41,59,0.8); backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,0.07); border-radius:16px; padding:2.5rem; width:100%; max-width:560px; box-shadow:0 32px 64px rgba(0,0,0,0.5); }
        .installer-logo { font-size:1.5rem; font-weight:800; background:linear-gradient(135deg,#818cf8,#6366f1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .step-bar { display:flex; gap:0.5rem; margin-bottom:2rem; }
        .step-bar .step { flex:1; height:4px; border-radius:2px; background:rgba(255,255,255,0.1); }
        .step-bar .step.done { background:#6366f1; }
        .step-bar .step.active { background:linear-gradient(90deg,#6366f1,#818cf8); }
        .form-control { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); color:#e2e8f0; }
        .form-control:focus { background:rgba(255,255,255,0.08); border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,0.15); color:#e2e8f0; }
        .form-label { font-size:.8rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; }
    </style>
</head>
<body>
<div class="installer-card">
    <div class="text-center mb-4">
        <div class="installer-logo">HuberCMS</div>
        <p class="text-muted mt-1 small">Installationsassistent — Schritt 2 von 3</p>
    </div>

    <div class="step-bar">
        <div class="step done"></div>
        <div class="step done"></div>
        <div class="step active"></div>
        <div class="step"></div>
    </div>

    @if ($__flash['error'])
        <div class="alert alert-danger border-0 small mb-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $__flash['error'] }}
        </div>
    @endif

    <h5 class="fw-bold mb-1">Datenbank konfigurieren</h5>
    <p class="text-muted small mb-4">MariaDB / MySQL Verbindungsdaten eingeben.</p>

    <form method="POST" action="/install/process">
        <input type="hidden" name="_token" value="{{ $__csrf }}">
        <input type="hidden" name="step" value="2">

        <div class="row g-3">
            <div class="col-8">
                <label class="form-label">Datenbank-Host</label>
                <input type="text" name="db_host" class="form-control" value="db" placeholder="127.0.0.1" required>
            </div>
            <div class="col-4">
                <label class="form-label">Port</label>
                <input type="number" name="db_port" class="form-control" value="3306" required>
            </div>
            <div class="col-12">
                <label class="form-label">Datenbank</label>
                <input type="text" name="db_database" class="form-control" value="db" placeholder="db" required>
            </div>
            <div class="col-6">
                <label class="form-label">Benutzername</label>
                <input type="text" name="db_username" class="form-control" value="db" required>
            </div>
            <div class="col-6">
                <label class="form-label">Passwort</label>
                <input type="password" name="db_password" class="form-control" value="db" autocomplete="new-password">
            </div>
            <div class="col-12">
                <label class="form-label">Tabellen-Präfix</label>
                <input type="text" name="db_prefix" class="form-control" value="hcms_" pattern="[a-zA-Z0-9_]+" required>
                <div class="form-text text-muted">Nur Buchstaben, Zahlen und Unterstriche.</div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <a href="/install" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Zurück
            </a>
            <button type="submit" class="btn btn-primary flex-grow-1 fw-semibold">
                Verbindung testen & weiter <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>
</body>
</html>

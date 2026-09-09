<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation abgeschlossen — HuberCMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background:radial-gradient(ellipse at top,#1e1b4b 0%,#0f172a 60%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .installer-card { background:rgba(30,41,59,0.8); backdrop-filter:blur(16px); border:1px solid rgba(255,255,255,0.07); border-radius:16px; padding:2.5rem; width:100%; max-width:480px; box-shadow:0 32px 64px rgba(0,0,0,0.5); text-align:center; }
        .success-icon { width:80px; height:80px; border-radius:50%; background:rgba(16,185,129,0.15); border:2px solid rgba(16,185,129,0.3); display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; animation:pop 0.5s cubic-bezier(0.34,1.56,0.64,1); }
        @keyframes pop { from { transform:scale(0); opacity:0; } to { transform:scale(1); opacity:1; } }
    </style>
</head>
<body>
<div class="installer-card">
    <div class="success-icon">
        <i class="bi bi-check-lg text-success" style="font-size:2.5rem"></i>
    </div>

    <h4 class="fw-bold mb-2">Installation erfolgreich! 🎉</h4>
    <p class="text-muted mb-4">
        HuberCMS wurde erfolgreich installiert und konfiguriert.<br>
        Du kannst dich jetzt im Admin-Panel anmelden.
    </p>

    <div class="d-grid gap-3">
        <a href="/admin" class="btn btn-primary fw-semibold">
            <i class="bi bi-speedometer2 me-1"></i> Zum Admin-Panel
        </a>
        <a href="/" class="btn btn-outline-secondary">
            <i class="bi bi-house me-1"></i> Website ansehen
        </a>
    </div>

    <hr class="border-secondary my-4">
    <p class="text-muted small mb-0">
        <i class="bi bi-shield-check text-success me-1"></i>
        Bitte lösche den <code>/install</code>-Ordner nach der Installation aus Sicherheitsgründen.
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>
</body>
</html>

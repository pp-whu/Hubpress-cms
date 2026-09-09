<!DOCTYPE html>
<html lang="{{ $__locale }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Registrierung geschlossen &mdash; {{ $__appName }}</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <style>
        body { background:radial-gradient(ellipse at 30% 40%,#312e81 0%,#0f172a 60%);min-height:100vh;display:flex;align-items:center;justify-content:center; }
        .auth-card { background:rgba(30,41,59,.85);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:2.5rem;width:100%;max-width:420px;text-align:center;box-shadow:0 32px 80px rgba(0,0,0,.5); }
        .auth-brand { font-size:1.6rem;font-weight:800;background:linear-gradient(135deg,#818cf8,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="auth-brand mb-3">{{ $__appName }}</div>
    <i class="bi bi-lock-fill fs-1 text-warning mb-3 d-block"></i>
    <h5 class="fw-bold text-light mb-2">Registrierung geschlossen</h5>
    <p class="text-muted small mb-4">
        Die Registrierung ist derzeit deaktiviert.<br>
        Wende dich an den Administrator.
    </p>
    <a href="/login" class="btn btn-outline-primary btn-sm">
        Zum Login
    </a>
</div>
</body>
</html>

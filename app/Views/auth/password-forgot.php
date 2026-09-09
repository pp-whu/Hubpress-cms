<!DOCTYPE html>
<html lang="{{ $__locale }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Passwort vergessen &mdash; {{ $__appName }}</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: radial-gradient(ellipse at 30% 40%, #312e81 0%, #0f172a 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: rgba(30,41,59,.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 32px 80px rgba(0,0,0,.5);
        }
        .auth-brand { font-size:1.6rem;font-weight:800;background:linear-gradient(135deg,#818cf8,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text; }
        .form-control { background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#e2e8f0;border-radius:10px;padding:.65rem 1rem; }
        .form-control:focus { background:rgba(255,255,255,.08);border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.2);color:#e2e8f0; }
        .form-control::placeholder { color:#64748b; }
        .btn-send { background:linear-gradient(135deg,#6366f1,#4f46e5);border:none;border-radius:10px;padding:.7rem;font-weight:600;transition:all .2s; }
        .btn-send:hover { transform:translateY(-1px);box-shadow:0 8px 24px rgba(99,102,241,.4); }
        .divider { border-color:rgba(255,255,255,.08); }
        .input-group-text { background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px 0 0 10px;color:#64748b; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <div class="auth-brand">{{ $__appName }}</div>
        <p class="text-muted small mt-1">Passwort zur&uuml;cksetzen</p>
    </div>

    @if ($__flash['success'])
        <div class="alert alert-success border-0 py-2 small mb-3">
            <i class="bi bi-check-circle-fill me-1"></i> {{ $__flash['success'] }}
        </div>
    @endif
    @if ($__flash['error'])
        <div class="alert alert-danger border-0 py-2 small mb-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $__flash['error'] }}
        </div>
    @endif

    <p class="text-muted small mb-4">
        Gib deine E-Mail-Adresse ein. Wir senden dir einen Link zum Zur&uuml;cksetzen deines Passworts.
    </p>

    <form method="POST" action="/password/forgot" novalidate>
        <input type="hidden" name="_token" value="{{ $__csrf }}">

        <div class="mb-4">
            <label for="email" class="form-label text-muted small fw-semibold">E-MAIL-ADRESSE</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control"
                       style="border-radius:0 10px 10px 0"
                       placeholder="deine@email.de"
                       autocomplete="email"
                       required>
            </div>
        </div>

        <button type="submit" class="btn btn-send btn-primary w-100 text-white">
            <i class="bi bi-send me-1"></i> Link senden
        </button>
    </form>

    <hr class="divider my-4">
    <p class="text-center text-muted small mb-0">
        <a href="/login" class="text-primary text-decoration-none">
            <i class="bi bi-arrow-left me-1"></i> Zur&uuml;ck zum Login
        </a>
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>
</body>
</html>

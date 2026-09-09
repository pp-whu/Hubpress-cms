<!DOCTYPE html>
<html lang="{{ $__locale }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $title ?? 'Anmelden' }} — {{ $__appName }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: radial-gradient(ellipse at 30% 40%, #312e81 0%, #0f172a 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: rgba(30,41,59,0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5);
        }
        .auth-brand {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #818cf8, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .form-control {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #e2e8f0;
            border-radius: 10px;
            padding: 0.65rem 1rem;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.08);
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
            color: #e2e8f0;
        }
        .form-control::placeholder { color: #64748b; }
        .btn-login {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            border-radius: 10px;
            padding: 0.7rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(99,102,241,0.4);
        }
        .divider { border-color: rgba(255,255,255,0.08); }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <div class="auth-brand">{{ $__appName }}</div>
        <p class="text-muted small mt-1">Melde dich an, um fortzufahren</p>
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

    <form method="POST" action="/login" novalidate>
        <input type="hidden" name="_token" value="{{ $__csrf }}">
        <input type="hidden" name="redirect" value="{{ $redirect ?? '/admin' }}">

        <div class="mb-3">
            <label for="email" class="form-label text-muted small fw-semibold">E-MAIL-ADRESSE</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 border" style="border-color:rgba(255,255,255,0.1);border-radius:10px 0 0 10px">
                    <i class="bi bi-envelope text-muted"></i>
                </span>
                <input type="email"
                       id="email"
                       name="email"
                       class="form-control border-start-0"
                       style="border-radius:0 10px 10px 0"
                       placeholder="deine@email.de"
                       autocomplete="email"
                       required>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label text-muted small fw-semibold">PASSWORT</label>
                <a href="/password/forgot" class="text-muted small text-decoration-none">Vergessen?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 border" style="border-color:rgba(255,255,255,0.1);border-radius:10px 0 0 10px">
                    <i class="bi bi-lock text-muted"></i>
                </span>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control border-start-0"
                       style="border-radius:0 10px 10px 0"
                       placeholder="••••••••"
                       autocomplete="current-password"
                       required>
            </div>
        </div>

        <div class="mb-4 d-flex align-items-center justify-content-between">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label text-muted small" for="remember">Angemeldet bleiben</label>
            </div>
        </div>

        <button type="submit" class="btn btn-login btn-primary w-100 text-white">
            Anmelden <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <hr class="divider my-4">

    <p class="text-center text-muted small mb-0">
        Noch kein Konto?
        <a href="/register" class="text-primary text-decoration-none fw-semibold">Registrieren</a>
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>
</body>
</html>

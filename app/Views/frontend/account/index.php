<!DOCTYPE html>
<html lang="{{ $__locale }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Konto &mdash; {{ $__appName }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background:#0f172a; color:#e2e8f0; font-family:system-ui,sans-serif; }
        .navbar { background:rgba(13,17,23,.95); backdrop-filter:blur(12px); border-bottom:1px solid rgba(255,255,255,.06); }
        .navbar-brand { font-weight:800; background:linear-gradient(135deg,#818cf8,#6366f1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .card { background:rgba(30,41,59,.8); border:1px solid rgba(255,255,255,.07); border-radius:12px; }
        footer { border-top:1px solid rgba(255,255,255,.07); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="/">{{ $__appName }}</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="/">Startseite</a></li>
                <li class="nav-item"><a class="nav-link" href="/blog">Blog</a></li>
                <li class="nav-item"><a class="nav-link active" href="/mein-konto">Mein Konto</a></li>
                @if ($__user && ($__user['role'] === 'admin' || $__user['role'] === 'super_admin' || $__user['role'] === 'editor'))
                <li class="nav-item">
                    <a class="btn btn-outline-primary btn-sm ms-2" href="/admin">
                        <i class="bi bi-speedometer2 me-1"></i> Admin
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<main class="container py-5">
    <div class="row g-4">

        {{-- Sidebar --}}
        <div class="col-12 col-lg-3">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div class="mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#818cf8);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700;color:#fff;box-shadow:0 4px 20px rgba(99,102,241,.4)">
                        {{ $__user ? strtoupper(substr($__user['username'], 0, 1)) : 'A' }}
                    </div>
                    <div class="fw-bold fs-6" style="color:#f1f5f9">{{ $__user['username'] ?? '' }}</div>
                    <div class="mt-1 mb-2" style="color:#94a3b8;font-size:.82rem;word-break:break-all">{{ $__user['email'] ?? '' }}</div>
                    <span style="background:rgba(99,102,241,.25);color:#a5b4fc;border:1px solid rgba(99,102,241,.5);border-radius:6px;padding:.3em .8em;font-size:.75rem;font-weight:600">
                        {{ $__user['role'] ?? '' }}
                    </span>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#profil"
                       class="list-group-item list-group-item-action bg-transparent border-secondary active"
                       onclick="showSection('profil')">
                        <i class="bi bi-person me-2"></i> Profil
                    </a>
                    <a href="#passwort"
                       class="list-group-item list-group-item-action bg-transparent border-secondary"
                       onclick="showSection('passwort')">
                        <i class="bi bi-lock me-2"></i> Passwort &auml;ndern
                    </a>
                    {{-- Abmelden als POST-Form --}}
                    <form method="POST" action="/logout">
                        <input type="hidden" name="_token" value="{{ $__csrf }}">
                        <button type="submit"
                                class="list-group-item list-group-item-action bg-transparent border-secondary text-danger w-100 text-start border-0"
                                style="border-radius:0;cursor:pointer">
                            <i class="bi bi-box-arrow-right me-2"></i> Abmelden
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Hauptbereich (Profil + Passwort) --}}
        <div class="col-12 col-lg-9">

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

            {{-- Profil --}}
            <div id="section-profil">
                <div class="card mb-4">
                    <div class="card-header fw-semibold">
                        <i class="bi bi-person me-2"></i>Profil
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">Benutzername</label>
                                <input type="text" class="form-control" value="{{ $__user['username'] ?? '' }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">E-Mail</label>
                                <input type="email" class="form-control" value="{{ $__user['email'] ?? '' }}" disabled>
                            </div>
                        </div>
                        <div class="mt-3 text-muted small">
                            <i class="bi bi-info-circle me-1"></i>
                            &Auml;nderungen im <a href="/admin/profil" class="text-primary">Admin-Panel</a>.
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header fw-semibold">
                        <i class="bi bi-shield-check me-2"></i>Konto-Informationen
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted fw-normal">Benutzername</dt>
                            <dd class="col-7 fw-semibold">{{ $__user['username'] ?? '&mdash;' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Rolle</dt>
                            <dd class="col-7 fw-semibold">{{ $__user['role'] ?? '&mdash;' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Passwort ändern --}}
            <div id="section-passwort" style="display:none">
                <div class="card">
                    <div class="card-header fw-semibold">
                        <i class="bi bi-lock me-2"></i>Passwort &auml;ndern
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/mein-konto/passwort">
                            <input type="hidden" name="_token" value="{{ $__csrf }}">
                            <div class="mb-3">
                                <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">
                                    Aktuelles Passwort
                                </label>
                                <input type="password" name="current_password" class="form-control"
                                       placeholder="Aktuelles Passwort" autocomplete="current-password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">
                                    Neues Passwort
                                </label>
                                <input type="password" name="password" class="form-control"
                                       placeholder="Mindestens 8 Zeichen" minlength="8"
                                       autocomplete="new-password" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small text-muted text-uppercase" style="letter-spacing:.05em">
                                    Neues Passwort best&auml;tigen
                                </label>
                                <input type="password" name="password_confirmation" class="form-control"
                                       placeholder="Passwort wiederholen"
                                       autocomplete="new-password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-lock-fill me-1"></i> Passwort &auml;ndern
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<footer class="py-4 text-center text-muted small">
    <div class="container">&copy; {{ date('Y') }} {{ $__appName }}</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>
<script>
function showSection(name) {
    ['profil', 'passwort'].forEach(function(s) {
        var el = document.getElementById('section-' + s);
        if (el) el.style.display = (s === name) ? '' : 'none';
    });
    document.querySelectorAll('.list-group-item').forEach(function(el) {
        el.classList.remove('active');
    });
    var active = document.querySelector('[onclick="showSection(\'' + name + '\')"]');
    if (active) active.classList.add('active');
}
</script>
</body>
</html>

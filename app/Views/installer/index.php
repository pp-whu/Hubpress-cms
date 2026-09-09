<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Installation' }} — HuberCMS</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: radial-gradient(ellipse at top, #1e1b4b 0%, #0f172a 60%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .installer-card {
            background: rgba(30,41,59,0.8);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 580px;
            box-shadow: 0 32px 64px rgba(0,0,0,0.5);
        }
        .installer-logo {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #818cf8, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .step-bar { display: flex; gap: 0.5rem; margin-bottom: 2rem; }
        .step-bar .step {
            flex: 1;
            height: 4px;
            border-radius: 2px;
            background: rgba(255,255,255,0.1);
        }
        .step-bar .step.done { background: #6366f1; }
        .step-bar .step.active { background: linear-gradient(90deg, #6366f1, #818cf8); }
        .check-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.625rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .check-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
<div class="installer-card">
    <div class="text-center mb-4">
        <div class="installer-logo">HuberCMS</div>
        <p class="text-muted mt-1">Installationsassistent</p>
    </div>

    <div class="step-bar">
        <div class="step done"></div>
        <div class="step active"></div>
        <div class="step"></div>
        <div class="step"></div>
    </div>

    <h5 class="fw-bold mb-3">Systemvoraussetzungen</h5>

    <div class="mb-4">
        @foreach ($checks as $check)
        <div class="check-item">
            <span class="{{ $check['passed'] ? 'text-light' : 'text-danger' }}">
                {{ $check['label'] }}
            </span>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted">{{ $check['detail'] }}</small>
                @if ($check['passed'])
                    <i class="bi bi-check-circle-fill text-success"></i>
                @else
                    <i class="bi bi-x-circle-fill text-danger"></i>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    @if ($allPassed)
        <a href="/install/step/2" class="btn btn-primary w-100 fw-semibold">
            Weiter <i class="bi bi-arrow-right ms-1"></i>
        </a>
    @else
        <div class="alert alert-danger border-0 small">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Bitte behebe die rot markierten Anforderungen und lade diese Seite neu.
        </div>
        <button class="btn btn-outline-secondary w-100" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i> Erneut prüfen
        </button>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmzDv3v/KTfEcxgLdKqXo2+QNXy"
        crossorigin="anonymous"></script>
</body>
</html>

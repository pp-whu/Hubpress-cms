@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Einstellungen</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h4 fw-bold mb-0">Einstellungen</h1>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

<form method="POST" action="/admin/einstellungen">
    <input type="hidden" name="_token" value="{{ $__csrf }}">
    <input type="hidden" name="group" value="general">

    <div class="row g-4">
        <div class="col-12 col-lg-7">

            <div class="card mb-4">
                <div class="card-header fw-semibold small">
                    <i class="bi bi-gear me-2"></i>Allgemein
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Website-Name</label>
                        <input type="text" name="site_name" class="form-control"
                               value="{{ $settings['general']['site_name'] ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Website-Beschreibung</label>
                        <textarea name="site_description" class="form-control" rows="2">{{ $settings['general']['site_description'] ?? '' }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin E-Mail</label>
                        <input type="email" name="admin_email" class="form-control"
                               value="{{ $settings['general']['admin_email'] ?? '' }}">
                    </div>
                    <div class="mb-0">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox"
                                   name="registration_open" value="1"
                                   id="regOpen"
                                   {{ ($settings['general']['registration_open'] ?? '1') === '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="regOpen">
                                Registrierung erlauben
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-12 col-lg-5">
            <div class="card">
                <div class="card-header fw-semibold small">
                    <i class="bi bi-info-circle me-2"></i>System-Info
                </div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-6 text-muted fw-normal">CMS-Version</dt>
                        <dd class="col-6 fw-semibold text-end">{{ $__version }}</dd>
                        <dt class="col-6 text-muted fw-normal">PHP-Version</dt>
                        <dd class="col-6 fw-semibold text-end">{{ PHP_VERSION }}</dd>
                        <dt class="col-6 text-muted fw-normal">Umgebung</dt>
                        <dd class="col-6 fw-semibold text-end">{{ $_ENV['APP_ENV'] ?? 'production' }}</dd>
                    </dl>
                    <hr class="border-secondary">
                    <a href="/admin/einstellungen/system" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-cpu me-1"></i> Systeminformationen
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i> Einstellungen speichern
        </button>
    </div>
</form>
@endsection

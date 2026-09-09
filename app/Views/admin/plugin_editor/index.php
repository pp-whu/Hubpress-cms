@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">Plugin-Editor</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">Plugin-Editor</h1>
        <p class="text-muted small mb-0">
            <i class="bi bi-exclamation-triangle text-warning me-1"></i>
            Direktes Bearbeiten von Plugin-Dateien &mdash; Vorsicht bei &Auml;nderungen.
        </p>
    </div>
</div>

<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-file-code d-block fs-1 mb-3 opacity-25"></i>
        <p class="mb-3">Plugin-Editor ist in Vorbereitung.</p>
        <a href="/admin/plugins" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Zur&uuml;ck zu Plugins
        </a>
    </div>
</div>
@endsection

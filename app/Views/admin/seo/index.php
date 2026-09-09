@extends('admin.layouts.main')

@section('breadcrumb')
    <li class="breadcrumb-item active">SEO</li>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-bold mb-0">SEO</h1>
        <p class="text-muted small mb-0">Suchmaschinen-Optimierung &amp; Meta-Einstellungen</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/seo/sitemap" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-diagram-3 me-1"></i> Sitemap ansehen
        </a>
    </div>
</div>

@if ($__flash['success'])
<div class="alert alert-success border-0 mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>{{ $__flash['success'] }}
</div>
@endif

<form method="POST" action="/admin/seo">
    <input type="hidden" name="_token" value="{{ $__csrf }}">
    <input type="hidden" name="group" value="seo">

    <div class="row g-4">
        {{-- Meta-Einstellungen --}}
        <div class="col-12 col-lg-7">

            <div class="card mb-4">
                <div class="card-header fw-semibold small">
                    <i class="bi bi-search me-2"></i>Meta-Tags
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Meta-Titel Format</label>
                        <input type="text" name="meta_title_format"
                               class="form-control"
                               value="{{ $seo['meta_title_format'] ?? '%s | HuberCMS' }}"
                               placeholder="%s | Mein CMS">
                        <div class="form-text text-muted">
                            <code>%s</code> wird durch den Seitentitel ersetzt.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Standard Meta-Beschreibung</label>
                        <textarea name="meta_description_default"
                                  class="form-control" rows="3"
                                  placeholder="Standard-Beschreibung wenn keine seitenspezifische vorhanden ist&hellip;">{{ $seo['meta_description_default'] ?? '' }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Google Analytics ID</label>
                        <input type="text" name="google_analytics_id"
                               class="form-control"
                               value="{{ $seo['google_analytics_id'] ?? '' }}"
                               placeholder="G-XXXXXXXXXX">
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header fw-semibold small">
                    <i class="bi bi-robot me-2"></i>robots.txt
                </div>
                <div class="card-body">
                    <textarea name="robots_txt"
                              class="form-control font-monospace"
                              rows="6"
                              style="font-size:.85rem">{{ $seo['robots_txt'] ?? "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /api/\n\nSitemap: /sitemap.xml" }}</textarea>
                </div>
            </div>

        </div>

        {{-- Sitemap & Social --}}
        <div class="col-12 col-lg-5">

            <div class="card mb-4">
                <div class="card-header fw-semibold small">
                    <i class="bi bi-diagram-3 me-2"></i>XML-Sitemap
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Die Sitemap wird automatisch aus Beitr&auml;gen und Seiten generiert.
                    </p>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm"
                               value="/sitemap.xml" readonly>
                        <a href="/admin/seo/sitemap" target="_blank"
                           class="btn btn-outline-primary btn-sm text-nowrap">
                            <i class="bi bi-eye me-1"></i> Ansehen
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header fw-semibold small">
                    <i class="bi bi-share me-2"></i>Open Graph / Social
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small">Standard OG-Bild URL</label>
                        <input type="text" name="og_default_image"
                               class="form-control form-control-sm"
                               value="{{ $seo['og_default_image'] ?? '' }}"
                               placeholder="/assets/images/og-default.jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Twitter/X Card-Typ</label>
                        <select name="twitter_card" class="form-select form-select-sm">
                            <option value="summary" {{ ($seo['twitter_card'] ?? 'summary') === 'summary' ? 'selected' : '' }}>summary</option>
                            <option value="summary_large_image" {{ ($seo['twitter_card'] ?? '') === 'summary_large_image' ? 'selected' : '' }}>summary_large_image</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Twitter/X Handle</label>
                        <input type="text" name="twitter_handle"
                               class="form-control form-control-sm"
                               value="{{ $seo['twitter_handle'] ?? '' }}"
                               placeholder="@handle">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold small">
                    <i class="bi bi-google me-2"></i>Google Search Console
                </div>
                <div class="card-body">
                    <label class="form-label small">Verifikations-Meta-Tag</label>
                    <input type="text" name="google_search_console"
                           class="form-control form-control-sm"
                           value="{{ $seo['google_search_console'] ?? '' }}"
                           placeholder="google-site-verification=...">
                </div>
            </div>

        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy me-1"></i> SEO-Einstellungen speichern
        </button>
    </div>
</form>
@endsection

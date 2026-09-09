# HuberCMS

**Modern Open-Source CMS built with PHP 8.4**

[![PHP](https://img.shields.io/badge/PHP-8.4-blue?logo=php)](https://php.net)
[![MariaDB](https://img.shields.io/badge/MariaDB-11.8-orange?logo=mariadb)](https://mariadb.org)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple?logo=bootstrap)](https://getbootstrap.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## Features

- **Custom MVC Framework** — Router, DI Container, Template Engine, ORM
- **Admin Panel** — Modern dark-mode UI, Dashboard, Users, Posts, Pages, Media, SEO, Plugins, Themes, Backups, Logs
- **REST API** — JWT authentication, API Keys, Rate Limiting
- **Plugin System** — Event-based hooks, Actions, Filters
- **Theme System** — Custom theme loader with hooks
- **Security** — CSRF, XSS protection, Prepared Statements, Bcrypt, Rate Limiting, Audit Logs
- **Media** — Drag & Drop uploads, Thumbnail generation, WebP/AVIF conversion
- **SEO** — Meta tags, OpenGraph, Twitter Cards, XML Sitemap, robots.txt
- **Installer** — 4-step wizard, system requirements check, DB migration, admin creation
- **2FA** — TOTP support
- **Localization** — Multi-language support (de/en/fr/es)

---

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Language | PHP 8.4 |
| Database | MariaDB 11.8 |
| Web Server | Apache / Nginx (DDEV) |
| CSS | Bootstrap 5.3 + Custom CSS |
| JavaScript | Vanilla ES2025 |
| Dev Environment | DDEV |
| Package Manager | Composer 2 |

---

## Requirements

- PHP 8.4+
- MariaDB 10.6+ / MySQL 8.0+
- Composer 2.x
- Extensions: `pdo_mysql`, `gd`, `json`, `mbstring`, `openssl`

---

## Quick Start (DDEV)

```bash
# 1. Start DDEV
ddev start

# 2. Install dependencies
ddev composer install

# 3. Open in browser
ddev launch
# → Opens https://cms.ddev.site → Redirects to /install
```

### Dashboard-Updates

Der Button **Auf neueste Version aktualisieren** im Dashboard benötigt eine
HTTPS-Release-Quelle in `.env`:

```dotenv
HUBERCMS_UPDATE_URL=https://example.com/releases/latest.json
```

Die JSON-Antwort muss `version` (oder `tag_name`) und `download_url` (oder
`zipball_url`) enthalten. Das ZIP wird serverseitig entpackt; `.env` und
`app/Storage` bleiben erhalten.

---

## Installation Wizard

1. Open `https://your-domain.com/` — redirects to `/install`
2. **Step 1** — System requirements check
3. **Step 2** — Enter database credentials
4. **Step 3** — Create admin user & site name
5. **Step 4** — Done! Admin panel available at `/admin`

---

## Project Structure

```
app/
├── Config/         Configuration files (app, database, cache, mail, session)
├── Controllers/    
│   ├── Admin/      Admin panel controllers
│   ├── Api/        REST API controllers
│   ├── Auth/       Login, Register, Password reset
│   └── Frontend/   Public-facing controllers
├── Core/           Framework core (Router, DI Container, ORM, Template Engine…)
├── Database/
│   └── Migrations/ Database migration classes
├── Enums/          PHP 8.1+ Enums (UserRole, PostStatus, MediaType)
├── Events/         Event classes
├── Helpers/        Static utility classes
├── Listeners/      Event listeners
├── Middleware/      HTTP middleware (Auth, Admin, CSRF, RateLimit, ApiAuth)
├── Models/         ORM models (User, Post, Page, Media…)
├── Repositories/   Repository pattern implementations
├── Routes/         Route definitions (web.php, api.php, admin.php)
├── Services/       Business logic (Auth, Media, Mail, JWT…)
├── Storage/        Uploads, Logs, Cache, Backups
├── Traits/         Reusable traits (HasTimestamps, HasSoftDelete)
├── Views/          Template files
│   ├── admin/      Admin panel views
│   ├── auth/       Login, Register, Password views
│   ├── installer/  Installation wizard views
│   └── frontend/   Public theme templates
Plugins/            Plugin directory
Themes/
└── default/        Default theme
public/
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
├── .htaccess
└── index.php       Front controller
```

---

## Architecture

HuberCMS uses a custom MVC framework with:

- **Front Controller** — `public/index.php` — all requests route here
- **DI Container** — auto-wires dependencies via PHP Reflection
- **Router** — regex-based URL matching, middleware pipeline, method spoofing
- **Template Engine** — custom, Blade-inspired (`{{ }}`, `@if`, `@foreach`, `@extends`, `@yield`)
- **ORM** — active record pattern with fluent query builder
- **Event Dispatcher** — pub/sub with priority and wildcard support
- **Plugin Loader** — discovers and boots plugins from `/Plugins/`
- **Theme Loader** — loads themes from `/Themes/`

---

## Security

- CSRF protection on all state-changing requests
- XSS protection via escaped template output (`{{ $var }}`)
- SQL Injection protection via prepared statements only
- Password hashing via bcrypt (configurable cost)
- Rate limiting (file-based fallback, APCu when available)
- Brute-force protection (5 attempts → 15min lock)
- Secure session configuration (HttpOnly, SameSite=Lax)
- CSP headers via `.htaccess`
- Path traversal prevention in file/plugin/theme loading
- JWT with HS256 for API authentication
- Installer database hosts are restricted to `db`, localhost, and loopback by default. Set `INSTALL_DB_ALLOWED_HOSTS` to a comma-separated explicit allowlist before installing against an external database.

---

## API

```http
POST /api/v1/auth/login
Content-Type: application/json

{"email": "admin@example.com", "password": "secret"}
```

Response:
```json
{
  "token": "eyJ...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": { "id": 1, "email": "...", "role": "super_admin" }
}
```

Use in subsequent requests:
```http
Authorization: Bearer eyJ...
```

---

## Plugin Development

```php
// Plugins/my-plugin/plugin.php
return [
    'name'    => 'My Plugin',
    'version' => '1.0.0',
    'author'  => 'You',
    'main'    => MyPlugin\MyPlugin::class,
];

// Plugins/my-plugin/MyPlugin.php
class MyPlugin {
    public function boot(\HuberCMS\Core\Container $container): void {
        $events = $container->make(\HuberCMS\Core\EventDispatcher::class);
        $events->listen('post.published', fn($post) => $this->onPostPublished($post));
    }
}
```

---

## License

MIT License — see [LICENSE](LICENSE)
# Hubpress-cms

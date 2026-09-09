<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};

/**
 * WidgetsController — Admin widget management
 * @package HuberCMS\Controllers\Admin
 */
final class WidgetsController extends Controller
{
    public function __construct(Session $session)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        // Verfügbare Widget-Typen (in einer echten Implementierung
        // werden diese von Themes und Plugins registriert)
        $availableWidgets = [
            ['id' => 'search',         'name' => 'Suche',          'description' => 'Suchfeld für die Website'],
            ['id' => 'recent_posts',   'name' => 'Letzte Beiträge', 'description' => 'Liste der neuesten Beiträge'],
            ['id' => 'categories',     'name' => 'Kategorien',     'description' => 'Kategorie-Liste'],
            ['id' => 'tags',           'name' => 'Schlagwörter',   'description' => 'Tag-Cloud'],
            ['id' => 'text',           'name' => 'Text / HTML',    'description' => 'Freier Text oder HTML'],
            ['id' => 'recent_comments','name' => 'Letzte Kommentare', 'description' => 'Neueste Kommentare'],
            ['id' => 'calendar',       'name' => 'Kalender',       'description' => 'Monatskalender'],
            ['id' => 'archives',       'name' => 'Archiv',         'description' => 'Monatliches Archiv'],
            ['id' => 'menu',           'name' => 'Navigationsmenü','description' => 'Ein vorhandenes Menü einbetten'],
        ];

        // Widget-Bereiche (vom aktiven Theme definiert)
        $sidebars = [
            [
                'id'      => 'sidebar-main',
                'name'    => 'Haupt-Sidebar',
                'widgets' => [],
            ],
            [
                'id'      => 'footer-1',
                'name'    => 'Footer — Spalte 1',
                'widgets' => [],
            ],
            [
                'id'      => 'footer-2',
                'name'    => 'Footer — Spalte 2',
                'widgets' => [],
            ],
            [
                'id'      => 'footer-3',
                'name'    => 'Footer — Spalte 3',
                'widgets' => [],
            ],
        ];

        return $this->view('admin.widgets.index', [
            'title'            => 'Widgets',
            'availableWidgets' => $availableWidgets,
            'sidebars'         => $sidebars,
            'currentRoute'     => 'admin.widgets',
        ]);
    }
}

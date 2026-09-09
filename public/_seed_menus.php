<?php
// One-time DB reset — deletes itself after running
$pdo = new PDO('mysql:host=db;dbname=db;charset=utf8mb4', 'db', 'db');
$pdo->exec('DELETE FROM hcms_menu_items');
$pdo->exec('DELETE FROM hcms_menus');
unlink(__FILE__);
echo 'OK - menus cleared, auto-seeder will run on next request';

// ── 1. Clear and re-insert menus ──────────────────────────
$pdo->exec("DELETE FROM `{$p}menu_items`");
$pdo->exec("DELETE FROM `{$p}menus`");

$menus = [
    ['Hauptnavigation', 'hauptnavigation', 'primary-nav'],
    ['Metabar-Menü',    'metabar-menu',    'meta-bar'],
    ['Socialbar-Menü',  'socialbar-menu',  'social-bar'],
    ['Footer-Menü',     'footer-menu',     'footer-nav'],
];

$menuIds = [];
$ins = $pdo->prepare("INSERT INTO `{$p}menus` (name, slug, location) VALUES (?,?,?)");
foreach ($menus as [$name, $slug, $loc]) {
    $ins->execute([$name, $slug, $loc]);
    $menuIds[$slug] = (int) $pdo->lastInsertId();
}

// ── 2. Insert items ───────────────────────────────────────
$insItem = $pdo->prepare(
    "INSERT INTO `{$p}menu_items` (menu_id, label, url, target, icon, sort_order)
     VALUES (?,?,?,?,?,?)"
);

// Hauptnavigation
$nav = $menuIds['hauptnavigation'];
foreach ([
    ['Startseite', '/',                  '_self', null,  1],
    ['Blog',       '/blog',              '_self', null,  2],
    ['Über uns',   '/seite/ueber-uns',   '_self', null,  3],
    ['Leistungen', '/seite/leistungen',  '_self', null,  4],
    ['Kontakt',    '/seite/kontakt',     '_self', null,  5],
] as [$label, $url, $target, $icon, $sort]) {
    $insItem->execute([$nav, $label, $url, $target, $icon, $sort]);
}

// Metabar
$meta = $menuIds['metabar-menu'];
foreach ([
    ['+49 89 123 456',   'tel:+4989123456',         '_self', 'bi-telephone-fill', 1],
    ['info@hubpress.de', 'mailto:info@hubpress.de', '_self', 'bi-envelope-fill',  2],
    ['Mo–Fr 9–17 Uhr',  '#',                        '_self', 'bi-clock-fill',     3],
] as [$label, $url, $target, $icon, $sort]) {
    $insItem->execute([$meta, $label, $url, $target, $icon, $sort]);
}

// Socialbar
$soc = $menuIds['socialbar-menu'];
foreach ([
    ['Facebook',    '#', '_blank', 'bi-facebook',   1],
    ['X / Twitter', '#', '_blank', 'bi-twitter-x',  2],
    ['Instagram',   '#', '_blank', 'bi-instagram',  3],
    ['LinkedIn',    '#', '_blank', 'bi-linkedin',   4],
    ['YouTube',     '#', '_blank', 'bi-youtube',    5],
] as [$label, $url, $target, $icon, $sort]) {
    $insItem->execute([$soc, $label, $url, $target, $icon, $sort]);
}

// Footer
$foot = $menuIds['footer-menu'];
foreach ([
    ['Startseite',        '/',                        '_self',  null, 1],
    ['Blog',              '/blog',                    '_self',  null, 2],
    ['Über uns',          '/seite/ueber-uns',         '_self',  null, 3],
    ['Leistungen',        '/seite/leistungen',        '_self',  null, 4],
    ['Kontakt',           '/seite/kontakt',           '_self',  null, 5],
    ['Impressum',         '/seite/impressum',         '_self',  null, 6],
    ['Datenschutz',       '/seite/datenschutz',       '_self',  null, 7],
    ['AGB',               '/seite/agb',               '_self',  null, 8],
    ['Cookie-Richtlinie', '/seite/cookie-richtlinie', '_self',  null, 9],
    ['Sitemap',           '/sitemap.xml',             '_blank', null, 10],
] as [$label, $url, $target, $icon, $sort]) {
    $insItem->execute([$foot, $label, $url, $target, $icon, $sort]);
}

// Verify
$result = $pdo->query("SELECT m.name, m.location, COUNT(mi.id) as cnt
    FROM `{$p}menus` m LEFT JOIN `{$p}menu_items` mi ON mi.menu_id=m.id
    GROUP BY m.id")->fetchAll(PDO::FETCH_ASSOC);

// Self-delete
unlink(__FILE__);

header('Content-Type: text/plain');
echo "OK\n";
foreach ($result as $r) {
    echo "{$r['name']} | {$r['location']} | {$r['cnt']} items\n";
}

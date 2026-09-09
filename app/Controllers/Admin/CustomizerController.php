<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session, Database};

/**
 * CustomizerController — Theme customizer / live preview settings
 * @package HuberCMS\Controllers\Admin
 */
final class CustomizerController extends Controller
{
    public function __construct(Session $session, private readonly Database $db)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        $p = $this->db->prefix();
        $rows = $this->db->select(
            "SELECT `key`, value FROM `{$p}settings` WHERE `group` = 'customizer'"
        );
        $settings = array_column($rows, 'value', 'key');

        return $this->view('admin.customizer.index', [
            'title'        => 'Customizer',
            'settings'     => $settings,
            'currentRoute' => 'admin.customizer',
        ]);
    }

    public function save(Request $request): Response
    {
        $p = $this->db->prefix();
        $fields = ['site_name', 'site_tagline', 'site_icon',
                   'color_primary', 'color_accent', 'homepage_display'];

        foreach ($fields as $key) {
            $value = strip_tags((string) $request->post($key, ''));
            $this->db->statement(
                "INSERT INTO `{$p}settings` (`group`,`key`,`value`) VALUES ('customizer',?,?)
                 ON DUPLICATE KEY UPDATE value=?",
                [$key, $value, $value]
            );
        }

        $this->flashSuccess('Customizer-Einstellungen gespeichert.');
        return $this->redirect('/admin/customizer');
    }
}

<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\Request;
use HuberCMS\Core\Response;
use HuberCMS\Core\Session;
use HuberCMS\Services\UpdateService;

final class UpdateController extends Controller
{
    public function __construct(Session $session, private readonly UpdateService $updates)
    {
        parent::__construct($session);
    }

    public function check(Request $request): Response
    {
        try {
            $latest = $this->updates->latest();
            $message = version_compare(HUBERCMS_VERSION, $latest['version'], '<')
                ? 'Update verfügbar: Version ' . $latest['version']
                : 'Du verwendest bereits die neueste Version.';
            $this->flashSuccess($message);
        } catch (\Throwable $e) {
            $this->flashError($e->getMessage());
        }
        return $this->redirect('/admin');
    }

    public function install(Request $request): Response
    {
        try {
            $latest = $this->updates->latest();
            if (version_compare(HUBERCMS_VERSION, $latest['version'], '>=')) {
                $this->flashSuccess('Du verwendest bereits die neueste Version.');
            } else {
                $this->updates->install($latest['download_url']);
                $this->flashSuccess('HuberCMS wurde auf Version ' . $latest['version'] . ' aktualisiert.');
            }
        } catch (\Throwable $e) {
            $this->flashError('Update fehlgeschlagen: ' . $e->getMessage());
        }
        return $this->redirect('/admin');
    }
}
<?php

declare(strict_types=1);

namespace HuberCMS\Controllers\Admin;

use HuberCMS\Controllers\Controller;
use HuberCMS\Core\{Request, Response, Session};

/**
 * PluginEditorController — Direct plugin file editing (stub)
 * @package HuberCMS\Controllers\Admin
 */
final class PluginEditorController extends Controller
{
    public function __construct(Session $session)
    {
        parent::__construct($session);
    }

    public function index(Request $request): Response
    {
        return $this->view('admin.plugin_editor.index', [
            'title'        => 'Plugin-Editor',
            'currentRoute' => 'admin.plugin_editor',
        ]);
    }
}

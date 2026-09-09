<?php

require_once __DIR__ . '/src/SnowfallPlugin.php';

return [
    'name'        => 'Snowfall',
    'version'     => '1.0.0',
    'author'      => 'HuberCMS Team',
    'description' => 'Lässt Schneeflocken in drei Größen am linken und rechten Bildschirmrand herabfallen. Im Light-Modus sind die Flocken bunt.',
    'main'        => \HuberCMS\Plugins\Snowfall\SnowfallPlugin::class,
];

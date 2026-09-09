<?php
/**
 * Plugin Name:       Snowfall
 * Description:       Schneeflocken-Animation mit Einstellungs-Panel, Heiligabend-Countdown und Mini-Spiel „Flocken fangen" (Bestenliste inklusive).
 * Version:           1.0.0
 * Author:            HuberCMS Team
 * License:           MIT
 * Requires at least: 6.0
 * Requires PHP:      8.0
 */

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    $ver = '1.0.0';

    // Icons für Button, Panel und Startbildschirm
    wp_enqueue_style(
        'bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
        [],
        '1.11.3'
    );

    wp_enqueue_style('snowfall', plugins_url('assets/css/snowfall.css', __FILE__), [], $ver);
    wp_enqueue_script('snowfall', plugins_url('assets/js/snowfall.js', __FILE__), [], $ver, true);
});

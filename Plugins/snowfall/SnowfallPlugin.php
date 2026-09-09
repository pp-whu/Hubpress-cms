<?php

declare(strict_types=1);

namespace HuberCMS\Plugins\Snowfall;

use HuberCMS\Core\Container;
use HuberCMS\Core\EventDispatcher;

/**
 * SnowfallPlugin
 *
 * Injiziert Schneeflocken-Animation (CSS + JS) ins Frontend.
 * Flocken fallen pendelnd von oben nach unten am linken und
 * rechten Bildschirmrand. Im Light-Modus sind sie bunt.
 */
class SnowfallPlugin
{
    public function boot(Container $container): void
    {
        /** @var EventDispatcher $events */
        $events = $container->make(EventDispatcher::class);

        $events->listen('frontend.body.end', fn(): string => $this->markup());
    }

    private function markup(): string
    {
        return <<<'HTML'
<style>
#snowfall-container {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 9998;
    overflow: hidden;
}
.snowflake {
    position: absolute;
    top: -2.5rem;
    user-select: none;
    line-height: 1;
    color: rgba(255,255,255,.85);
    will-change: transform, opacity;
    animation: sf-fall linear forwards;
}
.snowflake.sf-sm { font-size: .7rem; }
.snowflake.sf-md { font-size: 1.3rem; }
.snowflake.sf-lg { font-size: 2.1rem; }

/* top-to-bottom fall with gentle sway + rotation */
@keyframes sf-fall {
    0%   { transform: translateY(0)     translateX(0)     rotate(0deg);   opacity: .9; }
    25%  { transform: translateY(27vh)  translateX(18px)  rotate(90deg);  }
    50%  { transform: translateY(55vh)  translateX(-14px) rotate(180deg); }
    75%  { transform: translateY(82vh)  translateX(20px)  rotate(270deg); }
    100% { transform: translateY(110vh) translateX(0)     rotate(360deg); opacity: .1; }
}

/* colorful snowflakes in light mode */
[data-theme="light"] .snowflake.sf-c0 { color: #6366f1; }
[data-theme="light"] .snowflake.sf-c1 { color: #ec4899; }
[data-theme="light"] .snowflake.sf-c2 { color: #f59e0b; }
[data-theme="light"] .snowflake.sf-c3 { color: #10b981; }
[data-theme="light"] .snowflake.sf-c4 { color: #3b82f6; }
[data-theme="light"] .snowflake.sf-c5 { color: #ef4444; }
[data-theme="light"] .snowflake.sf-c6 { color: #8b5cf6; }
[data-theme="light"] .snowflake.sf-c7 { color: #06b6d4; }
[data-theme="light"] .snowflake.sf-c8 { color: #f97316; }
[data-theme="light"] .snowflake.sf-c9 { color: #84cc16; }
</style>
<script>
(function () {
    'use strict';
    var container = document.createElement('div');
    container.id = 'snowfall-container';
    document.body.appendChild(container);

    var chars  = ['❄', '❅', '❆', '✦', '✧', '❋', '•'];
    var sizes  = ['sf-sm', 'sf-sm', 'sf-sm', 'sf-md', 'sf-md', 'sf-lg'];
    var colors   = 10;    // sf-c0 … sf-c9
    var edgeFrac = 0.17;  // 17 % screen width per side

    function spawn() {
        var el = document.createElement('span');
        el.className = 'snowflake';
        el.textContent = chars[Math.floor(Math.random() * chars.length)];
        el.classList.add(sizes[Math.floor(Math.random() * sizes.length)]);
        el.classList.add('sf-c' + Math.floor(Math.random() * colors));

        var w   = window.innerWidth;
        var max = w * edgeFrac;
        var x   = Math.random() < 0.5
                    ? Math.random() * max          // left edge
                    : w - Math.random() * max;     // right edge
        el.style.left = x + 'px';

        var dur = (5 + Math.random() * 9).toFixed(2); // 5 – 14 s
        el.style.animationDuration = dur + 's';

        container.appendChild(el);
        setTimeout(function () { el.remove(); }, (parseFloat(dur) + 0.5) * 1000);
    }

    // pre-seed: stagger 60 flakes across the first 6 s
    for (var i = 0; i < 60; i++) {
        setTimeout(spawn, Math.random() * 6000);
    }

    // continuous stream: 3-5 new flakes every 600 ms
    setInterval(function () {
        var n = 3 + Math.floor(Math.random() * 3);
        for (var j = 0; j < n; j++) spawn();
    }, 600);
}());
</script>
HTML;
    }
}

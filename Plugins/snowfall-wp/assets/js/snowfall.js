(function () {
    'use strict';
    var KEY_ENABLED   = 'snowfall-enabled';
    var KEY_INTENSITY = 'snowfall-intensity';

    // default: OFF until the user enables it
    var enabled   = localStorage.getItem(KEY_ENABLED) === '1';
    var intensity = parseInt(localStorage.getItem(KEY_INTENSITY) || '4', 10);

    var container = document.createElement('div');
    container.id = 'snowfall-container';
    document.body.appendChild(container);

    // ── Boden & Männchen mit Schubkarre ────────────────────
    var GROUND_H = 26;
    var ground = document.createElement('div');
    ground.id = 'snowfall-ground';
    document.body.appendChild(ground);

    var man = document.createElement('div');
    man.id = 'snowfall-man';
    man.innerHTML =
        '<svg viewBox="0 0 64 40" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">' +
            '<circle cx="13" cy="34" r="4.5"/>' +                       // Rad
            '<path d="M4 21 L28 21 L24 31 L10 31 Z" fill="currentColor" fill-opacity=".25"/>' + // Wanne
            '<path d="M28 21 L42 26"/>' +                                // Griff
            '<circle cx="49" cy="9" r="4" fill="currentColor" fill-opacity=".25"/>' +           // Kopf
            '<path d="M49 13 L46 25"/>' +                                // Körper
            '<path d="M47 17 L40 25"/>' +                                // Arm
            '<path d="M46 25 L41 37"/><path d="M46 25 L52 37"/>' +       // Beine
        '</svg>';
    document.body.appendChild(man);

    // ── Maussteuerung: Männchen läuft zur Cursor-Position ─────
    var MAN_W     = 112;
    var MAN_SPEED = 4;                         // px pro Frame
    var manX      = 8;
    var manTarget = window.innerWidth / 2;

    document.addEventListener('mousemove', function (e) {
        manTarget = e.clientX - MAN_W / 2;
    });

    function moveMan() {
        if (gameActive) {
            var maxX = window.innerWidth - MAN_W - 4;
            var target = Math.min(Math.max(manTarget, 4), maxX);
            var dx = target - manX;
            if (Math.abs(dx) > MAN_SPEED) {
                manX += Math.sign(dx) * MAN_SPEED;
                man.classList.toggle('flip', dx > 0); // Blickrichtung
            } else {
                manX = target;
            }
            man.style.left = manX + 'px';
        }
        requestAnimationFrame(moveMan);
    }
    requestAnimationFrame(moveMan);

    function syncScene() {
        document.body.classList.toggle('sf-scene-on', enabled || gameActive);
        document.body.classList.toggle('sf-game-on', gameActive);
    }

    // ── Button (opens settings panel) ──────────────────────
    var toggle = document.createElement('button');
    toggle.id = 'snowfall-toggle';
    toggle.type = 'button';
    toggle.title = 'Schneefall-Einstellungen';
    toggle.innerHTML = '<i class="bi bi-snow"></i>';
    document.body.appendChild(toggle);

    // ── Settings panel ─────────────────────────────────
    var panel = document.createElement('div');
    panel.id = 'snowfall-panel';
    panel.innerHTML =
        '<div class="sf-panel-header">' +
            '<i class="bi bi-snow"></i> Schneefall' +
            '<button type="button" class="sf-panel-close" aria-label="Schlie\u00dfen">&times;</button>' +
        '</div>' +
        '<label class="sf-panel-row">' +
            '<span>Ein / Aus</span>' +
            '<span class="sf-switch">' +
                '<input type="checkbox" id="sf-switch-input">' +
                '<span class="sf-slider"></span>' +
            '</span>' +
        '</label>' +
        '<label class="sf-panel-row sf-panel-col">' +
            '<span>Intensit\u00e4t <small id="sf-intensity-value"></small></span>' +
            '<input type="range" id="sf-intensity" min="1" max="10" step="1">' +
        '</label>' +
        '<div class="sf-panel-row sf-panel-col sf-countdown">' +
            '<span><i class="bi bi-gift"></i> Bis Heiligabend</span>' +
            '<span id="sf-countdown-value">\u2026</span>' +
        '</div>' +
        '<div class="sf-panel-row sf-panel-col sf-game-row">' +
            '<span><i class="bi bi-controller"></i> Flocken fangen <small id="sf-highscore"></small></span>' +
            '<button type="button" id="sf-game-start" class="sf-game-btn">Spiel starten</button>' +
            '<label class="sf-panel-row sf-easy-row">' +
                '<span>Leichter Fang-Modus</span>' +
                '<span class="sf-switch">' +
                    '<input type="checkbox" id="sf-easy-input">' +
                    '<span class="sf-slider"></span>' +
                '</span>' +
            '</label>' +
            '<small class="sf-hint">Das ganze M\u00e4nnchen f\u00e4ngt \u2014 Flocken bringen dann aber nur 3 Punkte.</small>' +
        '</div>' +
        '<div class="sf-panel-row sf-panel-col sf-scores-row">' +
            '<span><i class="bi bi-trophy"></i> Bestenliste</span>' +
            '<ol id="sf-scores"></ol>' +
        '</div>';
    document.body.appendChild(panel);

    // Leichter Modus: ganzes Männchen fängt, aber nur 3 Punkte pro Flocke
    var KEY_EASY = 'snowfall-easy';
    var easyMode = localStorage.getItem(KEY_EASY) === '1';
    var easyInput = panel.querySelector('#sf-easy-input');
    easyInput.checked = easyMode;
    easyInput.addEventListener('change', function () {
        easyMode = easyInput.checked;
        localStorage.setItem(KEY_EASY, easyMode ? '1' : '0');
    });

    var switchInput    = panel.querySelector('#sf-switch-input');
    var intensityInput = panel.querySelector('#sf-intensity');
    var intensityValue = panel.querySelector('#sf-intensity-value');

    function syncUi() {
        toggle.classList.toggle('off', !enabled);
        switchInput.checked = enabled;
        intensityInput.value = String(intensity);
        intensityValue.textContent = '(' + intensity + '/10)';
    }
    syncUi();

    toggle.addEventListener('click', function () {
        panel.classList.toggle('open');
    });
    panel.querySelector('.sf-panel-close').addEventListener('click', function () {
        panel.classList.remove('open');
    });

    switchInput.addEventListener('change', function () {
        enabled = switchInput.checked;
        localStorage.setItem(KEY_ENABLED, enabled ? '1' : '0');
        if (!enabled) {
            container.innerHTML = '';
            landed.length = 0;
        } else {
            preSeed();
        }
        syncUi();
        syncScene();
    });

    intensityInput.addEventListener('input', function () {
        intensity = parseInt(intensityInput.value, 10);
        localStorage.setItem(KEY_INTENSITY, String(intensity));
        syncUi();
    });

    // ── Countdown bis Heiligabend ─────────────────────────
    var countdownEl = panel.querySelector('#sf-countdown-value');

    function updateCountdown() {
        var now = new Date();
        var target = new Date(now.getFullYear(), 11, 24); // 24.12. 00:00
        // ganz Heiligabend anzeigen, danach aufs nächste Jahr zählen
        if (now >= new Date(now.getFullYear(), 11, 25)) {
            target = new Date(now.getFullYear() + 1, 11, 24);
        }

        var diff = target - now;
        if (diff <= 0) {
            countdownEl.textContent = '\uD83C\uDF84 Heute ist Heiligabend!';
            return;
        }

        var d = Math.floor(diff / 86400000);
        var h = Math.floor(diff / 3600000) % 24;
        var m = Math.floor(diff / 60000) % 60;
        var s = Math.floor(diff / 1000) % 60;

        countdownEl.textContent =
            d + ' T  ' +
            String(h).padStart(2, '0') + ':' +
            String(m).padStart(2, '0') + ':' +
            String(s).padStart(2, '0');
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);

    // ── Mini-Spiel: Flocken fangen ────────────────────────
    var KEY_HIGHSCORE = 'snowfall-highscore';
    var GAME_SECONDS  = 30;
    var highscore  = parseInt(localStorage.getItem(KEY_HIGHSCORE) || '0', 10);
    var gameActive = false;
    var gameScore  = 0;
    var gameLeft   = 0;
    var gameTicker = null;

    var highscoreEl = panel.querySelector('#sf-highscore');
    function syncHighscore() {
        highscoreEl.textContent = highscore > 0 ? '(Rekord: ' + highscore + ')' : '';
    }
    syncHighscore();

    // ── Bestenliste (localStorage) ────────────────────────
    var KEY_SCORES = 'snowfall-scores';
    var KEY_PLAYER = 'snowfall-player';
    var scoresEl = panel.querySelector('#sf-scores');

    function escapeHtml(s) {
        return s.replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function loadScores() {
        try {
            var list = JSON.parse(localStorage.getItem(KEY_SCORES) || '[]');
            return Array.isArray(list) ? list : [];
        } catch (e) { return []; }
    }

    function saveScore(name, score) {
        var list = loadScores();
        list.push({ name: name, score: score });
        list.sort(function (a, b) { return b.score - a.score; });
        localStorage.setItem(KEY_SCORES, JSON.stringify(list.slice(0, 10)));
        renderScores();
    }

    function renderScores() {
        var list = loadScores().slice(0, 5);
        scoresEl.innerHTML = list.length
            ? list.map(function (s) {
                  return '<li>' + escapeHtml(String(s.name)) + ' <b>' + parseInt(s.score, 10) + '</b></li>';
              }).join('')
            : '<li class="sf-empty">Noch keine Eintr\u00e4ge</li>';
    }
    renderScores();

    var hud = document.createElement('div');
    hud.id = 'sf-game-hud';
    document.body.appendChild(hud);

    var result = document.createElement('div');
    result.id = 'sf-game-result';
    document.body.appendChild(result);

    // ── Startbildschirm ───────────────────────────────
    var KEY_LAST = 'snowfall-last-score';
    var lastScore = parseInt(localStorage.getItem(KEY_LAST) || '0', 10);

    var start = document.createElement('div');
    start.id = 'sf-start';
    start.innerHTML =
        '<button type="button" class="sf-start-close" aria-label="Schlie\u00dfen">&times;</button>' +
        '<div class="sf-start-title">' +
            '<span>Schnee</span><span>Flocken</span><em>Fangen</em>' +
        '</div>' +
        '<div class="sf-start-layout">' +
            '<div class="sf-start-menu">' +
                '<button type="button" data-action="play"><i class="bi bi-play-fill"></i> Spielen</button>' +
                '<button type="button" data-action="scores"><i class="bi bi-trophy-fill"></i> Bestenliste</button>' +
                '<button type="button" data-action="settings"><i class="bi bi-gear-fill"></i> Einstellungen</button>' +
                '<button type="button" data-action="help"><i class="bi bi-question-lg"></i> Hilfe</button>' +
            '</div>' +
            '<div class="sf-start-content" id="sf-start-content"></div>' +
            '<div class="sf-start-stats">' +
                '<div class="sf-stat"><span>Punkte</span><b id="sf-start-last">0</b></div>' +
                '<div class="sf-stat"><span>H\u00f6chstpunktzahl</span><b id="sf-start-high">0</b></div>' +
            '</div>' +
        '</div>' +
        '<div class="sf-start-ground"></div>';
    document.body.appendChild(start);

    // dekorative, statisch verteilte Flocken
    (function () {
        for (var i = 0; i < 16; i++) {
            var f = document.createElement('span');
            f.className = 'sf-start-deco';
            f.textContent = '\u2744';
            f.style.left = (Math.random() * 96) + '%';
            f.style.top  = (Math.random() * 65) + '%';
            f.style.fontSize = (12 + Math.random() * 30) + 'px';
            f.style.opacity = (0.35 + Math.random() * 0.55).toFixed(2);
            start.appendChild(f);
        }
    }());

    var startContent = start.querySelector('#sf-start-content');
    var startView = '';

    function openStart() {
        start.querySelector('#sf-start-last').textContent = lastScore;
        start.querySelector('#sf-start-high').textContent = highscore;
        startContent.innerHTML = '';
        startView = '';
        panel.classList.remove('open');
        result.classList.remove('open');
        start.classList.add('open');
    }

    function renderStartView(view) {
        if (startView === view) {           // gleicher Button = zuklappen
            startContent.innerHTML = '';
            startView = '';
            return;
        }
        startView = view;
        if (view === 'scores') {
            var list = loadScores();
            startContent.innerHTML = '<h4>\uD83C\uDFC6 Bestenliste</h4>' + (list.length
                ? '<ol>' + list.map(function (s) {
                      return '<li>' + escapeHtml(String(s.name)) + ' <b>' + parseInt(s.score, 10) + '</b></li>';
                  }).join('') + '</ol>'
                : '<p>Noch keine Eintr\u00e4ge \u2014 sei der Erste!</p>');
        } else if (view === 'settings') {
            startContent.innerHTML =
                '<h4>\u2699\uFE0F Einstellungen</h4>' +
                '<label class="sf-panel-row sf-start-setting">' +
                    '<span>Leichter Fang-Modus</span>' +
                    '<span class="sf-switch">' +
                        '<input type="checkbox" id="sf-easy-start">' +
                        '<span class="sf-slider"></span>' +
                    '</span>' +
                '</label>' +
                '<small class="sf-hint">Das ganze M\u00e4nnchen f\u00e4ngt \u2014 Flocken bringen dann aber nur 3 Punkte.</small>';
            var easyStart = startContent.querySelector('#sf-easy-start');
            easyStart.checked = easyMode;
            easyStart.addEventListener('change', function () {
                easyMode = easyStart.checked;
                localStorage.setItem(KEY_EASY, easyMode ? '1' : '0');
                easyInput.checked = easyMode;   // Panel-Schalter synchron halten
            });
        } else {
            startContent.innerHTML =
                '<h4>\u2753 So wird gespielt</h4>' +
                '<ul>' +
                    '<li><b>Maus bewegen:</b> M\u00e4nnchen steuern</li>' +
                    '<li><b>Flocken</b> mit der Schubkarre fangen (klein\u202f=\u202f30, mittel\u202f=\u202f20, gro\u00df\u202f=\u202f10)</li>' +
                    '<li><b>Leichter Fang-Modus</b> (Einstellungen): das ganze M\u00e4nnchen f\u00e4ngt, aber nur 3 Punkte pro Flocke</li>' +
                    '<li><b>Linksklick:</b> springen</li>' +
                    '<li><b>Steine niemals ber\u00fchren</b> \u2014 sonst ist das Spiel vorbei!</li>' +
                    '<li><b>30 Sekunden</b> Zeit \u2014 viel Erfolg!</li>' +
                '</ul>';
        }
    }

    start.querySelector('.sf-start-close').addEventListener('click', function () {
        start.classList.remove('open');
    });
    start.querySelector('.sf-start-menu').addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action');
        if (action === 'play') {
            start.classList.remove('open');
            startGame();
        } else {
            renderStartView(action);
        }
    });

    function updateHud() {
        hud.textContent = '\u2744 ' + gameScore + '  \u00b7  ' + gameLeft + 's';
    }

    function startGame() {
        if (gameActive) return;
        gameActive = true;
        gameScore  = 0;
        gameLeft   = GAME_SECONDS;
        container.innerHTML = '';
        landed.length = 0;
        container.classList.add('sf-game');
        result.classList.remove('open');
        panel.classList.remove('open');
        updateHud();
        hud.classList.add('open');
        syncScene();
        stoneTimer = setInterval(spawnStone, 2600);
        gameTicker = setInterval(function () {
            gameLeft--;
            if (gameLeft <= 0) { endGame(); } else { updateHud(); }
        }, 1000);
    }

    function endGame(died) {
        if (!gameActive) return;
        gameActive = false;
        clearInterval(gameTicker);
        clearInterval(stoneTimer);
        hud.classList.remove('open');

        if (died) {
            // Todes-Effekt abspielen, Männchen bleibt solange sichtbar
            man.classList.add('sf-dead');
            var rect = man.getBoundingClientRect();
            burstAt(rect.left + rect.width / 2, rect.top + rect.height / 2);
            document.body.classList.add('sf-shake');
            setTimeout(function () {
                man.classList.remove('sf-dead');
                document.body.classList.remove('sf-shake');
                showResult(true);
            }, 1150);
        } else {
            showResult(false);
        }
    }

    function showResult(died) {
        container.classList.remove('sf-game');
        container.querySelectorAll('.sf-stone').forEach(function (s) { s.remove(); });
        if (!enabled) {
            container.innerHTML = '';
            landed.length = 0;
        }
        syncScene();

        var record = gameScore > highscore;
        if (record) {
            highscore = gameScore;
            localStorage.setItem(KEY_HIGHSCORE, String(highscore));
            syncHighscore();
        }
        lastScore = gameScore;
        localStorage.setItem(KEY_LAST, String(lastScore));

        var title = died   ? '\uD83D\uDC80 Vom Stein erwischt!'
                  : record ? '\uD83C\uDFC6 Neuer Rekord!'
                  : 'Zeit um!';

        result.innerHTML =
            '<h3>' + title + '</h3>' +
            '<p class="sf-result-score">' + gameScore + ' Punkte</p>' +
            '<p class="sf-result-high">Highscore: ' + highscore + '</p>' +
            '<div class="sf-result-save">' +
                '<input type="text" id="sf-player-name" maxlength="20" placeholder="Dein Name">' +
                '<button type="button" class="sf-game-btn sf-save-score">Eintragen</button>' +
            '</div>' +
            '<div class="sf-result-actions">' +
                '<button type="button" class="sf-game-btn sf-again">Nochmal</button>' +
                '<button type="button" class="sf-game-btn sf-close-result">Schlie\u00dfen</button>' +
            '</div>';
        result.classList.add('open');

        var nameInput = result.querySelector('#sf-player-name');
        nameInput.value = localStorage.getItem(KEY_PLAYER) || '';
        result.querySelector('.sf-save-score').addEventListener('click', function () {
            var name = nameInput.value.trim() || 'Anonym';
            localStorage.setItem(KEY_PLAYER, name);
            saveScore(name, gameScore);
            this.disabled = true;
            this.textContent = 'Gespeichert \u2713';
        });
        result.querySelector('.sf-again').addEventListener('click', startGame);
        result.querySelector('.sf-close-result').addEventListener('click', function () {
            result.classList.remove('open');
        });
    }

    // Partikel-Explosion am Todesort
    function burstAt(x, y) {
        var glyphs = ['\u2744', '\u2726', '\u2022', '\u2745'];
        var palette = ['#6366f1', '#ec4899', '#f59e0b', '#ef4444', '#3b82f6'];
        for (var i = 0; i < 16; i++) {
            var p = document.createElement('span');
            p.className = 'sf-burst-p';
            p.textContent = glyphs[i % glyphs.length];
            p.style.left = x + 'px';
            p.style.top  = y + 'px';
            p.style.color = palette[Math.floor(Math.random() * palette.length)];
            var ang = Math.random() * Math.PI * 2;
            var r   = 60 + Math.random() * 90;
            p.style.setProperty('--bx', (Math.cos(ang) * r) + 'px');
            p.style.setProperty('--by', (Math.sin(ang) * r - 40) + 'px');
            document.body.appendChild(p);
            (function (pp) {
                setTimeout(function () { pp.remove(); }, 900);
            }(p));
        }
    }

    panel.querySelector('#sf-game-start').addEventListener('click', openStart);

    function flakePoints(el) {
        return el.classList.contains('sf-sm') ? 30
             : el.classList.contains('sf-md') ? 20 : 10;
    }

    function showPoints(x, y, pts) {
        var fx = document.createElement('span');
        fx.className = 'sf-points' + (pts < 0 ? ' sf-minus' : '');
        fx.textContent = (pts > 0 ? '+' : '') + pts;
        fx.style.left = x + 'px';
        fx.style.top  = y + 'px';
        document.body.appendChild(fx);
        setTimeout(function () { fx.remove(); }, 700);
    }

    // ── Steine: fallen vom Himmel, per Linksklick überspringen ──
    var stoneTimer = null;
    var jumping = false;
    var stoneSizes = ['sf-stone-sm', 'sf-stone-md', 'sf-stone-lg'];
    var stoneH = { 'sf-stone-sm': 14, 'sf-stone-md': 23, 'sf-stone-lg': 36 };

    function spawnStone() {
        if (!gameActive || reducedMotion.matches) return;
        var el = document.createElement('span');
        var size = stoneSizes[Math.floor(Math.random() * stoneSizes.length)];
        el.className = 'sf-stone ' + size;
        el.style.left = (Math.random() * (window.innerWidth - 50)) + 'px';
        var dist = window.innerHeight + 50 - GROUND_H - stoneH[size];
        el.style.setProperty('--sf-dist', dist + 'px');
        el.style.animationDuration = (2.2 + Math.random() * 1.8).toFixed(2) + 's';
        container.appendChild(el);
    }

    // Linksklick = Sprung (nur im Spiel)
    document.addEventListener('mousedown', function (e) {
        if (!gameActive || e.button !== 0 || jumping) return;
        if (e.target.closest('#snowfall-panel, #snowfall-toggle, #sf-game-result')) return;
        jumping = true;
        man.classList.add('sf-jump');
        setTimeout(function () {
            jumping = false;
            man.classList.remove('sf-jump');
        }, 650);
    });

    // ── Snowfall ───────────────────────────────────────
    var chars  = ['❄', '❅', '❆', '✦', '✧', '❋', '•'];
    var sizes  = ['sf-sm', 'sf-sm', 'sf-sm', 'sf-md', 'sf-md', 'sf-lg'];
    var colors   = 10;    // sf-c0 … sf-c9
    var edgeFrac = 0.17;  // 17 % screen width per side

    // accessibility: no flakes for users preferring reduced motion
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    // approximate flake heights per size class (px)
    var flakeH = { 'sf-sm': 11, 'sf-md': 21, 'sf-lg': 34 };

    function spawn() {
        if ((!enabled && !gameActive) || reducedMotion.matches) return;
        var el = document.createElement('span');
        el.className = 'snowflake';
        el.textContent = chars[Math.floor(Math.random() * chars.length)];
        var size = sizes[Math.floor(Math.random() * sizes.length)];
        el.classList.add(size);
        el.classList.add('sf-c' + Math.floor(Math.random() * colors));

        var w   = window.innerWidth;
        var max = w * edgeFrac;
        // im Spiel volle Breite, sonst nur Ränder
        var x   = gameActive
                    ? Math.random() * (w - 40)
                    : (Math.random() < 0.5
                        ? Math.random() * max          // left edge
                        : w - Math.random() * max);    // right edge
        el.style.left = x + 'px';

        // Falldistanz bis auf den Boden (Start bei top:-2.5rem = -40px)
        var dist = window.innerHeight + 40 - GROUND_H - flakeH[size];
        el.style.setProperty('--sf-dist', dist + 'px');

        var dur = (gameActive
                    ? 3 + Math.random() * 5            // schneller im Spiel
                    : 5 + Math.random() * 9).toFixed(2);
        el.style.animationDuration = dur + 's';

        container.appendChild(el);
    }

    // ── Landung & Einsammeln ───────────────────────────────
    var landed = [];
    var MAX_LANDED = 150;

    container.addEventListener('animationend', function (e) {
        var el = e.target;
        // Steine: liegen bleiben, nach 6 s ausblenden
        if (el.classList.contains('sf-stone')) {
            el.classList.add('sf-stone-landed');
            el.style.bottom = (GROUND_H - 4) + 'px';
            setTimeout(function () {
                el.classList.add('sf-stone-fade');
                setTimeout(function () { el.remove(); }, 500);
            }, 6000);
            return;
        }
        if (!el.classList.contains('snowflake') || el.classList.contains('sf-landed')) return;
        el.classList.add('sf-landed');
        el.style.bottom = (GROUND_H - 4) + 'px';
        landed.push(el);
        // älteste Flocken abräumen, damit der DOM nicht wächst
        while (landed.length > MAX_LANDED) {
            var old = landed.shift();
            if (old.isConnected) old.remove();
        }
    });

    // Fangzone: leichter Modus = ganzes Männchen (3 Punkte), sonst nur die Wanne
    // (Wanne im viewBox: x 4-28 von 64, y 21-31 von 40; bei flip gespiegelt)
    setInterval(function () {
        if (!gameActive) return;
        var rect = man.getBoundingClientRect();
        var zoneLeft, zoneRight, zoneTop, zoneBottom;

        if (easyMode) {
            zoneLeft   = rect.left;
            zoneRight  = rect.right;
            zoneTop    = rect.top;
            zoneBottom = rect.bottom;
        } else {
            var flipped = man.classList.contains('flip');
            zoneLeft   = flipped
                       ? rect.right - rect.width * (28 / 64)
                       : rect.left  + rect.width * (4 / 64);
            zoneRight  = zoneLeft + rect.width * (24 / 64);
            zoneTop    = rect.top + rect.height * (21 / 40);
            zoneBottom = rect.top + rect.height * (31 / 40);
        }

        var flakes = container.querySelectorAll('.snowflake:not(.sf-landed)');

        for (var i = 0; i < flakes.length; i++) {
            var el = flakes[i];
            var fx = el.getBoundingClientRect();
            var cx = fx.left + fx.width / 2;
            var hitX = cx > zoneLeft && cx < zoneRight;
            var hitY = fx.bottom > zoneTop && fx.top < zoneBottom;
            if (hitX && hitY) {
                var pts = easyMode ? 3 : flakePoints(el);
                gameScore += pts;
                updateHud();
                showPoints(cx, fx.top - 8, pts);
                el.remove();
            }
        }

        // Steinberührung = Tod, es sei denn man springt
        if (!jumping) {
            var stones = container.querySelectorAll('.sf-stone:not(.sf-stone-fade)');
            for (var k = 0; k < stones.length; k++) {
                var sx = stones[k].getBoundingClientRect();
                if (sx.right > rect.left + 12 && sx.left < rect.right - 12 &&
                    sx.bottom > rect.top && sx.top < rect.bottom) {
                    endGame(true);
                    return;
                }
            }
        }
    }, 100);
    syncScene();

    // pre-seed: stagger flakes across the first 6 s
    function preSeed() {
        var count = intensity * 15;
        for (var i = 0; i < count; i++) {
            setTimeout(spawn, Math.random() * 6000);
        }
    }
    if (enabled) preSeed();

    // continuous stream, amount scales with intensity (fixed rate in game)
    setInterval(function () {
        var n = gameActive
                ? 5
                : Math.max(1, Math.round(intensity * 0.75) + Math.floor(Math.random() * 2));
        for (var j = 0; j < n; j++) spawn();
    }, 600);
}());

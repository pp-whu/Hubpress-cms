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

    // ── Boden & Spielfigur ─────────────────────────────────
    var GROUND_H = 26;
    var ground = document.createElement('div');
    ground.id = 'snowfall-ground';
    document.body.appendChild(ground);

    var man = document.createElement('div');
    man.id = 'snowfall-man';
    document.body.appendChild(man);
    var player2 = document.createElement('div');
    player2.id = 'snowfall-player-2';
    document.body.appendChild(player2);

    var KEY_CHARACTER = 'snowfall-character';
    var savedCharacter = localStorage.getItem(KEY_CHARACTER);
    if (!savedCharacter || savedCharacter === 'scooter' || savedCharacter === 'wheelchair') {
        localStorage.setItem(KEY_CHARACTER, 'image');                          
    }
    var character = localStorage.getItem(KEY_CHARACTER) === 'image' ? 'image' : 'wheelbarrow';
    var KEY_CHARACTER_2 = 'snowfall-character-2';
    var savedCharacter2 = localStorage.getItem(KEY_CHARACTER_2);
    if (!savedCharacter2 || savedCharacter2 === 'scooter' || savedCharacter2 === 'wheelchair') {
        localStorage.setItem(KEY_CHARACTER_2, 'image');
    }
    var character2 = localStorage.getItem(KEY_CHARACTER_2) === 'image' ? 'image' : 'wheelbarrow';
    var KEY_MULTIPLAYER = 'snowfall-multiplayer';
    var multiplayer = localStorage.getItem(KEY_MULTIPLAYER) === '1';
    var KEY_COLOR_1 = 'snowfall-color-1';
    var KEY_COLOR_2 = 'snowfall-color-2';
    var playerColor1 = localStorage.getItem(KEY_COLOR_1) || '#f59e0b';
    var playerColor2 = localStorage.getItem(KEY_COLOR_2) || '#22d3ee';

    function characterSvg(selectedCharacter) {
        if (selectedCharacter === 'image') {
            return '<img src="/assets/images/character.svg" alt="Snowfall character" style="width:100%;height:100%;object-fit:contain;display:block;pointer-events:none;border:0;background:transparent;" />';
        }

        return selectedCharacter === 'scooter'
            ? '<svg viewBox="0 0 260 170" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">' +
                '<g transform="translate(0,2)">' +
                '<path d="M128 48 L176 48 L190 64 L200 122 L75 122 L72 64 L93 54 Z" fill="#2cae84" stroke="#111827" stroke-width="4"/>' +
                '<path d="M70 124 L17 124 L19 140 L57 140 L70 124 Z" fill="#1aa57d" stroke="#111827" stroke-width="4"/>' +
                '<path d="M111 122 L157 122 L170 150 L100 150 Z" fill="#1d8a6b" stroke="#111827" stroke-width="4"/>' +
                '<circle cx="48" cy="144" r="26" fill="#dce4ec" stroke="#111827" stroke-width="4"/>' +
                '<circle cx="48" cy="144" r="9" fill="#111827"/>' +
                '<circle cx="172" cy="144" r="26" fill="#dce4ec" stroke="#111827" stroke-width="4"/>' +
                '<circle cx="172" cy="144" r="9" fill="#111827"/>' +
                '<path d="M110 22 L145 18 L160 48 L90 48 Z" fill="#0f4d5d" stroke="#111827" stroke-width="4"/>' +
                '<path d="M88 48 L112 54 L91 74 L64 74 L79 54 Z" fill="#1ea9c3" stroke="#111827" stroke-width="4"/>' +
                '<path d="M73 82 C75 42, 108 20, 124 22 C148 25, 164 39, 168 70 L138 75 C138 56, 128 48, 114 49 L73 82 Z" fill="#f4d0a4" stroke="#111827" stroke-width="4"/>' +
                '<path d="M84 74 C96 58, 118 56, 130 58 L136 81 C119 86, 105 88, 88 84 Z" fill="#ddc1a2" stroke="#111827" stroke-width="4"/>' +
                '<path d="M122 60 C132 61, 141 69, 146 78 L150 106 L110 108 L109 82 C110 72, 116 63, 122 60 Z" fill="#e01111" stroke="#111827" stroke-width="4"/>' +
                '<circle cx="128" cy="48" r="28" fill="#f4d0a4" stroke="#111827" stroke-width="4"/>' +
                '<path d="M92 49 C102 22, 118 12, 138 12 C152 12, 162 18, 168 32 L164 57 L93 54 Z" fill="#36b5c7" stroke="#111827" stroke-width="4"/>' +
                '<path d="M98 31 C110 18, 125 15, 141 17 C152 18, 162 24, 166 35" stroke="#e7ba2f" stroke-width="8" fill="none"/>' +
                '<circle cx="118" cy="50" r="7" fill="#fff" stroke="#111827" stroke-width="3"/>' +
                '<circle cx="126" cy="49" r="2" fill="#111827"/>' +
                '<path d="M121 64 C130 68, 136 74, 140 82" stroke="#111827" stroke-width="3" fill="none"/>' +
                '<path d="M150 80 C169 77, 186 82, 192 94" stroke="#111827" stroke-width="3" fill="none"/>' +
                '<path d="M110 104 L84 120 L93 128 L118 110 Z" fill="#8c4d1c" stroke="#111827" stroke-width="4"/>' +
                '<path d="M118 112 L102 146 L110 146 L132 112 Z" fill="#8c4d1c" stroke="#111827" stroke-width="4"/>' +
                '<path d="M128 110 L148 144 L158 144 L142 110 Z" fill="#8c4d1c" stroke="#111827" stroke-width="4"/>' +
                '<path d="M132 52 L112 66" stroke="#111827" stroke-width="4" fill="none"/>' +
                '<path d="M102 28 L164 21" stroke="#111827" stroke-width="4" fill="none"/>' +
                '<path d="M157 66 L193 64" stroke="#111827" stroke-width="4" fill="none"/>' +
                '<path d="M185 61 L216 57" stroke="#111827" stroke-width="4" fill="none"/>' +
                '<path d="M206 57 L208 42" stroke="#111827" stroke-width="4" fill="none"/>' +
                '<path d="M190 62 L196 102" stroke="#111827" stroke-width="4" fill="none"/>' +
                '<circle cx="208" cy="40" r="12" fill="#ffea00" stroke="#111827" stroke-width="4"/>' +
                '<path d="M202 40 L208 29 L214 40" stroke="#111827" stroke-width="4" fill="none"/>' +
                '</g>' +
              '</svg>'
            : '<svg viewBox="0 0 64 40" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">' +
            '<circle cx="13" cy="34" r="4.5"/>' +                       // Rad
            '<path d="M4 21 L28 21 L24 31 L10 31 Z" fill="currentColor" fill-opacity=".25"/>' + // Wanne
            '<path d="M28 21 L42 26"/>' +                                // Griff
            '<circle cx="49" cy="9" r="4" fill="currentColor" fill-opacity=".25"/>' +           // Kopf
            '<path d="M49 13 L46 25"/>' +                                // Körper
            '<path d="M47 17 L40 25"/>' +                                // Arm
            '<path d="M46 25 L41 37"/><path d="M46 25 L52 37"/>' +       // Beine
        '</svg>';
    }

    function renderCharacter() {
        var isScooter1 = character === 'scooter';
        var isScooter2 = character2 === 'scooter';
        man.classList.toggle('sf-wheelchair', isScooter1);
        player2.classList.toggle('sf-wheelchair', isScooter2);
        man.classList.toggle('sf-scooter', isScooter1);
        player2.classList.toggle('sf-scooter', isScooter2);
        man.innerHTML = characterSvg(character);
        player2.innerHTML = characterSvg(character2);
        man.style.color = playerColor1;
        player2.style.color = playerColor2;
    }
    renderCharacter();

    // ── Maussteuerung: Männchen läuft zur Cursor-Position ─────
    var MAN_W     = 112;
    var MAN_SPEED = 4;                         // px pro Frame
    var manX      = 8;
    var manTarget = window.innerWidth / 2;
    var player2X = window.innerWidth - MAN_W - 8;
    var keys = {};

    document.addEventListener('mousemove', function (e) {
        manTarget = e.clientX - MAN_W / 2;
    });
    document.addEventListener('keydown', function (e) {
        if (!gameActive || !multiplayer) return;
        if (['ArrowLeft', 'ArrowRight', 'ArrowUp', ' '].includes(e.key)) e.preventDefault();
        keys[e.key] = true;
        if ((e.key === 'ArrowUp' || e.key === ' ') && !player2.classList.contains('sf-jump')) {
            player2.classList.add('sf-jump');
            setTimeout(function () { player2.classList.remove('sf-jump'); }, 650);
        }
    });
    document.addEventListener('keyup', function (e) { keys[e.key] = false; });

    function moveMan() {
        if (gameActive) {
            var maxX = window.innerWidth - MAN_W - 4;
            var target = Math.min(Math.max(manTarget, 4), maxX);
            var dx = target - manX;
            if (Math.abs(dx) > MAN_SPEED) {
                manX += Math.sign(dx) * MAN_SPEED;
                man.classList.toggle('flip', dx < 0); // Blickrichtung
            } else {
                manX = target;
            }
            man.style.left = manX + 'px';
            if (multiplayer) {
                var p2Direction = (keys.ArrowRight ? 1 : 0) - (keys.ArrowLeft ? 1 : 0);
                player2X = Math.min(Math.max(player2X + p2Direction * MAN_SPEED, 4), maxX);
                if (p2Direction) player2.classList.toggle('flip', p2Direction < 0);
                player2.style.left = player2X + 'px';
            }
        }
        requestAnimationFrame(moveMan);
    }
    requestAnimationFrame(moveMan);

    function syncScene() {
        document.body.classList.toggle('sf-scene-on', enabled || gameActive);
        document.body.classList.toggle('sf-game-on', gameActive);
        document.body.classList.toggle('sf-multiplayer-on', gameActive && multiplayer);
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
            '<label class="sf-panel-row sf-character-row">' +
                '<span>Figur</span>' +
                '<select id="sf-character"><option value="wheelbarrow">Schubkarre</option><option value="scooter">Roller</option><option value="image">Originalbild</option></select>' +
            '</label>' +
            '<label class="sf-panel-row sf-multiplayer-row">' +
                '<span>Lokaler Mehrspieler</span>' +
                '<span class="sf-switch"><input type="checkbox" id="sf-multiplayer"><span class="sf-slider"></span></span>' +
            '</label>' +
            '<div id="sf-player-2-options">' +
                '<label class="sf-panel-row sf-character-row"><span>Spieler 2: Figur</span><select id="sf-character-2"><option value="wheelbarrow">Schubkarre</option><option value="scooter">Roller</option><option value="image">Originalbild</option></select></label>' +
                '<div class="sf-color-options"><label>Spieler 1 Farbe <input type="color" id="sf-color-1"></label><label>Spieler 2 Farbe <input type="color" id="sf-color-2"></label></div>' +
                '<small class="sf-hint">Spieler 1: Maus und Linksklick. Spieler 2: Pfeiltasten und ↑ oder Leertaste.</small>' +
            '</div>' +
        '</div>' +
        '<div class="sf-panel-row sf-panel-col sf-scores-row">' +
            '<span><i class="bi bi-trophy"></i> Bestenliste</span>' +
            '<ol id="sf-scores"></ol>' +
        '</div>';
    document.body.appendChild(panel);

    var characterInput = panel.querySelector('#sf-character');
    characterInput.value = character;
    characterInput.addEventListener('change', function () {
        character = characterInput.value;
        localStorage.setItem(KEY_CHARACTER, character);
        renderCharacter();
        syncMultiplayerUi();
    });

    var multiplayerInput = panel.querySelector('#sf-multiplayer');
    var character2Input = panel.querySelector('#sf-character-2');
    var color1Input = panel.querySelector('#sf-color-1');
    var color2Input = panel.querySelector('#sf-color-2');

    function syncMultiplayerUi() {
        multiplayerInput.checked = multiplayer;
        character2Input.value = character2;
        color1Input.value = playerColor1;
        color2Input.value = playerColor2;
        panel.querySelector('#sf-player-2-options').classList.toggle('open', multiplayer);
        panel.querySelector('.sf-color-options').classList.toggle('open', multiplayer && character === character2);
    }
    multiplayerInput.addEventListener('change', function () {
        multiplayer = multiplayerInput.checked;
        localStorage.setItem(KEY_MULTIPLAYER, multiplayer ? '1' : '0');
        syncMultiplayerUi();
    });
    character2Input.addEventListener('change', function () {
        character2 = character2Input.value;
        localStorage.setItem(KEY_CHARACTER_2, character2);
        renderCharacter();
        syncMultiplayerUi();
    });
    color1Input.addEventListener('input', function () {
        playerColor1 = color1Input.value;
        if (character === character2 && playerColor1 === playerColor2) {
            playerColor2 = '#22d3ee';
            color2Input.value = playerColor2;
            localStorage.setItem(KEY_COLOR_2, playerColor2);
        }
        localStorage.setItem(KEY_COLOR_1, playerColor1);
        renderCharacter();
    });
    color2Input.addEventListener('input', function () {
        playerColor2 = color2Input.value;
        if (character === character2 && playerColor2 === playerColor1) {
            playerColor2 = '#22d3ee';
            color2Input.value = playerColor2;
        }
        localStorage.setItem(KEY_COLOR_2, playerColor2);
        renderCharacter();
    });
    syncMultiplayerUi();

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
    var GAME_SECONDS  = 300;
    var highscore  = parseInt(localStorage.getItem(KEY_HIGHSCORE) || '0', 10);
    var gameActive = false;
    var gameScore  = 0;
    var player1Score = 0;
    var player2Score = 0;
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
                '<label class="sf-panel-row sf-start-setting sf-character-row">' +
                    '<span>Figur</span>' +
                    '<select id="sf-character-start"><option value="wheelbarrow">Schubkarre</option><option value="scooter">Roller</option><option value="image">Originalbild</option></select>' +
                '</label>' +
                '<label class="sf-panel-row sf-start-setting">' +
                    '<span>Lokaler Mehrspieler</span>' +
                    '<span class="sf-switch"><input type="checkbox" id="sf-multiplayer-start"><span class="sf-slider"></span></span>' +
                '</label>' +
                '<div id="sf-start-player-2-options">' +
                    '<label class="sf-panel-row sf-start-setting sf-character-row"><span>Spieler 2: Figur</span><select id="sf-character-2-start"><option value="wheelbarrow">Schubkarre</option><option value="scooter">Roller</option><option value="image">Originalbild</option></select></label>' +
                    '<div class="sf-color-options"><label>Spieler 1 <input type="color" id="sf-color-1-start"></label><label>Spieler 2 <input type="color" id="sf-color-2-start"></label></div>' +
                    '<small class="sf-hint">Spieler 1: Maus und Linksklick. Spieler 2: Pfeiltasten und ↑ oder Leertaste.</small>' +
                '</div>';
            var characterStart = startContent.querySelector('#sf-character-start');
            characterStart.value = character;
            characterStart.addEventListener('change', function () {
                character = characterStart.value;
                localStorage.setItem(KEY_CHARACTER, character);
                characterInput.value = character;
                renderCharacter();
            });
            var multiplayerStart = startContent.querySelector('#sf-multiplayer-start');
            var character2Start = startContent.querySelector('#sf-character-2-start');
            var color1Start = startContent.querySelector('#sf-color-1-start');
            var color2Start = startContent.querySelector('#sf-color-2-start');
            function syncStartMultiplayerUi() {
                multiplayerStart.checked = multiplayer;
                character2Start.value = character2;
                color1Start.value = playerColor1;
                color2Start.value = playerColor2;
                startContent.querySelector('#sf-start-player-2-options').classList.toggle('open', multiplayer);
                startContent.querySelector('#sf-start-player-2-options .sf-color-options').classList.toggle('open', multiplayer && character === character2);
            }
            multiplayerStart.addEventListener('change', function () {
                multiplayer = multiplayerStart.checked;
                localStorage.setItem(KEY_MULTIPLAYER, multiplayer ? '1' : '0');
                multiplayerInput.checked = multiplayer;
                syncStartMultiplayerUi();
                syncMultiplayerUi();
            });
            character2Start.addEventListener('change', function () {
                character2 = character2Start.value;
                localStorage.setItem(KEY_CHARACTER_2, character2);
                character2Input.value = character2;
                renderCharacter();
                syncStartMultiplayerUi();
                syncMultiplayerUi();
            });
            color1Start.addEventListener('input', function () {
                playerColor1 = color1Start.value;
                localStorage.setItem(KEY_COLOR_1, playerColor1);
                color1Input.value = playerColor1;
                renderCharacter();
            });
            color2Start.addEventListener('input', function () {
                playerColor2 = color2Start.value;
                if (character === character2 && playerColor2 === playerColor1) playerColor2 = '#22d3ee';
                localStorage.setItem(KEY_COLOR_2, playerColor2);
                color2Start.value = playerColor2;
                color2Input.value = playerColor2;
                renderCharacter();
            });
            syncStartMultiplayerUi();
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
        hud.textContent = multiplayer
            ? 'S1: ' + player1Score + '  |  S2: ' + player2Score + '  ·  ' + gameLeft + 's'
            : '\u2744 ' + gameScore + '  ·  ' + gameLeft + 's';
    }

    function startGame() {
        if (gameActive) return;
        gameActive = true;
        gameScore  = 0;
        player1Score = 0;
        player2Score = 0;
        gameLeft   = GAME_SECONDS;
        container.innerHTML = '';
        landed.length = 0;
        container.classList.add('sf-game');
        result.classList.remove('open');
        panel.classList.remove('open');
        resetPowers();
        updateHud();
        hud.classList.add('open');
        syncScene();
        stoneTimer = setInterval(spawnStone, 2600);
        boxTimer = setInterval(spawnPowerBox, 9000);
        mushroomTimer = setInterval(spawnMushroom, 11000);
        gameTicker = setInterval(function () {
            gameLeft--;
            if (gameLeft <= 0) { endGame(); } else { updateHud(); }
        }, 1000);
    }

    function endGame(died, defeatedPlayer) {
        if (!gameActive) return;
        gameActive = false;
        clearInterval(gameTicker);
        clearInterval(stoneTimer);
        clearInterval(boxTimer);
        clearInterval(mushroomTimer);
        hud.classList.remove('open');

        if (died) {
            // Todes-Effekt abspielen, Männchen bleibt solange sichtbar
            var fallenPlayer = defeatedPlayer || man;
            fallenPlayer.classList.add('sf-dead');
            var rect = fallenPlayer.getBoundingClientRect();
            burstAt(rect.left + rect.width / 2, rect.top + rect.height / 2);
            document.body.classList.add('sf-shake');
            setTimeout(function () {
                fallenPlayer.classList.remove('sf-dead');
                document.body.classList.remove('sf-shake');
                showResult(true);
            }, 1150);
        } else {
            showResult(false);
        }
    }

    function showResult(died) {
        container.classList.remove('sf-game');
        container.querySelectorAll('.sf-stone, .sf-powerup, .sf-mushroom').forEach(function (s) { s.remove(); });
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

    function showLabel(x, y, text, color) {
        var fx = document.createElement('span');
        fx.className = 'sf-points sf-power-label';
        fx.style.color = color;
        fx.textContent = text;
        fx.style.left = x + 'px';
        fx.style.top  = y + 'px';
        document.body.appendChild(fx);
        setTimeout(function () { fx.remove(); }, 900);
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

    // ── Power-Ups: Überraschungsbox (fällt) & Pilz (wächst aus dem Boden) ──
    // Jede Fähigkeit hat eine eigene Farbe: Sprungkraft = gelb, Schutzschild = grün, Geisterlauf = lila
    var boxTimer = null;
    var mushroomTimer = null;
    var POWER_TYPES = {
        jump:   { color: '#fbbf24', label: '\u2b06 Sprungkraft', duration: 8000 },
        shield: { color: '#22c55e', label: '\ud83d\udee1 Schutzschild', duration: 0 },
        ghost:  { color: '#a855f7', label: '\ud83d\udc7b Geisterlauf', duration: 8000 }
    };
    var POWER_KEYS = Object.keys(POWER_TYPES);
    var powerState = {
        man:     { jumpUntil: 0, ghostUntil: 0, shield: false },
        player2: { jumpUntil: 0, ghostUntil: 0, shield: false }
    };

    function resetPowers() {
        powerState.man     = { jumpUntil: 0, ghostUntil: 0, shield: false };
        powerState.player2 = { jumpUntil: 0, ghostUntil: 0, shield: false };
        man.classList.remove('sf-power-jump', 'sf-power-shield', 'sf-power-ghost');
        player2.classList.remove('sf-power-jump', 'sf-power-shield', 'sf-power-ghost');
    }

    function applyPower(type, target) {
        var el = target === 'player2' ? player2 : man;
        var state = powerState[target];
        var def = POWER_TYPES[type];
        if (type === 'shield') {
            state.shield = true;
            el.classList.add('sf-power-shield');
            return;
        }
        el.classList.add('sf-power-' + type);
        var until = Date.now() + def.duration;
        state[type + 'Until'] = until;
        setTimeout(function () {
            if (Date.now() >= state[type + 'Until']) el.classList.remove('sf-power-' + type);
        }, def.duration + 30);
    }

    // Trifft ein Stein: Geisterlauf ignoriert ihn, Schutzschild wird verbraucht, sonst Tod
    function consumeHit(target, el) {
        var state = powerState[target];
        if (Date.now() < state.ghostUntil) return true;
        if (state.shield) {
            state.shield = false;
            el.classList.remove('sf-power-shield');
            el.classList.add('sf-shield-pop');
            setTimeout(function () { el.classList.remove('sf-shield-pop'); }, 500);
            return true;
        }
        return false;
    }

    function collectPower(el, target) {
        if (el.classList.contains('sf-collected')) return;
        el.classList.add('sf-collected');
        var type = el.dataset.power;
        var def = POWER_TYPES[type];
        var r = el.getBoundingClientRect();
        showLabel(r.left + r.width / 2, r.top - 8, def.label, def.color);
        el.remove();
        applyPower(type, target);
    }

    function spawnPowerBox() {
        if (!gameActive || reducedMotion.matches) return;
        var type = POWER_KEYS[Math.floor(Math.random() * POWER_KEYS.length)];
        var el = document.createElement('span');
        el.className = 'sf-powerup sf-power-' + type;
        el.dataset.power = type;
        el.style.left = (Math.random() * (window.innerWidth - 40)) + 'px';
        var dist = window.innerHeight + 50 - GROUND_H - 30;
        el.style.setProperty('--sf-dist', dist + 'px');
        el.style.animationDuration = (3 + Math.random() * 1.5).toFixed(2) + 's';
        container.appendChild(el);
    }

    function spawnMushroom() {
        if (!gameActive || reducedMotion.matches) return;
        var type = POWER_KEYS[Math.floor(Math.random() * POWER_KEYS.length)];
        var el = document.createElement('span');
        el.className = 'sf-mushroom sf-power-' + type;
        el.dataset.power = type;
        el.style.left = (Math.random() * (window.innerWidth - 40)) + 'px';
        container.appendChild(el);
        setTimeout(function () { if (el.isConnected) el.remove(); }, 6000);
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

    // Fangzone: das ganze Männchen fängt Flocken ein
    setInterval(function () {
        if (!gameActive) return;
        var rect = man.getBoundingClientRect();

        var flakes = container.querySelectorAll('.snowflake:not(.sf-landed)');

        for (var i = 0; i < flakes.length; i++) {
            var el = flakes[i];
            var fx = el.getBoundingClientRect();
            var cx = fx.left + fx.width / 2;
            var hitX = fx.right > rect.left && fx.left < rect.right;
            var hitY = fx.bottom > rect.top && fx.top < rect.bottom;
            if (hitX && hitY) {
                var pts = flakePoints(el);
                gameScore += pts;
                player1Score += pts;
                updateHud();
                showPoints(cx, fx.top - 8, pts);
                el.remove();
            }
        }

        if (multiplayer) {
            var player2Rect = player2.getBoundingClientRect();

            for (var p = flakes.length - 1; p >= 0; p--) {
                var player2Flake = flakes[p];
                if (!player2Flake.isConnected) continue;
                var player2FlakeRect = player2Flake.getBoundingClientRect();
                var player2FlakeX = player2FlakeRect.left + player2FlakeRect.width / 2;
                if (player2FlakeRect.right > player2Rect.left && player2FlakeRect.left < player2Rect.right &&
                    player2FlakeRect.bottom > player2Rect.top && player2FlakeRect.top < player2Rect.bottom) {
                    var player2Points = flakePoints(player2Flake);
                    gameScore += player2Points;
                    player2Score += player2Points;
                    updateHud();
                    showPoints(player2FlakeX, player2FlakeRect.top - 8, player2Points);
                    player2Flake.remove();
                }
            }
        }

        // Power-Ups: das ganze Männchen fängt sie ein (nicht nur die Wanne)
        var powerups = container.querySelectorAll('.sf-powerup:not(.sf-collected), .sf-mushroom:not(.sf-collected)');
        for (var pu = 0; pu < powerups.length; pu++) {
            var puEl = powerups[pu];
            var puRect = puEl.getBoundingClientRect();
            if (puRect.right > rect.left && puRect.left < rect.right &&
                puRect.bottom > rect.top && puRect.top < rect.bottom) {
                collectPower(puEl, 'man');
            } else if (multiplayer) {
                var p2FullRect = player2.getBoundingClientRect();
                if (puRect.right > p2FullRect.left && puRect.left < p2FullRect.right &&
                    puRect.bottom > p2FullRect.top && puRect.top < p2FullRect.bottom) {
                    collectPower(puEl, 'player2');
                }
            }
        }

        // Steinberührung = Tod, es sei denn man springt (Geisterlauf/Schutzschild schützen)
        if (!jumping) {
            var stones = container.querySelectorAll('.sf-stone:not(.sf-stone-fade)');
            for (var k = 0; k < stones.length; k++) {
                var sx = stones[k].getBoundingClientRect();
                if (sx.right > rect.left + 12 && sx.left < rect.right - 12 &&
                    sx.bottom > rect.top && sx.top < rect.bottom) {
                    if (!consumeHit('man', man)) { endGame(true, man); return; }
                }
                if (multiplayer && !player2.classList.contains('sf-jump')) {
                    var player2StoneRect = player2.getBoundingClientRect();
                    if (sx.right > player2StoneRect.left + 12 && sx.left < player2StoneRect.right - 12 &&
                        sx.bottom > player2StoneRect.top && sx.top < player2StoneRect.bottom) {
                        if (!consumeHit('player2', player2)) { endGame(true, player2); return; }
                    }
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

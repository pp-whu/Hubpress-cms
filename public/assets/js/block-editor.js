/**
 * HuberCMS Block Editor — block-editor.js
 * Gutenberg-style block editor, vanilla JS, no dependencies.
 */
(function (global) {
  'use strict';

  /* ================================================================
     BLOCK REGISTRY
     ================================================================ */
  const BlockRegistry = {
    _blocks: {},
    register(name, def) { this._blocks[name] = { name, ...def }; },
    get(name)           { return this._blocks[name] || null; },
    all()               { return Object.values(this._blocks); },
    byCategory(cat)     { return this.all().filter(b => b.category === cat); }
  };

  /* ================================================================
     UTILITIES
     ================================================================ */
  let _uid = 0;
  const uid    = () => 'hb-' + (++_uid);
  const esc    = s  => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  const clamp  = (n, lo, hi) => Math.max(lo, Math.min(hi, n));

  /* ================================================================
     STATE
     ================================================================ */
  let _state   = { blocks: [], selected: null, insertAfter: null };
  let _target  = null;   // hidden textarea
  let _canvas  = null;
  let _dragged = null;

  function getBlock(id) { return _state.blocks.find(b => b.id === id) || null; }
  function getIndex(id) { return _state.blocks.findIndex(b => b.id === id); }

  function commit() {
    if (_target) _target.value = JSON.stringify(_state.blocks.map(b => ({ type: b.type, attrs: b.attrs })));
  }

  /* ================================================================
     BLOCK REGISTRATION — 15 Types
     ================================================================ */

  // 1. PARAGRAPH
  BlockRegistry.register('paragraph', {
    title: 'Absatz', icon: 'bi-paragraph', category: 'text',
    defaults: { content: '', align: 'left' },
    preview(b) {
      return `<div class="hb-preview-para">${b.attrs.content || '<em>Leerer Absatz</em>'}</div>`;
    },
    edit(b) {
      return `
        <div class="hb-inline-toolbar">
          <button class="hb-it-btn" onclick="document.execCommand('bold')" title="Fett"><strong>B</strong></button>
          <button class="hb-it-btn" onclick="document.execCommand('italic')" title="Kursiv"><em>I</em></button>
          <button class="hb-it-btn" onclick="document.execCommand('underline')" title="Unterstrichen"><u>U</u></button>
          <div class="hb-it-sep"></div>
          <button class="hb-it-btn" onclick="document.execCommand('justifyLeft')" title="Links"><i class="bi bi-text-left"></i></button>
          <button class="hb-it-btn" onclick="document.execCommand('justifyCenter')" title="Mitte"><i class="bi bi-text-center"></i></button>
          <button class="hb-it-btn" onclick="document.execCommand('justifyRight')" title="Rechts"><i class="bi bi-text-right"></i></button>
        </div>
        <div class="hb-richtext" contenteditable="true" data-attr="content"
             data-placeholder="Absatz schreiben…"
             style="text-align:${esc(b.attrs.align||'left')}">${b.attrs.content||''}</div>`;
    }
  });

  // 2. HEADING
  BlockRegistry.register('heading', {
    title: 'Überschrift', icon: 'bi-type-h1', category: 'text',
    defaults: { content: '', level: 2, align: 'left' },
    preview(b) {
      const sizes = {'1':'1.6em','2':'1.3em','3':'1.1em','4':'.95em','5':'.9em','6':'.85em'};
      return `<div class="hb-preview-heading" style="font-size:${sizes[b.attrs.level]||'1.3em'};text-align:${b.attrs.align||'left'}">${b.attrs.content||'<em>Leere Überschrift</em>'}</div>`;
    },
    edit(b) {
      const lvl = b.attrs.level || 2;
      return `
        <div class="hb-inline-toolbar">
          ${[1,2,3,4,5,6].map(l=>`<button class="hb-it-btn${lvl==l?' active':''}" onclick="HuberBlocks._setAttr('${b.id}','level',${l});HuberBlocks._redrawBlock('${b.id}')">H${l}</button>`).join('')}
          <div class="hb-it-sep"></div>
          <button class="hb-it-btn" onclick="document.execCommand('justifyLeft')"><i class="bi bi-text-left"></i></button>
          <button class="hb-it-btn" onclick="document.execCommand('justifyCenter')"><i class="bi bi-text-center"></i></button>
          <button class="hb-it-btn" onclick="document.execCommand('justifyRight')"><i class="bi bi-text-right"></i></button>
        </div>
        <div class="hb-richtext hb-heading-${lvl}" contenteditable="true" data-attr="content"
             data-placeholder="Überschrift…"
             style="text-align:${esc(b.attrs.align||'left')}">${b.attrs.content||''}</div>`;
    }
  });

  // 3. IMAGE
  BlockRegistry.register('image', {
    title: 'Bild', icon: 'bi-image', category: 'media',
    defaults: { url: '', alt: '', caption: '', align: 'center', width: '100%' },
    preview(b) {
      if (!b.attrs.url) return `<div class="hb-preview-meta"><i class="bi bi-image"></i> Kein Bild gewählt</div>`;
      return `<div class="hb-preview-image" style="text-align:${b.attrs.align||'center'}"><img src="${esc(b.attrs.url)}" alt="${esc(b.attrs.alt)}" style="max-width:${esc(b.attrs.width||'100%')}"></div>`;
    },
    edit(b) {
      return `
        <div class="hb-field"><label class="hb-label">Bild-URL</label>
          <div style="display:flex;gap:.4rem">
            <input class="hb-input" type="url" data-attr="url" value="${esc(b.attrs.url)}" placeholder="https://… oder /uploads/…">
            <button class="hb-it-btn" onclick="HuberBlocks._mediaPicker('${b.id}','url')" title="Medien"><i class="bi bi-folder2-open"></i></button>
          </div>
        </div>
        <div class="hb-field"><label class="hb-label">Alt-Text</label>
          <input class="hb-input" type="text" data-attr="alt" value="${esc(b.attrs.alt)}" placeholder="Bildbeschreibung"></div>
        <div class="hb-field"><label class="hb-label">Beschriftung</label>
          <input class="hb-input" type="text" data-attr="caption" value="${esc(b.attrs.caption)}" placeholder="Bildunterschrift…"></div>
        <div style="display:flex;gap:1rem">
          <div class="hb-field" style="flex:1"><label class="hb-label">Ausrichtung</label>
            <select class="hb-select" data-attr="align" style="width:100%">
              <option value="left" ${b.attrs.align==='left'?'selected':''}>Links</option>
              <option value="center" ${b.attrs.align==='center'||!b.attrs.align?'selected':''}>Mitte</option>
              <option value="right" ${b.attrs.align==='right'?'selected':''}>Rechts</option>
            </select>
          </div>
          <div class="hb-field" style="flex:1"><label class="hb-label">Breite</label>
            <select class="hb-select" data-attr="width" style="width:100%">
              <option value="25%" ${b.attrs.width==='25%'?'selected':''}>25%</option>
              <option value="50%" ${b.attrs.width==='50%'?'selected':''}>50%</option>
              <option value="75%" ${b.attrs.width==='75%'?'selected':''}>75%</option>
              <option value="100%" ${b.attrs.width==='100%'||!b.attrs.width?'selected':''}>100%</option>
            </select>
          </div>
        </div>
        ${b.attrs.url ? `<div style="margin-top:.5rem;text-align:${b.attrs.align||'center'}"><img src="${esc(b.attrs.url)}" style="max-width:${esc(b.attrs.width||'100%')};max-height:200px;border-radius:8px;object-fit:cover"></div>` : ''}`;
    }
  });

  // 4. GALLERY
  BlockRegistry.register('gallery', {
    title: 'Galerie', icon: 'bi-images', category: 'media',
    defaults: { images: [], columns: 3 },
    preview(b) {
      const imgs = b.attrs.images || [];
      const cols = b.attrs.columns || 3;
      if (!imgs.length) return `<div class="hb-preview-meta"><i class="bi bi-images"></i> Galerie leer</div>`;
      return `<div class="hb-gallery-grid" style="grid-template-columns:repeat(${cols},1fr)">
        ${imgs.slice(0,6).map(u=>`<div class="hb-gallery-cell"><img src="${esc(u)}" alt=""></div>`).join('')}
        ${imgs.length>6?`<div class="hb-gallery-cell" style="background:rgba(99,102,241,.15);display:flex;align-items:center;justify-content:center;font-size:.75rem;color:#a5b4fc">+${imgs.length-6}</div>`:''}
      </div>`;
    },
    edit(b) {
      const imgs = b.attrs.images || [];
      return `
        <div class="hb-field"><label class="hb-label">Spalten</label>
          <select class="hb-select" data-attr="columns">
            ${[2,3,4].map(c=>`<option value="${c}" ${(b.attrs.columns||3)==c?'selected':''}>${c} Spalten</option>`).join('')}
          </select>
        </div>
        <div class="hb-field"><label class="hb-label">Bilder (eine URL pro Zeile)</label>
          <textarea class="hb-input" rows="5" data-attr-json="images" style="font-family:monospace;font-size:.8rem">${imgs.join('\n')}</textarea>
        </div>
        <div class="hb-gallery-grid" style="grid-template-columns:repeat(${b.attrs.columns||3},1fr);margin-top:.5rem">
          ${imgs.map(u=>`<div class="hb-gallery-cell"><img src="${esc(u)}"></div>`).join('')}
        </div>`;
    }
  });

  // 5. QUOTE
  BlockRegistry.register('quote', {
    title: 'Zitat', icon: 'bi-quote', category: 'text',
    defaults: { content: '', author: '', style: 'default' },
    preview(b) {
      return `<div class="hb-quote-preview">
        <div class="hb-quote-text">${b.attrs.content || '<em>Leer</em>'}</div>
        ${b.attrs.author?`<div class="hb-quote-author">— ${esc(b.attrs.author)}</div>`:''}
      </div>`;
    },
    edit(b) {
      return `
        <div class="hb-field"><label class="hb-label">Zitat</label>
          <div class="hb-richtext" contenteditable="true" data-attr="content"
               data-placeholder="Zitat eingeben…"
               style="border-left:3px solid #6366f1;padding-left:.75rem;font-style:italic">${b.attrs.content||''}</div>
        </div>
        <div class="hb-field"><label class="hb-label">Autor / Quelle</label>
          <input class="hb-input" type="text" data-attr="author" value="${esc(b.attrs.author)}" placeholder="Max Mustermann">
        </div>`;
    }
  });

  // 6. LIST
  BlockRegistry.register('list', {
    title: 'Liste', icon: 'bi-list-ul', category: 'text',
    defaults: { items: [''], ordered: false },
    preview(b) {
      const tag = b.attrs.ordered ? 'ol' : 'ul';
      const items = (b.attrs.items||['']).filter(Boolean);
      return `<${tag} style="margin:0;padding-left:1.2rem;font-size:.88rem;color:rgba(255,255,255,.65)">${items.map(i=>`<li>${esc(i)}</li>`).join('')}</${tag}>`;
    },
    edit(b) {
      const items = b.attrs.items || [''];
      return `
        <div class="hb-inline-toolbar">
          <button class="hb-it-btn${!b.attrs.ordered?' active':''}" onclick="HuberBlocks._setAttr('${b.id}','ordered',false);HuberBlocks._redrawBlock('${b.id}')"><i class="bi bi-list-ul"></i> Aufzählung</button>
          <button class="hb-it-btn${b.attrs.ordered?' active':''}" onclick="HuberBlocks._setAttr('${b.id}','ordered',true);HuberBlocks._redrawBlock('${b.id}')"><i class="bi bi-list-ol"></i> Nummeriert</button>
        </div>
        <div id="hb-list-items-${b.id}">
          ${items.map((item,i)=>`
            <div style="display:flex;gap:.4rem;margin-bottom:.3rem">
              <span style="color:rgba(255,255,255,.25);min-width:1.2rem;line-height:2;font-size:.8rem">${b.attrs.ordered?i+1+'.':'•'}</span>
              <input class="hb-input hb-list-item" data-block="${b.id}" data-idx="${i}" value="${esc(item)}" placeholder="Listenpunkt…" style="flex:1">
              <button class="hb-it-btn" onclick="HuberBlocks._removeListItem('${b.id}',${i})" style="color:#f87171"><i class="bi bi-x"></i></button>
            </div>`).join('')}
        </div>
        <button class="hb-it-btn" onclick="HuberBlocks._addListItem('${b.id}')" style="margin-top:.3rem"><i class="bi bi-plus me-1"></i>Punkt hinzufügen</button>`;
    }
  });

  // 7. SEPARATOR
  BlockRegistry.register('separator', {
    title: 'Trennlinie', icon: 'bi-dash-lg', category: 'layout',
    defaults: { style: 'solid', width: '100%', color: '#334155' },
    preview(b) {
      const style = b.attrs.style || 'solid';
      return `<hr style="border:none;border-top:2px ${style} ${esc(b.attrs.color||'#334155')};width:${esc(b.attrs.width||'100%')};margin:.5rem auto">`;
    },
    edit(b) {
      return `
        <div style="display:flex;gap:1rem">
          <div class="hb-field" style="flex:1"><label class="hb-label">Stil</label>
            <select class="hb-select" data-attr="style" style="width:100%">
              <option value="solid" ${b.attrs.style==='solid'||!b.attrs.style?'selected':''}>Durchgezogen</option>
              <option value="dashed" ${b.attrs.style==='dashed'?'selected':''}>Gestrichelt</option>
              <option value="dotted" ${b.attrs.style==='dotted'?'selected':''}>Gepunktet</option>
            </select>
          </div>
          <div class="hb-field" style="flex:1"><label class="hb-label">Breite</label>
            <select class="hb-select" data-attr="width" style="width:100%">
              ${['25%','50%','75%','100%'].map(w=>`<option value="${w}" ${(b.attrs.width||'100%')===w?'selected':''}>${w}</option>`).join('')}
            </select>
          </div>
          <div class="hb-field"><label class="hb-label">Farbe</label>
            <input type="color" data-attr="color" value="${b.attrs.color||'#334155'}" style="height:32px;border-radius:5px;border:1px solid rgba(255,255,255,.1);background:transparent;cursor:pointer">
          </div>
        </div>
        <hr style="border:none;border-top:2px ${esc(b.attrs.style||'solid')} ${esc(b.attrs.color||'#334155')};width:${esc(b.attrs.width||'100%')};margin:.5rem auto">`;
    }
  });

  // 8. BUTTON
  BlockRegistry.register('button', {
    title: 'Button', icon: 'bi-hand-index', category: 'layout',
    defaults: { buttons: [{ text: 'Mehr erfahren', url: '#', style: 'primary', target: '_self' }] },
    preview(b) {
      const btns = b.attrs.buttons || [];
      return `<div class="hb-preview-button-wrap">${btns.map(btn=>`<span class="hb-preview-btn-item ${esc(btn.style||'primary')}">${esc(btn.text||'Button')}</span>`).join('')}</div>`;
    },
    edit(b) {
      const btns = b.attrs.buttons || [{ text: '', url: '', style: 'primary', target: '_self' }];
      return `
        <div id="hb-btns-${b.id}">
          ${btns.map((btn,i)=>`
            <div style="background:rgba(255,255,255,.04);border-radius:8px;padding:.6rem;margin-bottom:.4rem">
              <div style="display:flex;gap:.4rem;margin-bottom:.3rem">
                <input class="hb-input hb-btn-field" data-block="${b.id}" data-idx="${i}" data-key="text" value="${esc(btn.text)}" placeholder="Beschriftung" style="flex:1">
                <input class="hb-input hb-btn-field" data-block="${b.id}" data-idx="${i}" data-key="url" value="${esc(btn.url)}" placeholder="URL" style="flex:1.5">
                <button class="hb-it-btn" onclick="HuberBlocks._removeBtn('${b.id}',${i})" style="color:#f87171"><i class="bi bi-x"></i></button>
              </div>
              <div style="display:flex;gap:.4rem">
                <select class="hb-select hb-btn-field" data-block="${b.id}" data-idx="${i}" data-key="style" style="flex:1">
                  <option value="primary" ${btn.style==='primary'?'selected':''}>Primary</option>
                  <option value="secondary" ${btn.style==='secondary'?'selected':''}>Secondary</option>
                  <option value="outline" ${btn.style==='outline'?'selected':''}>Outline</option>
                </select>
                <select class="hb-select hb-btn-field" data-block="${b.id}" data-idx="${i}" data-key="target" style="flex:1">
                  <option value="_self" ${btn.target!=='_blank'?'selected':''}>Gleicher Tab</option>
                  <option value="_blank" ${btn.target==='_blank'?'selected':''}>Neuer Tab</option>
                </select>
              </div>
            </div>`).join('')}
        </div>
        <button class="hb-it-btn" onclick="HuberBlocks._addBtn('${b.id}')"><i class="bi bi-plus me-1"></i>Button hinzufügen</button>`;
    }
  });

  // 9. COLUMNS
  BlockRegistry.register('columns', {
    title: 'Spalten', icon: 'bi-layout-split', category: 'layout',
    defaults: { count: 2, cols: ['', ''] },
    preview(b) {
      const count = b.attrs.count || 2;
      const cols  = b.attrs.cols || [];
      return `<div class="hb-columns-preview cols-${count}">
        ${Array.from({length:count},(_,i)=>`
          <div class="hb-col-cell">
            ${cols[i] ? `<div style="font-size:.8rem;color:rgba(255,255,255,.5)">${cols[i].substring(0,60)}…</div>` : `<div style="font-size:.75rem;color:rgba(255,255,255,.2)">Spalte ${i+1}</div>`}
          </div>`).join('')}
      </div>`;
    },
    edit(b) {
      const count = b.attrs.count || 2;
      const cols  = b.attrs.cols || [];
      return `
        <div class="hb-inline-toolbar">
          ${[2,3].map(c=>`<button class="hb-it-btn${count===c?' active':''}" onclick="HuberBlocks._setAttr('${b.id}','count',${c});HuberBlocks._setAttr('${b.id}','cols',${JSON.stringify(Array.from({length:c},(_,i)=>cols[i]||''))});HuberBlocks._redrawBlock('${b.id}')">${c} Spalten</button>`).join('')}
        </div>
        <div style="display:grid;grid-template-columns:repeat(${count},1fr);gap:.6rem;margin-top:.5rem">
          ${Array.from({length:count},(_,i)=>`
            <div>
              <label class="hb-label">Spalte ${i+1}</label>
              <div class="hb-richtext" contenteditable="true" data-attr-col="${i}" data-block="${b.id}"
                   data-placeholder="Inhalt Spalte ${i+1}…"
                   style="min-height:80px;border:1px solid rgba(255,255,255,.1);border-radius:6px;padding:.4rem">${cols[i]||''}</div>
            </div>`).join('')}
        </div>`;
    }
  });

  // 10. CODE
  BlockRegistry.register('code', {
    title: 'Code', icon: 'bi-code-slash', category: 'text',
    defaults: { content: '', language: 'php' },
    preview(b) {
      return `<div class="hb-preview-code"><span style="color:rgba(255,255,255,.3);font-size:.72rem;margin-right:.4rem">${esc(b.attrs.language||'code')}</span>${esc((b.attrs.content||'').substring(0,80))}${(b.attrs.content||'').length>80?'…':''}</div>`;
    },
    edit(b) {
      const langs = ['html','css','js','php','python','sql','bash','json','xml','markdown','text'];
      return `
        <div class="hb-field">
          <select class="hb-select" data-attr="language">
            ${langs.map(l=>`<option value="${l}" ${(b.attrs.language||'php')===l?'selected':''}>${l.toUpperCase()}</option>`).join('')}
          </select>
        </div>
        <textarea class="hb-input" data-attr="content" rows="8"
                  style="font-family:monospace;font-size:.82rem;line-height:1.5;resize:vertical"
                  placeholder="Code hier eingeben…">${esc(b.attrs.content||'')}</textarea>`;
    }
  });

  // 11. CALLOUT
  BlockRegistry.register('callout', {
    title: 'Hinweis-Box', icon: 'bi-info-circle', category: 'layout',
    defaults: { content: '', type: 'info', title: '' },
    preview(b) {
      const icons = { info:'bi-info-circle-fill', success:'bi-check-circle-fill', warning:'bi-exclamation-triangle-fill', danger:'bi-x-octagon-fill' };
      return `<div class="hb-preview-callout ${esc(b.attrs.type||'info')}">
        <i class="bi ${icons[b.attrs.type||'info']} me-2"></i>
        ${b.attrs.title?`<strong>${esc(b.attrs.title)}</strong> — `:''}${b.attrs.content||'<em>Leer</em>'}
      </div>`;
    },
    edit(b) {
      return `
        <div class="hb-inline-toolbar">
          ${['info','success','warning','danger'].map(t=>`<button class="hb-it-btn${(b.attrs.type||'info')===t?' active':''}" onclick="HuberBlocks._setAttr('${b.id}','type','${t}');HuberBlocks._redrawBlock('${b.id}')">${t}</button>`).join('')}
        </div>
        <div class="hb-field"><label class="hb-label">Titel (optional)</label>
          <input class="hb-input" type="text" data-attr="title" value="${esc(b.attrs.title)}" placeholder="Hinweis-Titel…">
        </div>
        <div class="hb-field"><label class="hb-label">Inhalt</label>
          <div class="hb-richtext" contenteditable="true" data-attr="content"
               data-placeholder="Nachricht…">${b.attrs.content||''}</div>
        </div>`;
    }
  });

  // 12. VIDEO
  BlockRegistry.register('video', {
    title: 'Video', icon: 'bi-play-circle', category: 'media',
    defaults: { url: '', caption: '' },
    preview(b) {
      if (!b.attrs.url) return `<div class="hb-preview-meta"><i class="bi bi-play-circle"></i> Kein Video</div>`;
      return `<div class="hb-video-preview"><i class="bi bi-play-circle-fill"></i><div><div style="font-size:.85rem;color:rgba(255,255,255,.7)">${esc(b.attrs.url)}</div>${b.attrs.caption?`<div style="font-size:.75rem;color:rgba(255,255,255,.35)">${esc(b.attrs.caption)}</div>`:''}</div></div>`;
    },
    edit(b) {
      const embedId = _getVideoId(b.attrs.url || '');
      return `
        <div class="hb-field"><label class="hb-label">YouTube / Vimeo URL</label>
          <input class="hb-input" type="url" data-attr="url" value="${esc(b.attrs.url)}" placeholder="https://www.youtube.com/watch?v=…">
        </div>
        <div class="hb-field"><label class="hb-label">Beschriftung</label>
          <input class="hb-input" type="text" data-attr="caption" value="${esc(b.attrs.caption)}" placeholder="Video-Beschriftung…">
        </div>
        ${embedId ? `<div style="margin-top:.5rem;aspect-ratio:16/9;border-radius:8px;overflow:hidden">
          <iframe width="100%" height="100%" src="https://www.youtube.com/embed/${embedId}" frameborder="0" allowfullscreen></iframe>
        </div>` : ''}`;
    }
  });

  function _getVideoId(url) {
    const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    return m ? m[1] : null;
  }

  // 13. HTML
  BlockRegistry.register('html', {
    title: 'HTML', icon: 'bi-code', category: 'text',
    defaults: { content: '' },
    preview(b) {
      return `<div class="hb-preview-code" style="color:#86efac">${esc((b.attrs.content||'').substring(0,100))}${(b.attrs.content||'').length>100?'…':''}</div>`;
    },
    edit(b) {
      return `
        <div class="hb-field"><label class="hb-label">HTML-Code</label>
          <textarea class="hb-input" data-attr="content" rows="8"
                    style="font-family:monospace;font-size:.82rem;line-height:1.5;resize:vertical"
                    placeholder="<div>Dein HTML…</div>">${esc(b.attrs.content||'')}</textarea>
        </div>
        ${b.attrs.content ? `<div class="hb-field"><label class="hb-label">Vorschau</label>
          <div style="background:rgba(255,255,255,.05);border-radius:8px;padding:.8rem;font-size:.9rem">${b.attrs.content}</div>
        </div>` : ''}`;
    }
  });

  // 14. TABLE
  BlockRegistry.register('table', {
    title: 'Tabelle', icon: 'bi-table', category: 'text',
    defaults: { head: ['Spalte 1','Spalte 2'], rows: [['',''],['','']], hasHead: true },
    preview(b) {
      const head = b.attrs.head || [];
      const rows = b.attrs.rows || [];
      return `<table class="hb-table">
        ${b.attrs.hasHead && head.length ? `<thead><tr>${head.map(h=>`<th>${esc(h)}</th>`).join('')}</tr></thead>` : ''}
        <tbody>${rows.slice(0,2).map(r=>`<tr>${(r||[]).map(c=>`<td>${esc(c)}</td>`).join('')}</tr>`).join('')}${rows.length>2?`<tr><td colspan="${head.length}" style="text-align:center;color:rgba(255,255,255,.3);font-size:.75rem">+${rows.length-2} weitere Zeilen</td></tr>`:''}</tbody>
      </table>`;
    },
    edit(b) {
      const head = b.attrs.head || ['Spalte 1','Spalte 2'];
      const rows = b.attrs.rows || [['','']];
      const cols = head.length;
      return `
        <div class="hb-inline-toolbar">
          <button class="hb-it-btn" onclick="HuberBlocks._tableAddCol('${b.id}')"><i class="bi bi-plus me-1"></i>Spalte</button>
          <button class="hb-it-btn" onclick="HuberBlocks._tableRemoveCol('${b.id}')"><i class="bi bi-dash me-1"></i>Spalte</button>
          <div class="hb-it-sep"></div>
          <button class="hb-it-btn" onclick="HuberBlocks._tableAddRow('${b.id}')"><i class="bi bi-plus me-1"></i>Zeile</button>
          <button class="hb-it-btn" onclick="HuberBlocks._tableRemoveRow('${b.id}')"><i class="bi bi-dash me-1"></i>Zeile</button>
        </div>
        <table class="hb-table" style="margin-top:.5rem">
          ${b.attrs.hasHead ? `<thead><tr>${head.map((h,ci)=>`<th><input class="hb-table-head" data-block="${b.id}" data-ci="${ci}" value="${esc(h)}" style="background:transparent;border:none;color:#e2e8f0;font-weight:600;width:100%;outline:none"></th>`).join('')}</tr></thead>` : ''}
          <tbody>
            ${rows.map((row,ri)=>`<tr>${Array.from({length:cols},(_,ci)=>`<td><input class="hb-table-cell" data-block="${b.id}" data-ri="${ri}" data-ci="${ci}" value="${esc((row||[])[ci]||'')}" style="background:transparent;border:none;color:rgba(255,255,255,.8);width:100%;outline:none"></td>`).join('')}</tr>`).join('')}
          </tbody>
        </table>`;
    }
  });

  // 15. SPACER
  BlockRegistry.register('spacer', {
    title: 'Abstand', icon: 'bi-arrows-expand-vertical', category: 'layout',
    defaults: { height: 40 },
    preview(b) {
      const h = b.attrs.height || 40;
      return `<div class="hb-preview-spacer" style="height:${clamp(h,10,300)}px"><i class="bi bi-arrows-expand-vertical me-1"></i>${h}px</div>`;
    },
    edit(b) {
      return `
        <div class="hb-field"><label class="hb-label">Höhe: <strong>${b.attrs.height||40}px</strong></label>
          <input type="range" data-attr="height" min="10" max="300" step="5" value="${b.attrs.height||40}"
                 style="width:100%;accent-color:#6366f1"
                 oninput="this.previousElementSibling.querySelector('strong').textContent=this.value+'px'">
        </div>
        <div style="background:repeating-linear-gradient(45deg,rgba(255,255,255,.04),rgba(255,255,255,.04) 4px,transparent 4px,transparent 16px);border-radius:8px;height:${clamp(b.attrs.height||40,10,200)}px;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.25);font-size:.8rem">
          ${b.attrs.height||40}px Abstand
        </div>`;
    }
  });

  /* ================================================================
     BLOCK EDITOR — MAIN CLASS
     ================================================================ */
  function BlockEditor(opts) {
    _target = document.getElementById(opts.targetId);
    _canvas = document.getElementById(opts.canvasId);

    // Load existing content
    try {
      const raw = _target.value.trim();
      if (raw && raw[0] === '[') {
        const parsed = JSON.parse(raw);
        _state.blocks = parsed.map(b => ({ id: uid(), type: b.type, attrs: { ...(BlockRegistry.get(b.type)?.defaults||{}), ...(b.attrs||{}) } }));
      } else if (raw) {
        // Migrate from Markdown: wrap in HTML block
        _state.blocks = [{ id: uid(), type: 'html', attrs: { content: '<pre>' + esc(raw) + '</pre>' } }];
      }
    } catch(e) {}

    _render();
    _renderInserter(opts.inserterId);
  }

  /* ── Internal render ── */
  function _render() {
    if (!_canvas) return;

    if (!_state.blocks.length) {
      _canvas.innerHTML = `
        <div id="hb-canvas-empty">
          <i class="bi bi-layout-text-sidebar-reverse"></i>
          <div>Klicke auf einen Block-Typ, um zu beginnen</div>
        </div>
        <div class="hb-add-strip" onclick="HuberBlocks._showInserterAt(null)">
          <button class="hb-add-strip-btn">+</button>
        </div>`;
      return;
    }

    let html = '<div class="hb-add-strip" onclick="HuberBlocks._showInserterAt(null,true)">' +
               '<button class="hb-add-strip-btn">+</button></div>';

    _state.blocks.forEach((block, idx) => {
      const def = BlockRegistry.get(block.type);
      const isSelected = block.id === _state.selected;
      html += `
        <div class="hb-block${isSelected?' hb-selected':''}"
             id="block-${block.id}"
             data-id="${block.id}"
             draggable="true"
             onclick="HuberBlocks._selectBlock('${block.id}',event)">
          <div class="hb-block-meta">
            <span class="hb-drag-handle" title="Ziehen zum Sortieren"><i class="bi bi-grip-vertical"></i></span>
            <span class="hb-block-type-badge"><i class="bi ${def?.icon||'bi-square'} me-1"></i>${def?.title||block.type}</span>
            <div class="hb-block-actions">
              <button class="hb-act-btn" onclick="HuberBlocks._moveBlock('${block.id}',-1,event)" title="Nach oben" ${idx===0?'disabled':''}><i class="bi bi-arrow-up"></i></button>
              <button class="hb-act-btn" onclick="HuberBlocks._moveBlock('${block.id}',1,event)" title="Nach unten" ${idx===_state.blocks.length-1?'disabled':''}><i class="bi bi-arrow-down"></i></button>
              <button class="hb-act-btn" onclick="HuberBlocks._duplicateBlock('${block.id}',event)" title="Duplizieren"><i class="bi bi-copy"></i></button>
              <button class="hb-act-btn hb-act-danger" onclick="HuberBlocks._deleteBlock('${block.id}',event)" title="Löschen"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <div class="hb-block-body">
            ${isSelected ? (def?.edit(block)||'') : (def?.preview(block)||'')}
          </div>
        </div>
        <div class="hb-add-strip" onclick="HuberBlocks._showInserterAt('${block.id}')">
          <button class="hb-add-strip-btn">+</button>
        </div>`;
    });

    _canvas.innerHTML = html;
    _bindBlockEvents();
    commit();
  }

  function _renderInserter(inserterId) {
    const panel = document.getElementById(inserterId);
    if (!panel) return;

    const categories = [
      { key: 'text',   label: 'Text' },
      { key: 'media',  label: 'Medien' },
      { key: 'layout', label: 'Layout' }
    ];

    let html = `<input id="hb-search" placeholder="Block suchen…" oninput="HuberBlocks._filterBlocks(this.value)">`;
    html += `<div id="hb-block-list" class="hb-block-list">`;

    categories.forEach(cat => {
      const blocks = BlockRegistry.byCategory(cat.key);
      if (!blocks.length) return;
      html += `<div class="hb-cat-header">${cat.label}</div>`;
      blocks.forEach(b => {
        html += `<div class="hb-block-item" onclick="HuberBlocks._insertBlock('${b.name}')" data-search="${b.title.toLowerCase()} ${b.name}">
          <i class="bi ${b.icon}"></i> ${b.title}
        </div>`;
      });
    });

    html += '</div>';
    panel.innerHTML = html;
  }

  /* ── Event binding for active blocks ── */
  function _bindBlockEvents() {
    if (!_canvas) return;

    // Richtext contenteditable — save on input
    _canvas.querySelectorAll('[contenteditable][data-attr]').forEach(el => {
      el.addEventListener('input', () => {
        const block = getBlock(_state.selected);
        if (!block) return;
        block.attrs[el.dataset.attr] = el.innerHTML;
        commit();
      });
    });

    // Columns richtext
    _canvas.querySelectorAll('[contenteditable][data-attr-col]').forEach(el => {
      el.addEventListener('input', () => {
        const block = getBlock(el.dataset.block);
        if (!block) return;
        const cols = [...(block.attrs.cols||[])];
        cols[parseInt(el.dataset.attrCol)] = el.innerHTML;
        block.attrs.cols = cols;
        commit();
      });
    });

    // Select inputs
    _canvas.querySelectorAll('select[data-attr]').forEach(el => {
      el.addEventListener('change', () => {
        const block = getBlock(_state.selected);
        if (!block) return;
        block.attrs[el.dataset.attr] = el.value;
        commit();
      });
    });

    // Regular inputs
    _canvas.querySelectorAll('input[data-attr],textarea[data-attr]').forEach(el => {
      el.addEventListener('input', () => {
        const block = getBlock(_state.selected);
        if (!block) return;
        const val = el.type === 'range' || el.type === 'number' ? Number(el.value) : el.value;
        block.attrs[el.dataset.attr] = val;
        commit();
      });
    });

    // Gallery textarea (newline-separated → array)
    _canvas.querySelectorAll('textarea[data-attr-json]').forEach(el => {
      el.addEventListener('input', () => {
        const block = getBlock(_state.selected);
        if (!block) return;
        block.attrs[el.dataset.attrJson] = el.value.split('\n').map(s=>s.trim()).filter(Boolean);
        commit();
      });
    });

    // List items
    _canvas.querySelectorAll('.hb-list-item').forEach(el => {
      el.addEventListener('input', () => {
        const block = getBlock(el.dataset.block);
        if (!block) return;
        const items = [...(block.attrs.items||[])];
        items[parseInt(el.dataset.idx)] = el.value;
        block.attrs.items = items;
        commit();
      });
    });

    // Button fields
    _canvas.querySelectorAll('.hb-btn-field').forEach(el => {
      el.addEventListener('input', () => _saveBtnFields());
      el.addEventListener('change', () => _saveBtnFields());
    });

    // Table cells
    _canvas.querySelectorAll('.hb-table-cell').forEach(el => {
      el.addEventListener('input', () => {
        const block = getBlock(el.dataset.block);
        if (!block) return;
        const rows = block.attrs.rows.map(r=>[...r]);
        rows[parseInt(el.dataset.ri)][parseInt(el.dataset.ci)] = el.value;
        block.attrs.rows = rows;
        commit();
      });
    });

    // Table head
    _canvas.querySelectorAll('.hb-table-head').forEach(el => {
      el.addEventListener('input', () => {
        const block = getBlock(el.dataset.block);
        if (!block) return;
        const head = [...(block.attrs.head||[])];
        head[parseInt(el.dataset.ci)] = el.value;
        block.attrs.head = head;
        commit();
      });
    });

    // Drag & drop
    _canvas.querySelectorAll('.hb-block[draggable]').forEach(el => {
      el.addEventListener('dragstart', _onDragStart);
      el.addEventListener('dragover',  _onDragOver);
      el.addEventListener('drop',      _onDrop);
      el.addEventListener('dragend',   _onDragEnd);
    });
  }

  function _saveBtnFields() {
    const block = getBlock(_state.selected);
    if (!block) return;
    const container = document.getElementById('hb-btns-' + block.id);
    if (!container) return;
    const btns = [];
    const count = container.querySelectorAll('[data-idx]').length ? Math.max(...[...container.querySelectorAll('[data-idx]')].map(e=>parseInt(e.dataset.idx)))+1 : 0;
    for (let i = 0; i < count; i++) {
      const btn = {};
      container.querySelectorAll(`[data-idx="${i}"]`).forEach(el => { btn[el.dataset.key] = el.value; });
      if (btn.text !== undefined) btns.push(btn);
    }
    block.attrs.buttons = btns;
    commit();
  }

  /* ── Drag & Drop ── */
  function _onDragStart(e) { _dragged = this.dataset.id; this.classList.add('hb-dragging'); e.dataTransfer.effectAllowed = 'move'; }
  function _onDragOver(e)  { e.preventDefault(); this.classList.add('hb-drag-over'); }
  function _onDrop(e)      { e.preventDefault(); const tid = this.dataset.id; if (_dragged && tid && _dragged !== tid) { _reorder(_dragged, tid); } }
  function _onDragEnd()    { this.classList.remove('hb-dragging'); _canvas.querySelectorAll('.hb-drag-over').forEach(el => el.classList.remove('hb-drag-over')); _dragged = null; }

  function _reorder(fromId, toId) {
    const fi = getIndex(fromId), ti = getIndex(toId);
    if (fi === -1 || ti === -1) return;
    const b = _state.blocks.splice(fi, 1)[0];
    _state.blocks.splice(fi < ti ? ti - 1 : ti, 0, b);
    _render();
  }

  /* ================================================================
     PUBLIC API (attached to HuberBlocks)
     ================================================================ */
  const HuberBlocks = {
    init: (opts) => new BlockEditor(opts),

    _selectBlock(id, e) {
      if (e) e.stopPropagation();
      if (_state.selected === id) return;
      _state.selected = id;
      _render();
    },

    _insertBlock(type, afterId) {
      const def = BlockRegistry.get(type);
      if (!def) return;
      const newBlock = { id: uid(), type, attrs: { ...(def.defaults||{}) } };
      const insertId = afterId !== undefined ? afterId : (_state.insertAfter !== undefined ? _state.insertAfter : null);

      if (insertId === null || insertId === 'start') {
        _state.blocks.unshift(newBlock);
      } else {
        const idx = getIndex(insertId);
        _state.blocks.splice(idx + 1, 0, newBlock);
      }
      _state.selected  = newBlock.id;
      _state.insertAfter = undefined;
      _render();
      setTimeout(() => { const el = document.getElementById('block-' + newBlock.id); if (el) el.scrollIntoView({ behavior:'smooth', block:'nearest' }); }, 50);
    },

    _showInserterAt(afterId, atStart) {
      _state.insertAfter = atStart ? null : afterId;
      const list = document.getElementById('hb-block-list');
      if (list) list.scrollIntoView({ behavior:'smooth', block:'nearest' });
    },

    _deleteBlock(id, e) {
      if (e) { e.stopPropagation(); }
      if (!confirm('Block löschen?')) return;
      _state.blocks = _state.blocks.filter(b => b.id !== id);
      if (_state.selected === id) _state.selected = null;
      _render();
    },

    _moveBlock(id, dir, e) {
      if (e) e.stopPropagation();
      const idx = getIndex(id);
      const nidx = idx + dir;
      if (nidx < 0 || nidx >= _state.blocks.length) return;
      [_state.blocks[idx], _state.blocks[nidx]] = [_state.blocks[nidx], _state.blocks[idx]];
      _render();
    },

    _duplicateBlock(id, e) {
      if (e) e.stopPropagation();
      const block = getBlock(id);
      if (!block) return;
      const copy = { id: uid(), type: block.type, attrs: JSON.parse(JSON.stringify(block.attrs)) };
      const idx = getIndex(id);
      _state.blocks.splice(idx + 1, 0, copy);
      _state.selected = copy.id;
      _render();
    },

    _setAttr(id, key, val) {
      const block = getBlock(id);
      if (block) { block.attrs[key] = val; commit(); }
    },

    _redrawBlock(id) {
      _state.selected = id;
      _render();
    },

    _filterBlocks(q) {
      const list = document.getElementById('hb-block-list');
      if (!list) return;
      list.querySelectorAll('.hb-block-item').forEach(item => {
        item.style.display = !q || item.dataset.search.includes(q.toLowerCase()) ? '' : 'none';
      });
      list.querySelectorAll('.hb-cat-header').forEach(h => {
        const next = h.nextElementSibling;
        h.style.display = next && next.style.display !== 'none' ? '' : 'none';
      });
    },

    // List helpers
    _addListItem(id) {
      const block = getBlock(id);
      if (!block) return;
      block.attrs.items = [...(block.attrs.items||['']), ''];
      _state.selected = id;
      _render();
    },
    _removeListItem(id, idx) {
      const block = getBlock(id);
      if (!block) return;
      block.attrs.items = (block.attrs.items||[]).filter((_,i)=>i!==idx);
      _state.selected = id;
      _render();
    },

    // Button helpers
    _addBtn(id) {
      const block = getBlock(id);
      if (!block) return;
      block.attrs.buttons = [...(block.attrs.buttons||[]), { text:'', url:'#', style:'primary', target:'_self' }];
      _state.selected = id;
      _render();
    },
    _removeBtn(id, idx) {
      const block = getBlock(id);
      if (!block) return;
      block.attrs.buttons = (block.attrs.buttons||[]).filter((_,i)=>i!==idx);
      _state.selected = id;
      _render();
    },

    // Table helpers
    _tableAddCol(id) {
      const b = getBlock(id);
      if (!b) return;
      b.attrs.head = [...(b.attrs.head||[]), 'Spalte'];
      b.attrs.rows = (b.attrs.rows||[]).map(r=>[...r,'']);
      _state.selected = id; _render();
    },
    _tableRemoveCol(id) {
      const b = getBlock(id);
      if (!b || (b.attrs.head||[]).length <= 1) return;
      b.attrs.head = (b.attrs.head||[]).slice(0,-1);
      b.attrs.rows = (b.attrs.rows||[]).map(r=>r.slice(0,-1));
      _state.selected = id; _render();
    },
    _tableAddRow(id) {
      const b = getBlock(id);
      if (!b) return;
      b.attrs.rows = [...(b.attrs.rows||[]), Array((b.attrs.head||[]).length).fill('')];
      _state.selected = id; _render();
    },
    _tableRemoveRow(id) {
      const b = getBlock(id);
      if (!b || (b.attrs.rows||[]).length <= 1) return;
      b.attrs.rows = (b.attrs.rows||[]).slice(0,-1);
      _state.selected = id; _render();
    },

    // Media picker (opens /admin/medien in modal)
    _mediaPicker(blockId, attr) {
      const w = window.open('/admin/medien?picker=1', 'mediaPicker', 'width=900,height=600,resizable=yes');
      window._mediaPickerCallback = (url) => {
        const block = getBlock(blockId);
        if (block) { block.attrs[attr] = url; _state.selected = blockId; _render(); }
        w.close();
      };
    },

    // Deselect on canvas background click
    _deselectAll() { _state.selected = null; _render(); }
  };

  global.HuberBlocks = HuberBlocks;

})(window);

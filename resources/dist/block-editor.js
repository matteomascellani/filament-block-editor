/**
 * filament-block-editor — Alpine.js block editor component
 *
 * Registered as window.blockEditor so Filament/Alpine can call it via
 * x-data="blockEditor({ state: $wire.entangle(...).live })"
 *
 * No ES module build required — plain script loaded via FilamentAsset.
 */
(function () {
    'use strict';

    // ── Block defaults by type ────────────────────────────────────────────────
    var DEFAULTS = {
        paragraph: { text: 'Scrivi qui il testo...' },
        heading:   { text: 'Titolo', level: 2 },
        image:     { src: '', alt: '', width: '100%', align: 'center' },
        columns:   { left: 'Colonna sinistra', right: 'Colonna destra' },
        button:    { text: 'Clicca qui', url: '#', variant: 'primary' },
        divider:   { spacing: 'md' },
        spacer:    { height: 32 },
        html:      { code: '<p>HTML personalizzato</p>' },
    };

    var LABELS = {
        paragraph: '¶  Paragrafo',
        heading:   'H  Titolo',
        image:     '🖼  Immagine',
        columns:   '▌▐  2 Colonne',
        button:    '⬜  Pulsante',
        divider:   '—  Separatore',
        spacer:    '↕  Spazio',
        html:      '</>  HTML',
    };

    // ── HTML escape ───────────────────────────────────────────────────────────
    function esc(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Rand ID ───────────────────────────────────────────────────────────────
    function uid() {
        return Math.random().toString(36).slice(2, 9);
    }

    // ── Render a single block to HTML ─────────────────────────────────────────
    function renderBlock(block) {
        var d = block.data || {};
        switch (block.type) {
            case 'paragraph':
                return '<p style="margin:0 0 1em;line-height:1.6;color:#374151;">' + esc(d.text) + '</p>';

            case 'heading': {
                var lvl = Math.max(1, Math.min(6, parseInt(d.level) || 2));
                var tag = 'h' + lvl;
                var sizes = { 1: '2rem', 2: '1.5rem', 3: '1.25rem', 4: '1.1rem', 5: '1rem', 6: '0.9rem' };
                var sz = sizes[lvl] || '1.5rem';
                return '<' + tag + ' style="margin:0 0 0.5em;font-size:' + sz + ';font-weight:700;color:#111827;">'
                    + esc(d.text) + '</' + tag + '>';
            }

            case 'image': {
                if (!d.src) {
                    return '<div style="padding:1em;border:2px dashed #d1d5db;text-align:center;color:#9ca3af;font-size:12px;">Immagine non impostata</div>';
                }
                var al = d.align || 'center';
                var mx = al === 'center' ? 'margin:0 auto;' : al === 'right' ? 'margin-left:auto;' : '';
                return '<div style="text-align:' + al + ';">'
                    + '<img src="' + esc(d.src) + '" alt="' + esc(d.alt || '') + '" '
                    + 'style="max-width:100%;width:' + esc(d.width || '100%') + ';display:block;border-radius:4px;' + mx + '">'
                    + '</div>';
            }

            case 'columns':
                return '<div style="display:flex;gap:1rem;">'
                    + '<div style="flex:1;min-width:0;">' + (d.left || '') + '</div>'
                    + '<div style="flex:1;min-width:0;">' + (d.right || '') + '</div>'
                    + '</div>';

            case 'button': {
                var vs = {
                    primary:   'background:#4f46e5;color:#fff;',
                    secondary: 'background:#6b7280;color:#fff;',
                    outline:   'background:transparent;color:#4f46e5;border:2px solid #4f46e5;',
                    danger:    'background:#dc2626;color:#fff;',
                };
                var btnStyle = vs[d.variant || 'primary'] || vs.primary;
                return '<div style="text-align:center;margin:1em 0;">'
                    + '<a href="' + esc(d.url || '#') + '" style="display:inline-block;padding:0.6em 1.5em;border-radius:6px;font-weight:600;text-decoration:none;' + btnStyle + '">'
                    + esc(d.text || 'Button') + '</a></div>';
            }

            case 'divider': {
                var sp = { sm: '0.5rem', md: '1rem', lg: '2rem' }[d.spacing || 'md'] || '1rem';
                return '<hr style="border:none;border-top:1px solid #e5e7eb;margin:' + sp + ' 0;">';
            }

            case 'spacer':
                return '<div style="height:' + (parseInt(d.height) || 32) + 'px;"></div>';

            case 'html':
                return d.code || '';

            default:
                return '';
        }
    }

    // ── Alpine component factory ──────────────────────────────────────────────
    window.blockEditor = function (params) {
        var state       = params.state;
        var placeholder = params.placeholder || '';

        return {
            open:        false,
            blocks:      [],
            state:       state,
            placeholder: placeholder,

            init: function () {
                this._parseState();
                var self = this;
                this.$watch('state', function (val) {
                    // Only re-parse if the state differs from what we last saved
                    if (val !== JSON.stringify(self.blocks)) {
                        self._parseState();
                    }
                });
            },

            _parseState: function () {
                if (!this.state) { this.blocks = []; return; }
                try { this.blocks = JSON.parse(this.state); } catch (e) { this.blocks = []; }
            },

            openEditor: function () {
                this._parseState(); // snapshot current persisted state for editing
                this.open = true;
            },

            saveAndClose: function () {
                this.state = JSON.stringify(this.blocks);
                this.open  = false;
            },

            discardAndClose: function () {
                this._parseState();
                this.open = false;
            },

            addBlock: function (type) {
                var defaults = DEFAULTS[type] || {};
                var data = {};
                for (var k in defaults) if (Object.prototype.hasOwnProperty.call(defaults, k)) data[k] = defaults[k];
                this.blocks = this.blocks.concat([{ id: uid(), type: type, data: data }]);
            },

            removeBlock: function (id) {
                this.blocks = this.blocks.filter(function (b) { return b.id !== id; });
            },

            moveUp: function (id) {
                var idx = this.blocks.findIndex(function (b) { return b.id === id; });
                if (idx > 0) {
                    var arr = this.blocks.slice();
                    var tmp = arr[idx - 1]; arr[idx - 1] = arr[idx]; arr[idx] = tmp;
                    this.blocks = arr;
                }
            },

            moveDown: function (id) {
                var idx = this.blocks.findIndex(function (b) { return b.id === id; });
                if (idx >= 0 && idx < this.blocks.length - 1) {
                    var arr = this.blocks.slice();
                    var tmp = arr[idx]; arr[idx] = arr[idx + 1]; arr[idx + 1] = tmp;
                    this.blocks = arr;
                }
            },

            duplicate: function (id) {
                var idx = this.blocks.findIndex(function (b) { return b.id === id; });
                if (idx < 0) return;
                var clone = JSON.parse(JSON.stringify(this.blocks[idx]));
                clone.id  = uid();
                var arr   = this.blocks.slice();
                arr.splice(idx + 1, 0, clone);
                this.blocks = arr;
            },

            updateData: function (id, key, value) {
                this.blocks = this.blocks.map(function (b) {
                    if (b.id !== id) return b;
                    var data = Object.assign({}, b.data);
                    data[key] = value;
                    return Object.assign({}, b, { data: data });
                });
            },

            blockLabel: function (type) {
                return LABELS[type] || type;
            },

            blockHtml: function (block) {
                return renderBlock(block);
            },

            previewHtml: function () {
                return this.blocks.map(renderBlock).join('\n');
            },
        };
    };
}());

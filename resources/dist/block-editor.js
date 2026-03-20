/**
 * filament-block-editor — Alpine.js block editor component v2
 *
 * Data model stored as JSON:
 *   { root: {mt,mr,mb,ml,pt,pr,pb,pl}, containers: [container] }
 *   container: { id, cols (1-4), mt,mr,mb,ml,pt,pr,pb,pl, columns: [column] }
 *   column:    { id, flex: 1-3, blocks: [block] }
 *   block:     { id, type, data }
 *     paragraph: data = { html: '' }
 *     image:     data = { src, alt, width, align }
 *     video:     data = { embed: '' }
 *
 * Registered as window.blockEditor — loaded globally via FilamentAsset.
 */
(function () {
    'use strict';

    // ── Defaults ──────────────────────────────────────────────────────────────
    var SPACING_ZERO = { mt:0, mr:0, mb:0, ml:0, pt:0, pr:0, pb:0, pl:0 };

    var CONTENT_DEFAULTS = {
        paragraph: { html: '' },
        image:     { src: '', alt: '', width: '100%', align: 'center' },
        video:     { embed: '' },
    };

    // ── Helpers ───────────────────────────────────────────────────────────────
    function esc(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function uid() { return Math.random().toString(36).slice(2, 9); }

    function px(v) { return (parseInt(v) || 0) + 'px'; }

    function spacingStyle(d) {
        return 'margin:'  + px(d.mt) + ' ' + px(d.mr) + ' ' + px(d.mb) + ' ' + px(d.ml) + ';'
             + 'padding:' + px(d.pt) + ' ' + px(d.pr) + ' ' + px(d.pb) + ' ' + px(d.pl) + ';';
    }

    function newSpacing() { return Object.assign({}, SPACING_ZERO); }

    function newColumn() { return { id: uid(), flex: 1, blocks: [] }; }

    function newContainer() {
        return Object.assign({ id: uid(), cols: 1, columns: [newColumn()] }, newSpacing());
    }

    // ── Render helpers ────────────────────────────────────────────────────────
    function renderContentBlock(block) {
        var d = block.data || {};
        switch (block.type) {
            case 'paragraph':
                return d.html ? '<div style="line-height:1.6;color:#374151;margin-bottom:0.75em;">' + d.html + '</div>' : '';
            case 'image': {
                if (!d.src) return '';
                var al = d.align || 'center';
                var mx = al === 'center' ? 'margin:0 auto;' : al === 'right' ? 'margin-left:auto;' : '';
                return '<div style="text-align:' + al + ';">'
                    + '<img src="' + esc(d.src) + '" alt="' + esc(d.alt || '') + '" '
                    + 'style="max-width:100%;width:' + esc(d.width || '100%') + ';display:block;border-radius:4px;' + mx + '">'
                    + '</div>';
            }
            case 'video':
                if (!d.embed) return '';
                return '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:6px;margin-bottom:0.75em;">'
                    + d.embed.replace(/<iframe/gi, '<iframe style="position:absolute;top:0;left:0;width:100%;height:100%;"')
                    + '</div>';
            default:
                return '';
        }
    }

    function renderContainer(c) {
        var colClass = 'fbc-' + c.id;
        var colsHtml = (c.columns || []).map(function (col) {
            var blocksHtml = (col.blocks || []).map(renderContentBlock).filter(Boolean).join('\n');
            var flexVal = parseInt(col.flex) || 1;
            return '<div class="' + colClass + '" style="flex:' + flexVal + ';min-width:0;">' + blocksHtml + '</div>';
        }).join('');

        var wrapStyle = spacingStyle(c) + 'display:flex;flex-wrap:wrap;gap:1rem;';
        var responsive = parseInt(c.cols) > 1
            ? '<style>@media(max-width:640px){.' + colClass + '{flex:0 0 100%!important;min-width:100%!important;}}</style>'
            : '';

        return '<div style="' + wrapStyle + '">' + colsHtml + '</div>' + responsive;
    }

    function renderAll(root, containers) {
        var inner = (containers || []).map(renderContainer).join('\n');
        return '<div style="' + spacingStyle(root || SPACING_ZERO) + '">' + inner + '</div>';
    }

    // ── Alpine component factory ──────────────────────────────────────────────
    window.blockEditor = function (params) {
        return {
            open:       false,
            state:      params.state,
            root:       Object.assign({}, SPACING_ZERO),
            containers: [],

            // Gallery drag & drop
            dragItem:   null,
            dragTarget: null,

            // Which column is "active" for palette insertions
            focusedContainerId: null,
            focusedColIdx:      0,

            // ── Init ──────────────────────────────────────────────────────
            init: function () {
                this._parse();
                var self = this;
                this.$watch('state', function (val) {
                    if (val !== self._serialize()) self._parse();
                });
            },

            _parse: function () {
                if (!this.state) { this.root = Object.assign({}, SPACING_ZERO); this.containers = []; return; }
                try {
                    var d = JSON.parse(this.state);
                    if (d && typeof d === 'object' && d.containers) {
                        this.root       = Object.assign({}, SPACING_ZERO, d.root || {});
                        this.containers = d.containers;
                    } else {
                        this.root = Object.assign({}, SPACING_ZERO);
                        this.containers = [];
                    }
                } catch (e) {
                    this.root = Object.assign({}, SPACING_ZERO);
                    this.containers = [];
                }
            },

            _serialize: function () {
                return JSON.stringify({ root: this.root, containers: this.containers });
            },

            openEditor: function () { this._parse(); this.open = true; },

            saveAndClose: function () {
                // Sync any open contenteditable before saving
                document.querySelectorAll('[data-fbe-rich]').forEach(function (el) {
                    el.dispatchEvent(new Event('blur'));
                });
                this.state = this._serialize();
                this.open  = false;
            },

            discardAndClose: function () { this._parse(); this.open = false; },

            // ── Root spacing ──────────────────────────────────────────────
            updateRoot: function (key, val) {
                this.root[key] = parseInt(val) || 0;
            },

            // ── Containers ────────────────────────────────────────────────
            addContainer: function () {
                var c = newContainer();
                this.containers = this.containers.concat([c]);
                this.focusedContainerId = c.id;
                this.focusedColIdx      = 0;
            },

            removeContainer: function (id) {
                this.containers = this.containers.filter(function (c) { return c.id !== id; });
                if (this.focusedContainerId === id) {
                    this.focusedContainerId = null;
                    this.focusedColIdx      = 0;
                }
            },

            moveContainerUp: function (id) {
                var idx = this.containers.findIndex(function (c) { return c.id === id; });
                if (idx <= 0) return;
                var arr = this.containers.slice();
                var tmp = arr[idx - 1]; arr[idx - 1] = arr[idx]; arr[idx] = tmp;
                this.containers = arr;
            },

            moveContainerDown: function (id) {
                var idx = this.containers.findIndex(function (c) { return c.id === id; });
                if (idx < 0 || idx >= this.containers.length - 1) return;
                var arr = this.containers.slice();
                var tmp = arr[idx]; arr[idx] = arr[idx + 1]; arr[idx + 1] = tmp;
                this.containers = arr;
            },

            setContainerCols: function (id, cols) {
                cols = Math.max(1, Math.min(4, parseInt(cols) || 1));
                var idx = this.containers.findIndex(function (c) { return c.id === id; });
                if (idx < 0) return;
                var c = JSON.parse(JSON.stringify(this.containers[idx]));
                var current = c.columns.length;
                if (cols > current) {
                    for (var i = current; i < cols; i++) c.columns.push(newColumn());
                } else if (cols < current) {
                    c.columns = c.columns.slice(0, cols);
                }
                c.cols = cols;
                var arr = this.containers.slice();
                arr[idx] = c;
                this.containers = arr;
                // Adjust focus if needed
                if (this.focusedContainerId === id && this.focusedColIdx >= cols) {
                    this.focusedColIdx = cols - 1;
                }
            },

            setColumnFlex: function (containerId, colIdx, flex) {
                flex = Math.max(1, Math.min(3, parseInt(flex) || 1));
                var idx = this.containers.findIndex(function (c) { return c.id === containerId; });
                if (idx < 0) return;
                var c = JSON.parse(JSON.stringify(this.containers[idx]));
                if (!c.columns[colIdx]) return;
                c.columns[colIdx].flex = flex;
                var arr = this.containers.slice();
                arr[idx] = c;
                this.containers = arr;
            },

            updateContainerSpacing: function (id, key, val) {
                var idx = this.containers.findIndex(function (c) { return c.id === id; });
                if (idx < 0) return;
                var c = Object.assign({}, this.containers[idx]);
                c[key] = parseInt(val) || 0;
                var arr = this.containers.slice();
                arr[idx] = c;
                this.containers = arr;
            },

            containerLayoutStyle: function (container) {
                return 'display:flex;flex-wrap:wrap;gap:12px;'
                    + 'margin:'  + px(container.mt) + ' ' + px(container.mr) + ' ' + px(container.mb) + ' ' + px(container.ml) + ';'
                    + 'padding:' + px(container.pt) + ' ' + px(container.pr) + ' ' + px(container.pb) + ' ' + px(container.pl) + ';';
            },

            // ── Content blocks ────────────────────────────────────────────
            addBlock: function (containerId, colIdx, type) {
                var idx = this.containers.findIndex(function (c) { return c.id === containerId; });
                if (idx < 0) return;
                var defaults = CONTENT_DEFAULTS[type] || {};
                var block = { id: uid(), type: type, data: JSON.parse(JSON.stringify(defaults)) };
                var c = JSON.parse(JSON.stringify(this.containers[idx]));
                if (!c.columns[colIdx]) return;
                c.columns[colIdx].blocks = c.columns[colIdx].blocks.concat([block]);
                var arr = this.containers.slice();
                arr[idx] = c;
                this.containers = arr;
            },

            addBlockToFocused: function (type) {
                if (!this.focusedContainerId) {
                    if (this.containers.length === 0) this.addContainer();
                    else { this.focusedContainerId = this.containers[0].id; this.focusedColIdx = 0; }
                }
                this.addBlock(this.focusedContainerId, this.focusedColIdx, type);
            },

            removeBlock: function (containerId, colIdx, blockId) {
                var idx = this.containers.findIndex(function (c) { return c.id === containerId; });
                if (idx < 0) return;
                var c = JSON.parse(JSON.stringify(this.containers[idx]));
                c.columns[colIdx].blocks = c.columns[colIdx].blocks.filter(function (b) { return b.id !== blockId; });
                var arr = this.containers.slice();
                arr[idx] = c;
                this.containers = arr;
            },

            moveBlockUp: function (containerId, colIdx, blockId) {
                var idx = this.containers.findIndex(function (c) { return c.id === containerId; });
                if (idx < 0) return;
                var c = JSON.parse(JSON.stringify(this.containers[idx]));
                var blocks = c.columns[colIdx].blocks;
                var bi = blocks.findIndex(function (b) { return b.id === blockId; });
                if (bi <= 0) return;
                var tmp = blocks[bi - 1]; blocks[bi - 1] = blocks[bi]; blocks[bi] = tmp;
                var arr = this.containers.slice();
                arr[idx] = c;
                this.containers = arr;
            },

            moveBlockDown: function (containerId, colIdx, blockId) {
                var idx = this.containers.findIndex(function (c) { return c.id === containerId; });
                if (idx < 0) return;
                var c = JSON.parse(JSON.stringify(this.containers[idx]));
                var blocks = c.columns[colIdx].blocks;
                var bi = blocks.findIndex(function (b) { return b.id === blockId; });
                if (bi < 0 || bi >= blocks.length - 1) return;
                var tmp = blocks[bi]; blocks[bi] = blocks[bi + 1]; blocks[bi + 1] = tmp;
                var arr = this.containers.slice();
                arr[idx] = c;
                this.containers = arr;
            },

            updateData: function (containerId, colIdx, blockId, key, value) {
                var idx = this.containers.findIndex(function (c) { return c.id === containerId; });
                if (idx < 0) return;
                var c = JSON.parse(JSON.stringify(this.containers[idx]));
                var block = c.columns[colIdx].blocks.find(function (b) { return b.id === blockId; });
                if (block) block.data[key] = value;
                var arr = this.containers.slice();
                arr[idx] = c;
                this.containers = arr;
            },

            // Rich text: mutate in place to avoid contenteditable re-init
            updateRichText: function (containerId, colIdx, blockId, html) {
                var c = this.containers.find(function (c) { return c.id === containerId; });
                if (!c || !c.columns[colIdx]) return;
                var block = c.columns[colIdx].blocks.find(function (b) { return b.id === blockId; });
                if (block) block.data.html = html;
            },

            execRichCmd: function (blockId, cmd, value) {
                // @mousedown.prevent on toolbar keeps focus in contenteditable
                document.execCommand(cmd, false, value || null);
                // Sync innerHTML back
                var el = document.querySelector('[data-fbe-rich="' + blockId + '"]');
                if (el) {
                    this.containers.forEach(function (c) {
                        c.columns.forEach(function (col) {
                            col.blocks.forEach(function (b) {
                                if (b.id === blockId) b.data.html = el.innerHTML;
                            });
                        });
                    });
                }
            },

            execRichLink: function (blockId) {
                var url = prompt('URL del link (es. https://...):');
                if (url) this.execRichCmd(blockId, 'createLink', url);
            },

            // ── Column focus ──────────────────────────────────────────────
            setFocus: function (containerId, colIdx) {
                this.focusedContainerId = containerId;
                this.focusedColIdx      = colIdx;
            },

            isFocused: function (containerId, colIdx) {
                return this.focusedContainerId === containerId && this.focusedColIdx === colIdx;
            },

            // ── Gallery drag & drop ───────────────────────────────────────
            startDrag: function (src, alt, event) {
                this.dragItem = { src: src, alt: alt };
                event.dataTransfer.effectAllowed = 'copy';
                event.dataTransfer.setData('text/plain', src);
            },

            endDrag: function () {
                this.dragItem   = null;
                this.dragTarget = null;
            },

            setDragTarget: function (id) {
                if (this.dragItem) this.dragTarget = id;
            },

            clearDragTarget: function () {
                this.dragTarget = null;
            },

            dropOnImage: function (containerId, colIdx, blockId) {
                if (!this.dragItem) return;
                this.updateData(containerId, colIdx, blockId, 'src', this.dragItem.src);
                this.updateData(containerId, colIdx, blockId, 'alt', this.dragItem.alt);
                this.dragItem   = null;
                this.dragTarget = null;
            },

            // ── Preview ───────────────────────────────────────────────────
            previewHtml: function () {
                return renderAll(this.root, this.containers);
            },

            hasContent: function () {
                return this.containers.some(function (c) {
                    return c.columns.some(function (col) { return col.blocks.length > 0; });
                });
            },
        };
    };

}());

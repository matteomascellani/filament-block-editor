<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @once
    <style>
        /* ── Block editor shell ─────────────────────────────────────────── */
        .fbe-preview img   { max-width: 100%; height: auto; }
        .fbe-preview table { width: 100%; border-collapse: collapse; }
        .fbe-preview td    { vertical-align: top; }

        .fbe-block {
            transition: box-shadow 0.1s, border-color 0.1s;
        }
        .fbe-block:hover {
            box-shadow: 0 0 0 2px rgba(99,102,241,0.3);
        }
        .fbe-block.fbe-selected {
            box-shadow: 0 0 0 2px rgb(99,102,241);
        }

        /* Palette button */
        .fbe-palette-btn {
            display: block;
            width: 100%;
            text-align: left;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: rgb(55 65 81);
            background: transparent;
            border: none;
            cursor: pointer;
            transition: background 0.1s, color 0.1s;
            white-space: nowrap;
        }
        .dark .fbe-palette-btn { color: rgb(209 213 219); }
        .fbe-palette-btn:hover {
            background: rgb(238 242 255);
            color: rgb(79 70 229);
        }
        .dark .fbe-palette-btn:hover {
            background: rgba(99,102,241,0.15);
            color: rgb(165 180 252);
        }

        /* Toolbar btn */
        .fbe-ctrl {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border: none;
            background: transparent;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            color: rgb(107 114 128);
            transition: background 0.1s, color 0.1s;
        }
        .fbe-ctrl:disabled { opacity: 0.3; cursor: default; }
        .fbe-ctrl:not(:disabled):hover { background: rgb(229 231 235); color: rgb(55 65 81); }
        .dark .fbe-ctrl { color: rgb(156 163 175); }
        .dark .fbe-ctrl:not(:disabled):hover { background: rgb(55 65 81); color: rgb(209 213 219); }
        .fbe-ctrl-danger:not(:disabled):hover { background: rgb(254 226 226) !important; color: rgb(220 38 38) !important; }

        /* Field input in block */
        .fbe-input {
            width: 100%;
            font-size: 13px;
            border: 1px solid rgb(209 213 219);
            border-radius: 5px;
            padding: 4px 8px;
            background: #fff;
            color: rgb(17 24 39);
            outline: none;
            transition: border-color 0.1s;
        }
        .fbe-input:focus { border-color: rgb(99 102 241); }
        .dark .fbe-input {
            background: rgb(31 41 55);
            border-color: rgb(55 65 81);
            color: rgb(243 244 246);
        }
        .dark .fbe-input:focus { border-color: rgb(99 102 241); }
        .fbe-textarea { resize: vertical; min-height: 72px; }
        .fbe-select { cursor: pointer; }
    </style>
    @endonce

    @php
        $statePath = $getStatePath();
    @endphp

    <div x-data="blockEditor({ state: $wire.entangle('{{ $statePath }}').live })" x-cloak>

        {{-- ── Preview card (click anywhere = open when empty) ──────── --}}
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">

            {{-- Header toolbar --}}
            <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <span class="text-xs text-gray-400" x-text="blocks.length + (blocks.length === 1 ? ' blocco' : ' blocchi')"></span>
                <button type="button"
                    @click="openEditor()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold bg-primary-600 hover:bg-primary-500 text-white transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5" aria-hidden="true">
                        <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z"/>
                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"/>
                    </svg>
                    ✏️ Apri block editor
                </button>
            </div>

            {{-- Empty state: big click target --}}
            <div x-show="blocks.length === 0"
                 @click="openEditor()"
                 class="flex flex-col items-center justify-center gap-3 py-8 cursor-pointer
                        bg-gray-50 dark:bg-gray-800/40
                        hover:bg-primary-50 dark:hover:bg-primary-900/20
                        transition-colors group">
                <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center
                            group-hover:bg-primary-200 dark:group-hover:bg-primary-800/60 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                         class="w-5 h-5 text-primary-600 dark:text-primary-400" aria-hidden="true">
                        <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z"/>
                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"/>
                    </svg>
                </div>
                <div class="text-center">
                    <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                        Nessun contenuto
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        Clicca per aprire il block editor
                    </p>
                </div>
            </div>

            {{-- Preview when blocks exist --}}
            <div x-show="blocks.length > 0"
                 class="fbe-preview p-4 text-sm overflow-hidden cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors"
                 @click="openEditor()"
                 x-html="previewHtml()"
                 title="Clicca per modificare">
            </div>
        </div>

        {{-- ── Full-screen editor overlay ──────────────────────────────── --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            style="display:none; z-index:99999;"
            class="fixed inset-0 flex flex-col bg-white dark:bg-gray-900"
        >
            {{-- ── Header ──────────────────────────────────────────────── --}}
            <div class="shrink-0 flex items-center justify-between px-4 py-2.5 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Block Editor</span>
                    <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-[10px] font-mono"
                          x-text="blocks.length + ' blocchi'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        @click="discardAndClose()"
                        class="px-3 py-1.5 rounded-md text-xs font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        Annulla
                    </button>
                    <button type="button"
                        @click="saveAndClose()"
                        class="px-4 py-1.5 rounded-md bg-primary-600 hover:bg-primary-500 text-white text-xs font-semibold transition-colors">
                        ✓ Salva
                    </button>
                </div>
            </div>

            {{-- ── Body ─────────────────────────────────────────────────── --}}
            <div class="flex flex-1 overflow-hidden">

                {{-- ── Block palette (left) ──────────────────────────── --}}
                <div class="w-44 shrink-0 border-r border-gray-200 dark:border-gray-700 overflow-y-auto p-2 bg-gray-50 dark:bg-gray-800/60">
                    <p class="px-2 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Aggiungi blocco</p>
                    @foreach (['paragraph','heading','image','columns','button','divider','spacer','html'] as $blockType)
                        <button type="button"
                            class="fbe-palette-btn"
                            @click="addBlock('{{ $blockType }}')"
                            x-text="blockLabel('{{ $blockType }}')">
                        </button>
                    @endforeach

                    {{-- Tip --}}
                    <div class="mt-4 px-2">
                        <p class="text-[10px] text-gray-400 leading-relaxed">
                            Clicca un tipo per aggiungere un blocco in fondo al canvas.
                        </p>
                    </div>
                </div>

                {{-- ── Canvas (center) ───────────────────────────────── --}}
                <div class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-950 p-6">

                    {{-- Empty state --}}
                    <div x-show="blocks.length === 0"
                         class="flex flex-col items-center justify-center h-full text-center text-gray-400 select-none">
                        <div class="text-5xl mb-4">📄</div>
                        <p class="font-semibold text-sm text-gray-500 dark:text-gray-400">Nessun blocco ancora</p>
                        <p class="text-xs mt-1 text-gray-400">Clicca un tipo a sinistra per iniziare</p>
                    </div>

                    {{-- Blocks --}}
                    <div class="max-w-2xl mx-auto space-y-3" x-show="blocks.length > 0">
                        <template x-for="(block, idx) in blocks" :key="block.id">
                            <div
                                class="fbe-block bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
                                :class="{ 'fbe-selected': false }"
                            >
                                {{-- Block header --}}
                                <div class="flex items-center justify-between px-3 py-1 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400"
                                          x-text="blockLabel(block.type)"></span>
                                    <div class="flex items-center gap-0.5">
                                        <button type="button" class="fbe-ctrl" title="Su"      :disabled="idx === 0"                   @click="moveUp(block.id)">↑</button>
                                        <button type="button" class="fbe-ctrl" title="Giù"     :disabled="idx === blocks.length - 1"   @click="moveDown(block.id)">↓</button>
                                        <button type="button" class="fbe-ctrl" title="Duplica"                                         @click="duplicate(block.id)">⎘</button>
                                        <button type="button" class="fbe-ctrl fbe-ctrl-danger" title="Elimina"                         @click="removeBlock(block.id)">×</button>
                                    </div>
                                </div>

                                {{-- Block edit form --}}
                                <div class="p-3">

                                    {{-- PARAGRAPH --}}
                                    <template x-if="block.type === 'paragraph'">
                                        <textarea
                                            class="fbe-input fbe-textarea"
                                            placeholder="Testo paragrafo..."
                                            :value="block.data.text"
                                            @input="updateData(block.id, 'text', $event.target.value)"
                                            rows="4"></textarea>
                                    </template>

                                    {{-- HEADING --}}
                                    <template x-if="block.type === 'heading'">
                                        <div class="space-y-2">
                                            <input type="text"
                                                class="fbe-input font-bold text-base"
                                                placeholder="Testo del titolo..."
                                                :value="block.data.text"
                                                @input="updateData(block.id, 'text', $event.target.value)">
                                            <select class="fbe-input fbe-select"
                                                :value="String(block.data.level)"
                                                @change="updateData(block.id, 'level', parseInt($event.target.value))">
                                                <option value="2">H2 — Titolo principale</option>
                                                <option value="3">H3 — Sottotitolo</option>
                                                <option value="4">H4 — Titolo piccolo</option>
                                            </select>
                                        </div>
                                    </template>

                                    {{-- IMAGE --}}
                                    <template x-if="block.type === 'image'">
                                        <div class="space-y-2">
                                            <div x-show="block.data.src"
                                                 class="rounded border border-gray-200 dark:border-gray-700 overflow-hidden h-28 flex items-center justify-center bg-gray-50 dark:bg-gray-700/30">
                                                <img :src="block.data.src" :alt="block.data.alt || ''"
                                                     class="max-h-28 max-w-full object-contain">
                                            </div>
                                            <input type="url" class="fbe-input"
                                                placeholder="URL immagine (https://...)"
                                                :value="block.data.src"
                                                @input="updateData(block.id, 'src', $event.target.value)">
                                            <input type="text" class="fbe-input"
                                                placeholder="Testo alternativo (alt)"
                                                :value="block.data.alt"
                                                @input="updateData(block.id, 'alt', $event.target.value)">
                                            <div class="flex gap-2">
                                                <select class="fbe-input fbe-select flex-1"
                                                    :value="block.data.align"
                                                    @change="updateData(block.id, 'align', $event.target.value)">
                                                    <option value="left">⬛⬜ Sinistra</option>
                                                    <option value="center">⬛ Centro</option>
                                                    <option value="right">⬜⬛ Destra</option>
                                                </select>
                                                <input type="text" class="fbe-input w-28"
                                                    placeholder="Larghezza (100%)"
                                                    :value="block.data.width"
                                                    @input="updateData(block.id, 'width', $event.target.value)">
                                            </div>
                                        </div>
                                    </template>

                                    {{-- 2 COLUMNS --}}
                                    <template x-if="block.type === 'columns'">
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Colonna sinistra</p>
                                                <textarea class="fbe-input fbe-textarea" rows="6"
                                                    placeholder="Contenuto sinistra..."
                                                    :value="block.data.left"
                                                    @input="updateData(block.id, 'left', $event.target.value)"></textarea>
                                            </div>
                                            <div>
                                                <p class="text-[10px] uppercase tracking-wide text-gray-400 mb-1">Colonna destra</p>
                                                <textarea class="fbe-input fbe-textarea" rows="6"
                                                    placeholder="Contenuto destra..."
                                                    :value="block.data.right"
                                                    @input="updateData(block.id, 'right', $event.target.value)"></textarea>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- BUTTON --}}
                                    <template x-if="block.type === 'button'">
                                        <div class="space-y-2">
                                            <input type="text" class="fbe-input"
                                                placeholder="Testo del pulsante"
                                                :value="block.data.text"
                                                @input="updateData(block.id, 'text', $event.target.value)">
                                            <input type="url" class="fbe-input"
                                                placeholder="URL destinazione (https://...)"
                                                :value="block.data.url"
                                                @input="updateData(block.id, 'url', $event.target.value)">
                                            <select class="fbe-input fbe-select"
                                                :value="block.data.variant"
                                                @change="updateData(block.id, 'variant', $event.target.value)">
                                                <option value="primary">Primary (viola)</option>
                                                <option value="secondary">Secondary (grigio)</option>
                                                <option value="outline">Outline</option>
                                                <option value="danger">Danger (rosso)</option>
                                            </select>
                                            {{-- Preview --}}
                                            <div class="text-center pt-1"
                                                 x-html="blockHtml(block)"></div>
                                        </div>
                                    </template>

                                    {{-- DIVIDER --}}
                                    <template x-if="block.type === 'divider'">
                                        <div class="flex items-center gap-3">
                                            <hr class="flex-1 border-gray-300 dark:border-gray-600">
                                            <select class="fbe-input fbe-select w-40"
                                                :value="block.data.spacing"
                                                @change="updateData(block.id, 'spacing', $event.target.value)">
                                                <option value="sm">Spazio piccolo</option>
                                                <option value="md">Spazio medio</option>
                                                <option value="lg">Spazio grande</option>
                                            </select>
                                            <hr class="flex-1 border-gray-300 dark:border-gray-600">
                                        </div>
                                    </template>

                                    {{-- SPACER --}}
                                    <template x-if="block.type === 'spacer'">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 rounded border-2 border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center"
                                                 :style="'height:' + Math.min(80, parseInt(block.data.height) || 32) + 'px'">
                                                <span class="text-[10px] text-gray-400" x-text="(parseInt(block.data.height) || 32) + 'px spazio'"></span>
                                            </div>
                                            <input type="number" class="fbe-input w-24"
                                                min="4" max="300" step="4"
                                                :value="block.data.height"
                                                @input="updateData(block.id, 'height', parseInt($event.target.value) || 32)">
                                        </div>
                                    </template>

                                    {{-- RAW HTML --}}
                                    <template x-if="block.type === 'html'">
                                        <div class="space-y-2">
                                            <textarea class="fbe-input fbe-textarea font-mono text-xs" rows="8"
                                                placeholder="HTML personalizzato..."
                                                :value="block.data.code"
                                                @input="updateData(block.id, 'code', $event.target.value)"></textarea>
                                            <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded p-2 fbe-preview text-xs"
                                                 x-html="block.data.code || ''"></div>
                                        </div>
                                    </template>

                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ── Right: mini HTML preview ─────────────────────── --}}
                <div class="w-60 shrink-0 border-l border-gray-200 dark:border-gray-700 overflow-y-auto bg-white dark:bg-gray-900 p-3"
                     x-show="blocks.length > 0">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 mb-2">Anteprima</p>
                    <div class="text-xs bg-gray-50 dark:bg-gray-800/50 rounded border border-gray-200 dark:border-gray-700 p-2 fbe-preview"
                         x-html="previewHtml()"></div>
                </div>

            </div>{{-- /body --}}
        </div>{{-- /overlay --}}

    </div>{{-- /x-data --}}
</x-dynamic-component>

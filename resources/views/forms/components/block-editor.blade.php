<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @once
    <style>
        /* ── Block editor shell ─────────────────────────────────────────── */
        .fbe-input {
            width: 100%;
            font-size: 12px;
            border: 1px solid rgb(209 213 219);
            border-radius: 4px;
            padding: 3px 6px;
            background: #fff;
            color: rgb(17 24 39);
            outline: none;
        }
        .fbe-input:focus { border-color: rgb(99 102 241); }
        .dark .fbe-input { background: rgb(31 41 55); border-color: rgb(55 65 81); color: rgb(243 244 246); }
        .fbe-input-num { width: 48px; text-align: center; }
        .fbe-select { cursor: pointer; }

        .fbe-ctrl {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border: none; background: transparent;
            border-radius: 3px; cursor: pointer; font-size: 11px;
            color: rgb(107 114 128); transition: background .1s, color .1s;
        }
        .fbe-ctrl:disabled { opacity: .3; cursor: default; }
        .fbe-ctrl:not(:disabled):hover { background: rgb(229 231 235); color: rgb(55 65 81); }
        .dark .fbe-ctrl { color: rgb(156 163 175); }
        .dark .fbe-ctrl:not(:disabled):hover { background: rgb(55 65 81); color: rgb(209 213 219); }
        .fbe-ctrl-del:not(:disabled):hover { background: rgb(254 226 226) !important; color: rgb(220 38 38) !important; }

        /* Palette buttons */
        .fbe-palette-btn {
            display: flex; align-items: center; gap: 6px;
            width: 100%; padding: 7px 10px; border-radius: 6px;
            font-size: 12px; font-weight: 500; color: rgb(55 65 81);
            background: transparent; border: none; cursor: pointer;
            transition: background .1s, color .1s; white-space: nowrap;
        }
        .dark .fbe-palette-btn { color: rgb(209 213 219); }
        .fbe-palette-btn:hover { background: rgb(238 242 255); color: rgb(79 70 229); }
        .dark .fbe-palette-btn:hover { background: rgba(99,102,241,.15); color: rgb(165 180 252); }
        .fbe-palette-btn.fbe-primary { background: rgb(238 242 255); color: rgb(79 70 229); font-weight: 600; }
        .dark .fbe-palette-btn.fbe-primary { background: rgba(99,102,241,.2); color: rgb(165 180 252); }

        /* Column focus ring */
        .fbe-col-area { border-radius: 6px; transition: box-shadow .1s; }
        .fbe-col-area.fbe-focused { box-shadow: 0 0 0 2px rgb(99 102 241); }

        /* Rich text editor */
        .fbe-richtext-toolbar {
            display: flex; flex-wrap: wrap; gap: 2px;
            padding: 4px; background: rgb(249 250 251);
            border: 1px solid rgb(229 231 235); border-bottom: none;
            border-radius: 5px 5px 0 0;
        }
        .dark .fbe-richtext-toolbar { background: rgb(31 41 55); border-color: rgb(55 65 81); }
        .fbe-richtext-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 2px 5px; min-width: 22px; height: 22px;
            border: none; background: transparent; border-radius: 3px;
            cursor: pointer; font-size: 11px; font-weight: 600;
            color: rgb(75 85 99); transition: background .1s;
        }
        .fbe-richtext-btn:hover { background: rgb(229 231 235); color: rgb(55 65 81); }
        .dark .fbe-richtext-btn { color: rgb(156 163 175); }
        .dark .fbe-richtext-btn:hover { background: rgb(55 65 81); }
        .fbe-richtext-sep { width: 1px; height: 16px; background: rgb(209 213 219); margin: 3px 2px; align-self: center; }
        .fbe-richtext {
            min-height: 100px; padding: 8px;
            border: 1px solid rgb(209 213 219); border-radius: 0 0 5px 5px;
            font-size: 13px; line-height: 1.6; color: rgb(17 24 39);
            background: #fff; outline: none;
        }
        .fbe-richtext:focus { border-color: rgb(99 102 241); }
        .dark .fbe-richtext { background: rgb(31 41 55); border-color: rgb(55 65 81); color: rgb(243 244 246); }
        .fbe-richtext p, .fbe-richtext h2, .fbe-richtext h3 { margin: 0 0 0.5em; }
        .fbe-richtext ul, .fbe-richtext ol { padding-left: 1.4em; margin: 0 0 0.5em; }
        .fbe-richtext a { color: rgb(79 70 229); text-decoration: underline; }

        /* Drop zone for image block */
        .fbe-dropzone { border-radius: 5px; transition: box-shadow .1s, background .1s; }
        .fbe-dropzone.fbe-dropover { box-shadow: 0 0 0 2px rgb(99 102 241); background: rgba(99,102,241,.06); }

        /* Preview */
        .fbe-preview img   { max-width: 100%; height: auto; }
        .fbe-preview table { width: 100%; border-collapse: collapse; }

        /* Draggable gallery items */
        .fbe-draggable { cursor: grab; user-select: none; }
        .fbe-draggable:active { cursor: grabbing; }
    </style>
    @endonce

    @php
        $statePath       = $getStatePath();
        $mediaCollection = $getMediaCollection();
        $record          = $getRecord();
        $mediaItems      = ($mediaCollection && $record && method_exists($record, 'getMedia'))
            ? $record->getMedia($mediaCollection)
            : collect();
    @endphp

    <div x-data="blockEditor({ state: $wire.entangle('{{ $statePath }}').live })" x-cloak>

        {{-- ── Preview card ──────────────────────────────────────────────── --}}
        <div class="border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <span class="text-xs text-gray-400"
                      x-text="containers.length + ' sezioni'"></span>
                <button type="button"
                    @click="openEditor()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold
                           bg-primary-600 hover:bg-primary-500 text-white transition-colors shadow-sm">
                    ✏️ Apri block editor
                </button>
            </div>

            {{-- Empty state --}}
            <div x-show="!hasContent()"
                 @click="openEditor()"
                 class="flex flex-col items-center justify-center gap-2 py-8 cursor-pointer
                        bg-gray-50 dark:bg-gray-800/40 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors group">
                <div class="w-10 h-10 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                         class="w-5 h-5 text-primary-500" aria-hidden="true">
                        <path d="M5.433 13.917l1.262-3.155A4 4 0 017.58 9.42l6.92-6.918a2.121 2.121 0 013 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 01-.65-.65z"/>
                        <path d="M3.5 5.75c0-.69.56-1.25 1.25-1.25H10A.75.75 0 0010 3H4.75A2.75 2.75 0 002 5.75v9.5A2.75 2.75 0 004.75 18h9.5A2.75 2.75 0 0017 15.25V10a.75.75 0 00-1.5 0v5.25c0 .69-.56 1.25-1.25 1.25h-9.5c-.69 0-1.25-.56-1.25-1.25v-9.5z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 group-hover:text-primary-600 transition-colors">
                    Nessun contenuto — clicca per aprire
                </p>
            </div>

            {{-- Preview --}}
            <div x-show="hasContent()"
                 class="fbe-preview p-4 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/20 transition-colors"
                 @click="openEditor()"
                 x-html="previewHtml()"
                 title="Clicca per modificare"></div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- ── Fullscreen editor overlay ──────────────────────────────────── --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display:none; z-index:99999;"
            class="fixed inset-0 flex flex-col bg-white dark:bg-gray-900"
        >
            {{-- ── Top bar ────────────────────────────────────────────────── --}}
            <div class="shrink-0 flex items-center justify-between px-4 py-2.5
                        border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-sm text-gray-800 dark:text-gray-100">Block Editor</span>
                    <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[10px] font-mono text-gray-500"
                          x-text="containers.length + ' sezioni'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                        @click="discardAndClose()"
                        class="px-3 py-1.5 rounded-md text-xs font-medium text-gray-500
                               hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        Annulla
                    </button>
                    <button type="button"
                        @click="saveAndClose()"
                        class="px-4 py-1.5 rounded-md bg-primary-600 hover:bg-primary-500 text-white text-xs font-semibold transition-colors">
                        ✓ Salva
                    </button>
                </div>
            </div>

            {{-- ── Body: 2 columns ────────────────────────────────────────── --}}
            <div class="flex flex-1 overflow-hidden">

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- LEFT PANEL — palette + gallery                            --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="shrink-0 border-r border-gray-200 dark:border-gray-700 overflow-y-auto overflow-x-hidden
                            bg-gray-50 dark:bg-gray-800/60 flex flex-col"
                     style="width:20%; max-width:20%; min-width:0; box-sizing:border-box;">

                    {{-- ── Sezioni ─────────────────────────────────────────── --}}
                    <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                        <p class="px-1 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">Sezioni</p>
                        <button type="button"
                            @click="addContainer()"
                            class="fbe-palette-btn fbe-primary">
                            <span class="text-base leading-none">＋</span> Aggiungi sezione
                        </button>
                        <p class="mt-2 px-1 text-[10px] text-gray-400 leading-relaxed">
                            Ogni sezione ha 1–4 colonne. I blocchi vanno dentro le colonne.
                        </p>
                    </div>

                    {{-- ── Blocchi ──────────────────────────────────────────── --}}
                    <div class="p-3 border-b border-gray-200 dark:border-gray-700">
                        <p class="px-1 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                            Blocchi
                        </p>
                        <button type="button" @click="addBlockToFocused('paragraph')" class="fbe-palette-btn">
                            <span>¶</span> Testo
                        </button>
                        <button type="button" @click="addBlockToFocused('image')" class="fbe-palette-btn">
                            <span>🖼</span> Immagine
                        </button>
                        <button type="button" @click="addBlockToFocused('video')" class="fbe-palette-btn">
                            <span>▶</span> Video YouTube
                        </button>
                        <p class="mt-2 px-1 text-[10px] text-gray-400 leading-relaxed">
                            Aggiunti alla colonna evidenziata in blu. Clicca una colonna per selezionarla.
                        </p>
                    </div>

                    {{-- ── Media / Gallery ──────────────────────────────────── --}}
                    @if($mediaItems->isNotEmpty())
                    <div class="p-3 flex-1">
                        <p class="px-1 mb-2 text-[10px] font-semibold uppercase tracking-wider text-gray-400">
                            📷 Media — trascina sui blocchi immagine
                        </p>
                        <div class="flex flex-col gap-2">
                            @foreach($mediaItems as $medium)
                                @php
                                    // Store relative path so the URL is rebuilt from APP_URL at render time,
                                    // making content portable across tunnel rotations and environments.
                                    $mUrl  = e(parse_url($medium->getUrl(), PHP_URL_PATH));
                                    $mName = $medium->name ?: $medium->file_name;
                                @endphp
                                <div class="fbe-draggable rounded border border-gray-200 dark:border-gray-600
                                            bg-white dark:bg-gray-700 overflow-hidden
                                            hover:border-primary-400 dark:hover:border-primary-500 transition-colors"
                                     style="width:100%; box-sizing:border-box;"
                                     draggable="true"
                                     @dragstart="startDrag('{{ $mUrl }}', '{{ e($mName) }}', $event)"
                                     @dragend="endDrag()"
                                     title="Trascina: {{ e($mName) }}">
                                    <img src="{{ $mUrl }}" alt="{{ e($mName) }}"
                                         class="h-20 object-cover block pointer-events-none"
                                         style="width:100%; max-width:100%; display:block;">
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 px-2 py-1 truncate"
                                       title="{{ e($mName) }}">{{ $mName }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>{{-- /left panel --}}

                {{-- ══════════════════════════════════════════════════════════ --}}
                {{-- RIGHT PANEL — canvas                                      --}}
                {{-- ══════════════════════════════════════════════════════════ --}}
                <div class="flex-1 flex flex-col overflow-hidden">

                    {{-- ── Root wrapper spacing (fixed, above canvas) ──────── --}}
                    <div class="shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-2">
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-gray-500 dark:text-gray-400">
                            <span class="font-semibold text-gray-600 dark:text-gray-300 shrink-0">Wrapper esterno</span>
                            <div class="flex items-center gap-1">
                                <span class="text-[10px] uppercase tracking-wide">Margine</span>
                                <span class="text-[10px] text-gray-400">T</span>
                                <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                    :value="root.mt" @input="updateRoot('mt', $event.target.value)">
                                <span class="text-[10px] text-gray-400">R</span>
                                <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                    :value="root.mr" @input="updateRoot('mr', $event.target.value)">
                                <span class="text-[10px] text-gray-400">B</span>
                                <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                    :value="root.mb" @input="updateRoot('mb', $event.target.value)">
                                <span class="text-[10px] text-gray-400">L</span>
                                <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                    :value="root.ml" @input="updateRoot('ml', $event.target.value)">
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-[10px] uppercase tracking-wide">Padding</span>
                                <span class="text-[10px] text-gray-400">T</span>
                                <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                    :value="root.pt" @input="updateRoot('pt', $event.target.value)">
                                <span class="text-[10px] text-gray-400">R</span>
                                <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                    :value="root.pr" @input="updateRoot('pr', $event.target.value)">
                                <span class="text-[10px] text-gray-400">B</span>
                                <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                    :value="root.pb" @input="updateRoot('pb', $event.target.value)">
                                <span class="text-[10px] text-gray-400">L</span>
                                <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                    :value="root.pl" @input="updateRoot('pl', $event.target.value)">
                            </div>
                        </div>
                    </div>

                    {{-- ── Scrollable canvas ────────────────────────────────── --}}
                    <div class="flex-1 overflow-y-auto bg-gray-100 dark:bg-gray-950 p-5">

                    {{-- ── Empty canvas ─────────────────────────────────────── --}}
                    <div x-show="containers.length === 0"
                         class="flex flex-col items-center justify-center py-16 text-center text-gray-400 select-none">
                        <div class="text-5xl mb-3">📐</div>
                        <p class="font-semibold text-sm text-gray-500 dark:text-gray-400">Nessuna sezione</p>
                        <p class="text-xs mt-1">Clicca "Aggiungi sezione" nel pannello sinistro</p>
                    </div>

                    {{-- ── Containers ───────────────────────────────────────── --}}
                    <template x-for="(container, cIdx) in containers" :key="container.id">
                        <div class="mb-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

                            {{-- Container header --}}
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 px-3 py-2
                                        bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600 text-xs">

                                {{-- Cols selector --}}
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-[10px] uppercase tracking-wide text-gray-400">Colonne</span>
                                    <select class="fbe-input fbe-select w-14"
                                        :value="container.cols"
                                        @change="setContainerCols(container.id, parseInt($event.target.value))">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                    </select>
                                </div>

                                {{-- Margine --}}
                                <div class="flex items-center gap-1 text-gray-500">
                                    <span class="text-[10px] uppercase tracking-wide text-gray-400">M</span>
                                    <span class="text-[10px] text-gray-400">T</span>
                                    <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                        :value="container.mt" @input="updateContainerSpacing(container.id, 'mt', $event.target.value)">
                                    <span class="text-[10px] text-gray-400">R</span>
                                    <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                        :value="container.mr" @input="updateContainerSpacing(container.id, 'mr', $event.target.value)">
                                    <span class="text-[10px] text-gray-400">B</span>
                                    <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                        :value="container.mb" @input="updateContainerSpacing(container.id, 'mb', $event.target.value)">
                                    <span class="text-[10px] text-gray-400">L</span>
                                    <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                        :value="container.ml" @input="updateContainerSpacing(container.id, 'ml', $event.target.value)">
                                </div>

                                {{-- Padding --}}
                                <div class="flex items-center gap-1 text-gray-500">
                                    <span class="text-[10px] uppercase tracking-wide text-gray-400">P</span>
                                    <span class="text-[10px] text-gray-400">T</span>
                                    <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                        :value="container.pt" @input="updateContainerSpacing(container.id, 'pt', $event.target.value)">
                                    <span class="text-[10px] text-gray-400">R</span>
                                    <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                        :value="container.pr" @input="updateContainerSpacing(container.id, 'pr', $event.target.value)">
                                    <span class="text-[10px] text-gray-400">B</span>
                                    <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                        :value="container.pb" @input="updateContainerSpacing(container.id, 'pb', $event.target.value)">
                                    <span class="text-[10px] text-gray-400">L</span>
                                    <input type="number" class="fbe-input fbe-input-num" min="0" max="200" step="4"
                                        :value="container.pl" @input="updateContainerSpacing(container.id, 'pl', $event.target.value)">
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center gap-0.5 ml-auto shrink-0">
                                    <button type="button" class="fbe-ctrl" title="Sposta su"
                                        :disabled="cIdx === 0" @click="moveContainerUp(container.id)">↑</button>
                                    <button type="button" class="fbe-ctrl" title="Sposta giù"
                                        :disabled="cIdx === containers.length - 1" @click="moveContainerDown(container.id)">↓</button>
                                    <button type="button" class="fbe-ctrl fbe-ctrl-del" title="Elimina sezione"
                                        @click="removeContainer(container.id)">×</button>
                                </div>
                            </div>

                            {{-- Columns --}}
                            <div class="p-3 flex gap-3 flex-wrap items-start"
                                 :style="'gap:12px;'">
                                <template x-for="(col, colIdx) in container.columns" :key="col.id">
                                    <div class="fbe-col-area min-w-0 p-2 rounded border border-dashed border-gray-200 dark:border-gray-700"
                                         :style="'flex:' + (col.flex || 1) + ';min-width:0;'"
                                         :class="{ 'fbe-focused': isFocused(container.id, colIdx) }"
                                         @click.self="setFocus(container.id, colIdx)">

                                        {{-- Column label + flex selector --}}
                                        <div class="flex items-center justify-between mb-2 cursor-pointer gap-2"
                                             @click.self="setFocus(container.id, colIdx)">
                                            <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 shrink-0"
                                                  x-text="container.cols > 1 ? 'Col ' + (colIdx + 1) : 'Contenuto'"></span>

                                            {{-- Width selector: only when 2+ columns --}}
                                            <div x-show="container.cols > 1"
                                                 class="flex items-center gap-0.5 shrink-0"
                                                 title="Larghezza relativa colonna">
                                                <template x-for="fw in [1,2,3]" :key="fw">
                                                    <button type="button"
                                                        @click.stop="setColumnFlex(container.id, colIdx, fw)"
                                                        :style="(col.flex || 1) === fw
                                                            ? 'background:#7c3aed;color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;border:none;cursor:pointer;line-height:1.4;'
                                                            : 'background:#f3f4f6;color:#6b7280;font-size:9px;font-weight:700;padding:2px 6px;border-radius:4px;border:none;cursor:pointer;line-height:1.4;'"
                                                        x-text="fw + 'x'">
                                                    </button>
                                                </template>
                                            </div>

                                            <span x-show="isFocused(container.id, colIdx)"
                                                  class="text-[9px] px-1.5 py-0.5 rounded bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 font-medium ml-auto shrink-0">
                                                attiva
                                            </span>
                                        </div>

                                        {{-- Blocks in this column --}}
                                        <template x-for="(block, bIdx) in col.blocks" :key="block.id">
                                            <div class="mb-2 rounded border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/40 overflow-hidden">

                                                {{-- Block toolbar --}}
                                                <div class="flex items-center justify-between px-2 py-1 bg-white dark:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
                                                    <span class="text-[9px] font-semibold uppercase tracking-wider text-gray-400"
                                                          x-text="block.type === 'paragraph' ? '¶ Testo' : block.type === 'image' ? '🖼 Immagine' : '▶ Video'"></span>
                                                    <div class="flex items-center gap-0.5">
                                                        <button type="button" class="fbe-ctrl" title="Su"
                                                            :disabled="bIdx === 0"
                                                            @click="moveBlockUp(container.id, colIdx, block.id)">↑</button>
                                                        <button type="button" class="fbe-ctrl" title="Giù"
                                                            :disabled="bIdx === col.blocks.length - 1"
                                                            @click="moveBlockDown(container.id, colIdx, block.id)">↓</button>
                                                        <button type="button" class="fbe-ctrl fbe-ctrl-del" title="Elimina blocco"
                                                            @click="removeBlock(container.id, colIdx, block.id)">×</button>
                                                    </div>
                                                </div>

                                                {{-- Block edit area --}}
                                                <div class="p-2">

                                                    {{-- PARAGRAPH — rich text editor --}}
                                                    <template x-if="block.type === 'paragraph'">
                                                        <div>
                                                            <div class="fbe-richtext-toolbar">
                                                                <button type="button" class="fbe-richtext-btn" title="Grassetto" @mousedown.prevent="execRichCmd(block.id, 'bold')"><b>B</b></button>
                                                                <button type="button" class="fbe-richtext-btn" title="Corsivo" @mousedown.prevent="execRichCmd(block.id, 'italic')"><i>I</i></button>
                                                                <button type="button" class="fbe-richtext-btn" title="Sottolineato" @mousedown.prevent="execRichCmd(block.id, 'underline')"><u>U</u></button>
                                                                <div class="fbe-richtext-sep"></div>
                                                                <button type="button" class="fbe-richtext-btn" title="Titolo H2" @mousedown.prevent="execRichCmd(block.id, 'formatBlock', 'h2')">H2</button>
                                                                <button type="button" class="fbe-richtext-btn" title="Titolo H3" @mousedown.prevent="execRichCmd(block.id, 'formatBlock', 'h3')">H3</button>
                                                                <button type="button" class="fbe-richtext-btn" title="Paragrafo" @mousedown.prevent="execRichCmd(block.id, 'formatBlock', 'p')">¶</button>
                                                                <div class="fbe-richtext-sep"></div>
                                                                <button type="button" class="fbe-richtext-btn" title="Lista puntata" @mousedown.prevent="execRichCmd(block.id, 'insertUnorderedList')">•≡</button>
                                                                <button type="button" class="fbe-richtext-btn" title="Lista numerata" @mousedown.prevent="execRichCmd(block.id, 'insertOrderedList')">1≡</button>
                                                                <div class="fbe-richtext-sep"></div>
                                                                <button type="button" class="fbe-richtext-btn" title="Aggiungi link" @mousedown.prevent="execRichLink(block.id)">🔗</button>
                                                                <button type="button" class="fbe-richtext-btn" title="Rimuovi link" @mousedown.prevent="execRichCmd(block.id, 'unlink')">✂</button>
                                                            </div>
                                                            <div class="fbe-richtext"
                                                                 contenteditable="true"
                                                                 :data-fbe-rich="block.id"
                                                                 x-init="if (!$el.innerHTML) $el.innerHTML = block.data.html || ''"
                                                                 @blur="updateRichText(container.id, colIdx, block.id, $el.innerHTML)"
                                                                 @focus="setFocus(container.id, colIdx)"></div>
                                                        </div>
                                                    </template>

                                                    {{-- IMAGE --}}
                                                    <template x-if="block.type === 'image'">
                                                        <div class="space-y-2">
                                                            {{-- Drop zone --}}
                                                            <div class="fbe-dropzone rounded border border-gray-200 dark:border-gray-600 h-28
                                                                        flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-700/30 relative"
                                                                 :class="{ 'fbe-dropover': dragTarget === 'img-' + block.id }"
                                                                 @dragover.prevent="setDragTarget('img-' + block.id)"
                                                                 @dragleave="clearDragTarget()"
                                                                 @drop.prevent="dropOnImage(container.id, colIdx, block.id)">
                                                                <img x-show="block.data.src"
                                                                     :src="block.data.src" :alt="block.data.alt || ''"
                                                                     class="max-h-28 max-w-full object-contain pointer-events-none">
                                                                <span x-show="!block.data.src"
                                                                      class="text-xs text-gray-400 select-none">
                                                                    🖼 Trascina immagine dalla galleria
                                                                </span>
                                                                <div x-show="dragTarget === 'img-' + block.id"
                                                                     class="absolute inset-0 flex items-center justify-center
                                                                            bg-primary-50/80 dark:bg-primary-900/60 pointer-events-none">
                                                                    <span class="text-xs font-bold text-primary-600 dark:text-primary-300">
                                                                        ↓ Rilascia
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <input type="url" class="fbe-input"
                                                                placeholder="URL immagine (https://...)"
                                                                :value="block.data.src"
                                                                @input="updateData(container.id, colIdx, block.id, 'src', $event.target.value)">
                                                            <input type="text" class="fbe-input"
                                                                placeholder="Testo alt"
                                                                :value="block.data.alt"
                                                                @input="updateData(container.id, colIdx, block.id, 'alt', $event.target.value)">
                                                            <div class="flex gap-2">
                                                                <select class="fbe-input fbe-select flex-1"
                                                                    :value="block.data.align"
                                                                    @change="updateData(container.id, colIdx, block.id, 'align', $event.target.value)">
                                                                    <option value="left">← Sinistra</option>
                                                                    <option value="center">↔ Centro</option>
                                                                    <option value="right">→ Destra</option>
                                                                </select>
                                                                <input type="text" class="fbe-input" style="width:80px;"
                                                                    placeholder="100%"
                                                                    :value="block.data.width"
                                                                    @input="updateData(container.id, colIdx, block.id, 'width', $event.target.value)">
                                                            </div>
                                                        </div>
                                                    </template>

                                                    {{-- VIDEO (YouTube embed) --}}
                                                    <template x-if="block.type === 'video'">
                                                        <div class="space-y-2">
                                                            <textarea class="fbe-input font-mono text-[11px]" rows="4"
                                                                style="resize:vertical;"
                                                                placeholder="Incolla qui il codice embed di YouTube (<iframe ...>)"
                                                                :value="block.data.embed"
                                                                @input="updateData(container.id, colIdx, block.id, 'embed', $event.target.value)"></textarea>
                                                            <div x-show="block.data.embed"
                                                                 class="rounded overflow-hidden border border-gray-200 dark:border-gray-600 bg-black"
                                                                 style="position:relative;padding-bottom:40%;height:0;"
                                                                 x-html="block.data.embed ? block.data.embed.replace(/<iframe/gi, '<iframe style=\'position:absolute;top:0;left:0;width:100%;height:100%;\'') : ''">
                                                            </div>
                                                        </div>
                                                    </template>

                                                </div>{{-- /block edit area --}}
                                            </div>{{-- /block card --}}
                                        </template>{{-- /blocks loop --}}

                                        {{-- Add block buttons --}}
                                        <div class="flex gap-1 mt-1">
                                            <button type="button"
                                                @click="setFocus(container.id, colIdx); addBlock(container.id, colIdx, 'paragraph')"
                                                class="flex-1 py-1 rounded border border-dashed border-gray-300 dark:border-gray-600
                                                       text-[10px] text-gray-400 hover:border-primary-400 hover:text-primary-500
                                                       transition-colors bg-transparent"
                                                title="Aggiungi testo">+ ¶</button>
                                            <button type="button"
                                                @click="setFocus(container.id, colIdx); addBlock(container.id, colIdx, 'image')"
                                                class="flex-1 py-1 rounded border border-dashed border-gray-300 dark:border-gray-600
                                                       text-[10px] text-gray-400 hover:border-primary-400 hover:text-primary-500
                                                       transition-colors bg-transparent"
                                                title="Aggiungi immagine">+ 🖼</button>
                                            <button type="button"
                                                @click="setFocus(container.id, colIdx); addBlock(container.id, colIdx, 'video')"
                                                class="flex-1 py-1 rounded border border-dashed border-gray-300 dark:border-gray-600
                                                       text-[10px] text-gray-400 hover:border-primary-400 hover:text-primary-500
                                                       transition-colors bg-transparent"
                                                title="Aggiungi video">+ ▶</button>
                                        </div>

                                    </div>{{-- /column --}}
                                </template>{{-- /columns loop --}}
                            </div>{{-- /columns wrapper --}}

                        </div>{{-- /container card --}}
                    </template>{{-- /containers loop --}}

                    {{-- Bottom add section button --}}
                    <div x-show="containers.length > 0" class="mt-1">
                        <button type="button"
                            @click="addContainer()"
                            class="w-full py-2.5 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700
                                   text-xs font-medium text-gray-400 hover:border-primary-400 hover:text-primary-500
                                   transition-colors bg-transparent">
                            ＋ Aggiungi sezione
                        </button>
                    </div>{{-- /bottom add section --}}

                    </div>{{-- /scrollable canvas --}}
                </div>{{-- /right panel --}}
            </div>{{-- /body --}}
        </div>{{-- /overlay --}}

    </div>{{-- /x-data --}}
</x-dynamic-component>

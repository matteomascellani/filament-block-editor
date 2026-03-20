<?php

namespace Matteomascellani\FilamentBlockEditor\Forms\Components;

use Filament\Forms\Components\Field;

class BlockEditor extends Field
{
    protected string $view = 'filament-block-editor::forms.components.block-editor';

    protected ?string $mediaCollection = null;

    public function mediaCollection(string $collection): static
    {
        $this->mediaCollection = $collection;
        return $this;
    }

    public function getMediaCollection(): ?string
    {
        return $this->mediaCollection;
    }

    /**
     * Convert stored JSON block structure to an HTML string for frontend display.
     *
     * Supports the v2 format: { root: {spacing}, containers: [...] }
     */
    public static function renderHtml(?string $json): string
    {
        if (! $json) {
            return '';
        }

        $data = json_decode($json, true);

        if (! is_array($data)) {
            return '';
        }

        // v2 format
        if (isset($data['containers'])) {
            return static::renderV2($data);
        }

        return '';
    }

    // ── v2 renderer ───────────────────────────────────────────────────────────

    private static function spacingStyle(array $d): string
    {
        $px = fn ($v) => ((int) ($v ?? 0)) . 'px';
        return 'margin:' . $px($d['mt'] ?? 0) . ' ' . $px($d['mr'] ?? 0) . ' '
                         . $px($d['mb'] ?? 0) . ' ' . $px($d['ml'] ?? 0) . ';'
             . 'padding:' . $px($d['pt'] ?? 0) . ' ' . $px($d['pr'] ?? 0) . ' '
                          . $px($d['pb'] ?? 0) . ' ' . $px($d['pl'] ?? 0) . ';';
    }

    private static function renderV2(array $data): string
    {
        $root       = $data['root'] ?? [];
        $containers = $data['containers'] ?? [];

        $inner = implode("\n", array_map([static::class, 'renderContainer'], $containers));

        return '<div style="' . static::spacingStyle($root) . '">' . $inner . '</div>';
    }

    private static function renderContainer(array $c): string
    {
        $colClass  = 'fbc-' . ($c['id'] ?? uniqid());
        $wrapStyle = static::spacingStyle($c) . 'display:flex;flex-wrap:wrap;gap:1rem;';

        $colsHtml = implode('', array_map(function ($col) use ($colClass) {
            $flexVal    = max(1, min(3, (int) ($col['flex'] ?? 1)));
            $colStyle   = 'flex:' . $flexVal . ';min-width:0;';
            $blocksHtml = implode("\n", array_filter(
                array_map([static::class, 'renderContentBlock'], $col['blocks'] ?? [])
            ));
            return '<div class="' . $colClass . '" style="' . $colStyle . '">' . $blocksHtml . '</div>';
        }, $c['columns'] ?? []));

        $cols       = max(1, min(4, (int) ($c['cols'] ?? 1)));
        $responsive = $cols > 1
            ? '<style>@media(max-width:640px){.' . $colClass . '{flex:0 0 100%!important;min-width:100%!important;}}</style>'
            : '';

        return '<div style="' . $wrapStyle . '">' . $colsHtml . '</div>' . $responsive;
    }

    private static function renderContentBlock(array $block): string
    {
        $data = $block['data'] ?? [];

        return match ($block['type'] ?? '') {
            'paragraph' => ! empty($data['html'])
                ? '<div style="line-height:1.6;color:#374151;margin-bottom:0.75em;">' . $data['html'] . '</div>'
                : '',

            'image' => (function () use ($data): string {
                if (empty($data['src'])) {
                    return '';
                }
                $align    = in_array($data['align'] ?? '', ['left', 'center', 'right']) ? $data['align'] : 'left';
                $width    = $data['width'] ?? '100%';
                $isFullW  = ($width === '100%');

                $wrapperStyle = implode(';', array_filter([
                    'overflow:hidden',
                    'border-radius:8px',
                    'border:1px solid #e5e7eb',
                    'margin-bottom:0.75em',
                    // aspect-ratio ensures equal height when columns are side by side
                    $isFullW ? 'aspect-ratio:16/9' : null,
                    'width:' . $width,
                    match ($align) {
                        'center' => 'margin-left:auto;margin-right:auto',
                        'right'  => 'margin-left:auto',
                        default  => '',
                    },
                ]));

                $imgStyle = $isFullW
                    ? 'width:100%;height:100%;object-fit:cover;display:block;'
                    : 'width:100%;max-width:100%;height:auto;display:block;';

                return '<div style="' . $wrapperStyle . '">'
                    . '<img src="' . e($data['src']) . '" alt="' . e($data['alt'] ?? '') . '" '
                    . 'style="' . $imgStyle . '" loading="lazy">'
                    . '</div>';
            })(),

            'video' => ! empty($data['embed'])
                ? '<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:6px;margin-bottom:0.75em;">'
                    . preg_replace('/<iframe/i', '<iframe style="position:absolute;top:0;left:0;width:100%;height:100%;"', $data['embed'])
                    . '</div>'
                : '',

            default => '',
        };
    }
}

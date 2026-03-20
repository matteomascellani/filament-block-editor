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
     */
    public static function renderHtml(?string $json): string
    {
        if (! $json) {
            return '';
        }

        $blocks = json_decode($json, true);

        if (! is_array($blocks)) {
            return '';
        }

        return implode("\n", array_map([static::class, 'renderBlock'], $blocks));
    }

    private static function renderBlock(array $block): string
    {
        $data = $block['data'] ?? [];
        $type = $block['type'] ?? '';

        return match ($type) {
            'paragraph' => '<p style="margin:0 0 1em;line-height:1.6;color:#374151;">' . e($data['text'] ?? '') . '</p>',

            'heading' => (function () use ($data): string {
                $level = max(1, min(6, (int) ($data['level'] ?? 2)));
                $tag   = "h{$level}";
                $sizes = [1 => '2rem', 2 => '1.5rem', 3 => '1.25rem', 4 => '1.1rem', 5 => '1rem', 6 => '0.9rem'];
                $size  = $sizes[$level] ?? '1.5rem';

                return "<{$tag} style=\"margin:0 0 0.5em;font-size:{$size};font-weight:700;color:#111827;\">"
                    . e($data['text'] ?? '')
                    . "</{$tag}>";
            })(),

            'image' => (function () use ($data): string {
                if (empty($data['src'])) {
                    return '';
                }
                $align     = in_array($data['align'] ?? '', ['left', 'center', 'right']) ? $data['align'] : 'center';
                $marginAuto = match ($align) {
                    'center' => 'margin:0 auto;',
                    'right'  => 'margin-left:auto;',
                    default  => '',
                };

                return '<div style="text-align:' . $align . ';">'
                    . '<img src="' . e($data['src']) . '" alt="' . e($data['alt'] ?? '') . '" '
                    . 'style="max-width:100%;width:' . e($data['width'] ?? '100%') . ';display:block;border-radius:4px;' . $marginAuto . '">'
                    . '</div>';
            })(),

            'columns' => '<div style="display:flex;gap:1rem;">'
                . '<div style="flex:1;min-width:0;">' . ($data['left'] ?? '') . '</div>'
                . '<div style="flex:1;min-width:0;">' . ($data['right'] ?? '') . '</div>'
                . '</div>',

            'button' => (function () use ($data): string {
                $styles = [
                    'primary'   => 'background:#4f46e5;color:#fff;',
                    'secondary' => 'background:#6b7280;color:#fff;',
                    'outline'   => 'background:transparent;color:#4f46e5;border:2px solid #4f46e5;',
                    'danger'    => 'background:#dc2626;color:#fff;',
                ];
                $style = $styles[$data['variant'] ?? 'primary'] ?? $styles['primary'];

                return '<div style="text-align:center;margin:1em 0;">'
                    . '<a href="' . e($data['url'] ?? '#') . '" '
                    . 'style="display:inline-block;padding:0.6em 1.5em;border-radius:6px;font-weight:600;text-decoration:none;' . $style . '">'
                    . e($data['text'] ?? 'Button')
                    . '</a></div>';
            })(),

            'divider' => (function () use ($data): string {
                $spacing = ['sm' => '0.5rem', 'md' => '1rem', 'lg' => '2rem'][$data['spacing'] ?? 'md'] ?? '1rem';

                return "<hr style=\"border:none;border-top:1px solid #e5e7eb;margin:{$spacing} 0;\">";
            })(),

            'spacer' => '<div style="height:' . max(4, (int) ($data['height'] ?? 32)) . 'px;"></div>',

            'html' => $data['code'] ?? '',

            default => '',
        };
    }
}

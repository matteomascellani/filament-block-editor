<?php

namespace Matteomascellani\FilamentBlockEditor;

use Filament\Contracts\Plugin;
use Filament\Panel;

class FilamentBlockEditorPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-block-editor';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }
}

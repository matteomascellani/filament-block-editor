<?php

namespace Matteomascellani\FilamentBlockEditor;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class FilamentBlockEditorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-block-editor');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-block-editor'),
        ], 'filament-block-editor-views');

        // Register the block-editor Alpine component globally so it is available
        // at page-load time before Alpine processes any x-data attributes.
        FilamentAsset::register([
            Js::make('filament-block-editor-v2', __DIR__ . '/../resources/dist/block-editor.js'),
        ]);
    }

    public function register(): void
    {
        //
    }
}

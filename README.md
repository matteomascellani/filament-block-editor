# filament-block-editor

A block editor Filament form field. Replaces a plain textarea with a fullscreen drag-and-drop HTML block builder.

## Features

- Fullscreen modal editor inside a Filament form
- Block types: Paragraph, Heading (H2/H3/H4), Image, 2 Columns, Button, Divider, Spacer, Raw HTML
- Move, duplicate and delete blocks
- Saves as JSON (block structure) to the field
- `BlockEditor::renderHtml($json)` to get the rendered HTML for frontend display
- Works with any Filament v3/v4 form

## Installation

```bash
composer require matteomascellani/filament-block-editor
```

## Usage

```php
use Matteomascellani\FilamentBlockEditor\Forms\Components\BlockEditor;

// In your Filament Resource form:
BlockEditor::make('content'),
```

The field stores a JSON string. On your frontend, render it to HTML:

```php
use Matteomascellani\FilamentBlockEditor\Forms\Components\BlockEditor;

{!! BlockEditor::renderHtml($record->content) !!}
```

## Block Types

| Type | Description |
|------|-------------|
| `paragraph` | Simple text paragraph |
| `heading` | Heading H2/H3/H4 |
| `image` | Image with URL, alt, width, alignment |
| `columns` | Two-column layout with independent text areas |
| `button` | Call-to-action button with URL and style variant |
| `divider` | Horizontal rule with configurable spacing |
| `spacer` | Empty vertical space |
| `html` | Raw HTML block for advanced use |

## Plugin (optional)

If you need to hook into the Filament panel:

```php
// In your PanelProvider:
->plugins([
    \Matteomascellani\FilamentBlockEditor\FilamentBlockEditorPlugin::make(),
])
```

## License

MIT

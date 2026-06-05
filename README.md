# wordpress-gcb/fields (PHP)

Turn a GCB `block.fields.json` controls schema into a WordPress block
**attribute** schema, in PHP, with **no dependency on the GCB plugin**. The
server-side companion to [`@wordpress-gcb/fields`](https://www.npmjs.com/package/@wordpress-gcb/fields)
(npm).

Part of **[GCB](https://gutenbergcontrolblocks.com)** — Gutenberg Control Blocks.

## Install

```bash
composer require wordpress-gcb/fields
```

## Usage

```php
use GCBFields\Schema;

$controls = json_decode(
    file_get_contents( __DIR__ . '/blocks/hero/block.fields.json' ),
    true
)['controls'];

register_block_type( __DIR__ . '/blocks/hero', [
    'attributes' => Schema::attributes( $controls ),
] );
```

`Schema::attributes()` returns the `{ type, default }` map WordPress expects —
structural controls (group/panel/tools-panel) are skipped, control types map to
the right attribute type, and each gets a sensible default (overridable per
control via `attributeType` / `default`).

## API

- `Schema::attributes(array $controls): array` — full controls → attributes map.
- `Schema::attribute_type(string $controlType): string` — one control type →
  `string|number|boolean|array|object`.
- `Schema::default_value(string $attributeType): mixed` — the empty default for a type.

## Links

- **Website:** <https://gutenbergcontrolblocks.com>
- **Source:** <https://github.com/wordpress-gcb/gcb-fields-php>
- **npm counterpart:** [`@wordpress-gcb/fields`](https://www.npmjs.com/package/@wordpress-gcb/fields)

## License

GPL-2.0-or-later

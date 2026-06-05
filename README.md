# gcb/fields (PHP / composer)

`block.fields.json` → a registered WordPress block with typed attributes, as a
small PHP library callable **without** the GCB plugin. The companion to
`@gcb/fields` (npm) on the server side.

**Status: placeholder.** To be extracted from the plugin's
`includes/Blocks/BlockLoader.php` (`generate_attributes()` + the attribute-type
mapping + the `block.fields.json` schema). See `~/sites/gcb/README.md` for how it
fits, and `gcb-pro/docs/eject-blocks-scope.md` for the architecture.

Composer package name: `gcb/fields`. License: GPL-2.0-or-later.

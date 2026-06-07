<?php
/**
 * GCB field schema → WordPress block attributes.
 *
 * The PHP companion to the @wordpress-gcb/fields npm package. Given a GCB
 * `block.fields.json` `controls` array, produce the `attributes` map WordPress
 * `register_block_type()` expects ({ type, default } per attribute) — with no
 * dependency on the GCB plugin.
 *
 * Extracted from the plugin's BlockLoader so headless / standalone blocks
 * (and the eject tooling) can register typed attributes without the plugin
 * generating them at runtime.
 *
 * @package GCBFields
 */

namespace GCBFields;

class Schema {

    /**
     * Structural controls render Inspector layout (panel headers, groups) and
     * produce no stored attribute. Kept here so the package is self-contained
     * (the plugin defines the same list in its validator).
     */
    public const STRUCTURAL_TYPES = ['group', 'panel', 'tools-panel'];

    /**
     * Build a WordPress block `attributes` map from a controls array.
     *
     * @param array $controls The `controls` array from a block.fields.json.
     * @return array<string, array{type: string, default: mixed}>
     */
    public static function attributes(array $controls): array {
        $attributes = [];

        foreach ($controls as $control) {
            // Structural controls (group / panel / tools-panel) produce no attribute.
            if (in_array($control['type'] ?? null, self::STRUCTURAL_TYPES, true)) {
                continue;
            }

            $key = $control['attributeKey'] ?? null;
            if (!is_string($key) || $key === '') {
                continue;
            }

            $attribute_type = $control['attributeType'] ?? self::attribute_type($control['type'] ?? '');

            // query-loop: the query config (postType, perPage, orderby, order,
            // pagination, taxonomy filters) is set on the CONTROL at design time,
            // not entered per-instance in the editor. Fold it into the attribute
            // default so render.php receives the query even when the block markup
            // doesn't repeat it.
            if (($control['type'] ?? '') === 'query-loop') {
                $default = isset($control['default']) && is_array($control['default'])
                    ? $control['default']
                    : self::query_loop_config($control);
                $attributes[$key] = ['type' => 'object', 'default' => $default];
                continue;
            }

            $attributes[$key] = [
                'type'    => $attribute_type,
                'default' => $control['default'] ?? self::default_value($attribute_type),
            ];
        }

        return $attributes;
    }

    /**
     * Pull the query-loop config keys off a control into a plain config array,
     * so it can seed the attribute default. Unknown keys are ignored.
     *
     * @param array $control
     * @return array
     */
    public static function query_loop_config(array $control): array {
        $cfg = [];
        foreach (['postType', 'perPage', 'orderby', 'order', 'pagination', 'enableTaxonomyFilter', 'filterTaxonomies'] as $k) {
            if (array_key_exists($k, $control)) {
                $cfg[$k] = $control[$k];
            }
        }
        return $cfg;
    }

    /**
     * Map a GCB control type to its WordPress block-attribute type.
     *
     * @param string $control_type
     * @return string One of: string|number|boolean|array|object.
     */
    public static function attribute_type(string $control_type): string {
        $map = [
            'number'         => 'number',
            'range'          => 'number',
            'toggle'         => 'boolean',
            'checkbox'       => 'boolean',
            'checkbox-group' => 'array',
            'image'          => 'object',
            'gallery'        => 'array',
            'file'           => 'object',
            'post-object'    => 'object',
            'taxonomy'       => 'array',
            'user'           => 'object',
            'relationship'   => 'array',
            'icon'           => 'object',
            'url'            => 'object',
            'google-map'     => 'object',
            'heading-level'  => 'object',
            'repeater'       => 'array',
        ];
        return $map[$control_type] ?? 'string';
    }

    /**
     * The empty/default value for a given attribute type.
     *
     * @param string $attribute_type
     * @return mixed
     */
    public static function default_value(string $attribute_type) {
        switch ($attribute_type) {
            case 'number':  return 0;
            case 'boolean': return false;
            case 'array':   return [];
            case 'object':  return (object) [];
            default:        return '';
        }
    }
}

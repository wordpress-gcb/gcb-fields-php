<?php

namespace GCBFields\Tests;

use GCBFields\Schema;
use PHPUnit\Framework\TestCase;

class SchemaTest extends TestCase {

    public function test_text_control_becomes_string_attribute(): void {
        $attrs = Schema::attributes([
            ['type' => 'text', 'attributeKey' => 'heading'],
        ]);
        $this->assertSame(['type' => 'string', 'default' => ''], $attrs['heading']);
    }

    public function test_explicit_default_is_kept(): void {
        $attrs = Schema::attributes([
            ['type' => 'text', 'attributeKey' => 'heading', 'default' => 'Hi'],
        ]);
        $this->assertSame('Hi', $attrs['heading']['default']);
    }

    public function test_structural_controls_produce_no_attribute(): void {
        $attrs = Schema::attributes([
            ['type' => 'group', 'id' => 'panel'],
            ['type' => 'panel', 'id' => 'p2'],
            ['type' => 'tools-panel', 'id' => 'p3'],
            ['type' => 'text', 'attributeKey' => 'name'],
        ]);
        $this->assertSame(['name'], array_keys($attrs));
    }

    public function test_controls_without_attribute_key_are_skipped(): void {
        $attrs = Schema::attributes([
            ['type' => 'text'],                       // no key
            ['type' => 'text', 'attributeKey' => ''], // empty key
            ['type' => 'text', 'attributeKey' => 'ok'],
        ]);
        $this->assertSame(['ok'], array_keys($attrs));
    }

    public function test_attribute_type_mapping(): void {
        $this->assertSame('number',  Schema::attribute_type('range'));
        $this->assertSame('boolean', Schema::attribute_type('toggle'));
        $this->assertSame('array',   Schema::attribute_type('gallery'));
        $this->assertSame('object',  Schema::attribute_type('image'));
        $this->assertSame('string',  Schema::attribute_type('unknown-type'));
    }

    /** The three the map used to leave as string, against their own docs (2026-10-07). */
    public function test_multi_select_and_link_controls_store_what_their_docs_say(): void {
        $this->assertSame('array', Schema::attribute_type('button-group'));
        $this->assertSame('object', Schema::attribute_type('page-link'));
        $this->assertSame('object', Schema::attribute_type('query-loop'));
        $attrs = Schema::attributes([
            ['id' => 'a', 'type' => 'button-group', 'attributeKey' => 'days'],
            ['id' => 'b', 'type' => 'page-link', 'attributeKey' => 'more'],
        ]);
        $this->assertSame(['type' => 'array', 'default' => []], $attrs['days']);
        $this->assertSame('object', $attrs['more']['type']);
    }

    public function test_query_loop_default_is_seeded_from_config(): void {
        // The query config lives on the control (design-time); it must become the
        // attribute default so render.php receives the query without per-instance
        // markup repeating it.
        $attrs = Schema::attributes([
            [
                'type' => 'query-loop', 'attributeKey' => 'q', 'label' => 'People',
                'attributeType' => 'object',
                'postType' => 'team-member', 'perPage' => 9, 'orderby' => 'title',
                'order' => 'ASC', 'pagination' => 'numbered',
                'enableTaxonomyFilter' => true,
                'filterTaxonomies' => [['slug' => 'department', 'label' => 'Department']],
            ],
        ]);
        $this->assertSame('object', $attrs['q']['type']);
        $this->assertSame('team-member', $attrs['q']['default']['postType']);
        $this->assertSame(9, $attrs['q']['default']['perPage']);
        $this->assertSame('numbered', $attrs['q']['default']['pagination']);
        $this->assertSame('department', $attrs['q']['default']['filterTaxonomies'][0]['slug']);
    }

    public function test_query_loop_explicit_default_wins(): void {
        $attrs = Schema::attributes([
            ['type' => 'query-loop', 'attributeKey' => 'q', 'postType' => 'x', 'default' => ['postType' => 'override']],
        ]);
        $this->assertSame('override', $attrs['q']['default']['postType']);
    }

    public function test_attributeType_override_wins(): void {
        $attrs = Schema::attributes([
            ['type' => 'toggle-group', 'attributeKey' => 'side', 'attributeType' => 'string', 'default' => 'left'],
        ]);
        $this->assertSame('string', $attrs['side']['type']);
        $this->assertSame('left', $attrs['side']['default']);
    }

    public function test_default_values_per_type(): void {
        $this->assertSame(0,     Schema::default_value('number'));
        $this->assertSame(false, Schema::default_value('boolean'));
        $this->assertSame([],    Schema::default_value('array'));
        $this->assertEquals((object) [], Schema::default_value('object'));
        $this->assertSame('',    Schema::default_value('string'));
    }
}

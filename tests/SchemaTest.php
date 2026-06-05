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

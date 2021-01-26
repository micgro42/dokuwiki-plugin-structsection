<?php

declare(strict_types=1);

use dokuwiki\plugin\structsection\types\Section;

/**
 * Tests for the struct Section type
 *
 * @group plugin_structsection
 * @group plugins
 */
class SectionTest extends PHPUnit\Framework\TestCase {

    /** @var array alway enable the needed plugins */
    protected $pluginsEnabled = array('struct', 'sqlite');

    public function test_valueEditorTest() {
        $section = new Section();
        $name ='testName';
        $rawValue='testRawValue';
        $htmlID='testHtmlId';

        $actual = $section->valueEditor($name, $rawValue, $htmlID);

        self::assertEquals(
            "<textarea name=\"$name\" class=\"struct_section\" id=\"$htmlID\">$rawValue</textarea>",
            $actual
        );
    }

    // TODO: add test for placeholder in config
}

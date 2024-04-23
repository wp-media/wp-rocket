<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\Weglot;

use WP_Rocket\ThirdParty\Plugins\I18n\Weglot;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\I18n\Weglot::add_langs_to_referer
 */
class Test_AddLangsToReferer extends TestCase {

    /**
     * @var Weglot
     */
    protected $weglot;

    public function setUp(): void {
        parent::setUp();

        $this->weglot = new Weglot();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($config) {
        Functions\expect('weglot_get_current_language')->andReturn($config['lang']);
		Functions\expect('wp_parse_url')->andReturn(parse_url($config['referer']));

        $this->assertSame(
            $config['expected'],
            $this->weglot->add_langs_to_referer($config['referer'])
        );
    }
}

<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\I18n\Weglot;

use WP_Rocket\ThirdParty\Plugins\I18n\Weglot;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;


/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\I18n\Weglot::add_langs_to_referer
 * @group Weglot
 */
class Test_addLangsToReferer extends TestCase {

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
	public function testShouldReturnAsExpected( $config, $expected ) {
        Functions\expect('weglot_get_current_language')->andReturn($config['lang']);

		$this->assertSame(
			$config['expected'],
			apply_filters( 'rocket_admin_bar_referer', $config['referer'] )
		);
	}
}

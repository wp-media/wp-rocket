<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::set_current_language
 * @group TranslatePress
 */
class Test_SetCurrentLanguage extends TestCase {
    /**
     * @var TranslatePress
     */
    protected $translatepress;

    protected function setUp(): void {
        parent::setUp();

        $this->translatepress = new TranslatePress();
    }

	protected function tearDown(): void {
		unset( $GLOBALS['TRP_LANGUAGE'] );

		parent::tearDown();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $current_language, $expected ) {
		$GLOBALS['TRP_LANGUAGE'] = $config['trp_language'];

        $this->assertSame(
			$expected,
			$this->translatepress->set_current_language( $current_language )
		);
    }
}

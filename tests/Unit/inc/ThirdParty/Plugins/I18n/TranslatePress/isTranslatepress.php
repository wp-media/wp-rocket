<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::is_translatepress
 * @group TranslatePress
 */
class Test_IsTranslatepress extends TestCase {
    /**
     * @var TranslatePress
     */
    protected $translatepress;

    protected function setUp(): void {
        parent::setUp();

        $this->translatepress = new TranslatePress();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $identifier, $expected ) {
		Functions\when( 'trp_get_languages' )->justReturn( $config['languages'] );

        $this->assertSame(
			$expected,
			$this->translatepress->is_translatepress( $identifier )
		);
    }
}

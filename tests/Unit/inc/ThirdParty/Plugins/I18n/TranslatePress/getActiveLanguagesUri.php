<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::get_active_languages_uri
 * @group TranslatePress
 */
class Test_GetActiveLanguagesUri extends TestCase {
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
    public function testShouldReturnAsExpected( $urls, $expected ) {
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

        $this->assertSame(
			$expected,
			$this->translatepress->get_active_languages_uri( $urls )
		);
    }
}

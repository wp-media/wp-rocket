<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use Brain\Monkey\Functions;
use TRP_Url_Converter;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::detect_homepage
 * @group TranslatePress
 */
class Test_detectHomepage extends TestCase {
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
    public function testShouldReturnAsExpected( $config, $expected ) {
		TRP_Url_Converter::$lang = $config['language'];

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

        $this->assertSame(
			$expected,
			$this->translatepress->detect_homepage( $config['home_url'], $config['url'] )
		);
    }
}

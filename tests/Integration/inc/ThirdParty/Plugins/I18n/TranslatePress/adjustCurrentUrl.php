<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\I18n\TranslatePress;

use TRP_Url_Converter;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::adjust_current_url
 * @group TranslatePress
 */
class Test_adjustCurrentUrl extends TestCase {
    public function tearDown(): void {
		TRP_Url_Converter::$lang = '';
		TRP_Url_Converter::$slug = '';
		parent::tearDown();
	}
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		TRP_Url_Converter::$lang = $config['language'];
		TRP_Url_Converter::$slug = $config['slug'];

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_current_url', $config['current_url'] )
		);
	}
}

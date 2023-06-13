<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\I18n\TranslatePress;

use TRP_Url_Converter;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::detect_homepage
 * @group TranslatePress
 */
class Test_detectHomepage extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		TRP_Url_Converter::$url = $config['url_language'];
		TRP_Url_Converter::$lang = $config['language'];
		$this->assertSame($expected['result'], apply_filters('rocket_rucss_is_home_url', $config['home_url'], $config['url']));
    }
}

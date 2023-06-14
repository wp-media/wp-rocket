<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use Mockery;
use TRP_Url_Converter;
use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::detect_homepage
 * @group TranslatePress
 */
class Test_detectHomepage extends TestCase {

    /**
     * @var TranslatePress
     */
    protected $translatepress;

    public function set_up() {
        parent::set_up();

        $this->translatepress = new TranslatePress();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		TRP_Url_Converter::$url = $config['url_language'];
		TRP_Url_Converter::$lang = $config['language'];
		Functions\when('home_url')->justReturn('home_url');
        $this->assertSame($expected['result'], $this->translatepress->detect_homepage($config['home_url'], $config['url']));
    }
}

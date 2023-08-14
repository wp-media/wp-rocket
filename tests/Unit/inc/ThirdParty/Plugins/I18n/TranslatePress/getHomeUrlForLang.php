<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::get_home_url_for_lang
 * @group TranslatePress
 */
class Test_GetHomeUrlForLang extends TestCase {
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
    public function testShouldReturnAsExpected( $home_url, $lang, $expected ) {
        $this->assertSame(
			$expected,
			$this->translatepress->get_home_url_for_lang( $home_url, $lang )
		);
    }
}

<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::get_translated_post_urls
 * @group TranslatePress
 */
class Test_GetTranslatedPostUrls extends TestCase {
    /**
     * @var TranslatePress
     */
    protected $translatepress;

    protected function setUp(): void {
        parent::setUp();

		$this->stubWpParseUrl();
        $this->translatepress = new TranslatePress();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected ) {
        $this->assertSame(
			$expected,
			$this->translatepress->get_translated_post_urls( [], $config['url'], $config['post_type'], $config['regex'] )
		);
    }
}

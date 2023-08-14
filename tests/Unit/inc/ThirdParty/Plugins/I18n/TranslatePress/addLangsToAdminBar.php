<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\TranslatePress;

use WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\I18n\TranslatePress::add_langs_to_admin_bar
 * @group TranslatePress
 */
class Test_AddLangsToAdminBar extends TestCase {
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
    public function testShouldReturnAsExpected( $langlinks, $expected ) {
        $this->assertSame(
			$expected,
			$this->translatepress->add_langs_to_admin_bar( $langlinks )
		);
    }
}

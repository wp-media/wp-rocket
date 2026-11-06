<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Settings;

use WP_Rocket\Engine\CriticalPath\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\Admin\Settings::add_hidden_async_css_mobile
 *
 * @group  CriticalPath
 * @group  CriticalPathSettings
 */
class Test_AddHiddenAsyncCssMobile extends TestCase {
	use AdminTrait;

	private $settings;

	public function setUp() : void {
		parent::setUp();

		$this->setUpMocks();

		$this->settings = new Settings(
			$this->options,
			$this->beacon,
			$this->critical_css,
			'wp-content/plugins/wp-rocket/views/metabox/cpcss'
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddValueToArray( $hidden_fields, $expected ) {
        $this->assertSame(
			$expected,
			$this->settings->add_hidden_async_css_mobile( $hidden_fields )
		);
	}
}

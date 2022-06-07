<?php

namespace WP_Rocket\Tests\Fixtures\inc\ThirdParty\Plugins\Jetpack;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Jetpack;
use Brain\Monkey\Functions;

class Test_ActivateJetpackCookieLaw extends TestCase
{
	protected $option;
	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$this->option = Mockery::mock(Options_Data::class);
		$this->subscriber = new Jetpack($this->option);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		$this->option->expects()->get('rocket_jetpack_eu_cookie_widget')->andReturn($config['is_eu_widget_present']);
		Functions\expect('is_active_widget')->with(false, false, 'eu_cookie_law_widget')->andReturn($config['']);
		$this->subscriber->activate_jetpack_cookie_law();
	}
}

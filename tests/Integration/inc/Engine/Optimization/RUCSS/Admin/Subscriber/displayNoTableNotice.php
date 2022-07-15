<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::display_no_table_notice
 *
 * @group  Beacon
 * @group  AdminOnly
 */
class Test_DisplayNoTableNotice extends TestCase
{
	protected $rucss;

	public function setUp()
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
	}

	public function tearDown()
	{
		remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->rucss = $config['rucss'];
		ob_start();
		do_action('admin_notices');
		$result = ob_end_flush();
		$this->assertContains(
			$this->format_the_html($expected ),
			$result
		);
	}

	public function rucss() {
		return $this->rucss;
	}
}

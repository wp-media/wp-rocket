<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use WP_Rocket\Tests\Fixtures\WP_Filesystem_Direct;
use WP_Rocket\Tests\Integration\AdminTestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\DBTrait;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::display_no_table_notice
 *
 * @group  RUCSS
 * @group  AdminOnly
 */
class Test_DisplayNoTableNotice extends AdminTestCase
{

	use DBTrait;

	protected $rucss;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::uninstallAll();
	}

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
		$this->setRoleCap( 'administrator', 'rocket_manage_options' );
	}

	public function tear_down()
	{
		$this->removeRoleCap( 'administrator', 'rocket_manage_options' );
		remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		$this->rucss = $config['remove_unused_css'];
		$filesystem_mock = Mockery::mock(WP_Filesystem_Direct::class);
		$filesystem_mock->shouldReceive('is_writable')->zeroOrMoreTimes()->andReturn(true);
		$filesystem_mock->shouldReceive('is_readable')->zeroOrMoreTimes()->andReturn(true);
		$filesystem_mock->shouldReceive('exists')->zeroOrMoreTimes()->andReturn(true);
		$filesystem_mock->shouldReceive('is_dir')->zeroOrMoreTimes()->andReturn(true);
		$filesystem_mock->shouldReceive('mkdir')->zeroOrMoreTimes()->andReturn(true);
		$this->setCurrentUser('administrator');
		set_current_screen( 'settings_page_wprocket' );
		Functions\when('rocket_direct_filesystem')->justReturn($filesystem_mock);

		ob_start();
		do_action('admin_notices');
		$result = ob_get_clean();

		$container = apply_filters('rocket_container', null);

		$table = $container->get('rucss_usedcss_table');

		$content = str_replace('wpr_table', $table->get_name(), $expected['content']);

		if($expected['contains']) {
			$this->assertStringContainsString(
				$this->format_the_html( $content ),
				$this->format_the_html( $result )
			);
		} else {
			$this->assertStringNotContainsString(
				$this->format_the_html( $content ),
				$this->format_the_html( $result )
			);
		}
	}

	public function rucss() {
		return $this->rucss;
	}
}

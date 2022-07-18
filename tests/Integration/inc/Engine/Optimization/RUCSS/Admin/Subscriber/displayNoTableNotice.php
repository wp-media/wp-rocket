<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use WP_Rocket\Tests\Fixtures\WP_Filesystem_Direct;
use WP_Rocket\Tests\Integration\AdminTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::display_no_table_notice
 *
 * @group  RUCSS
 * @group  AdminOnly
 */
class Test_DisplayNoTableNotice extends AdminTestCase
{

	protected $rucss;
	protected $used_css;

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
		$container = apply_filters( 'rocket_container', null );
		$this->used_css = $container->get( 'rucss_usedcss_table' );
		$this->setRoleCap( 'administrator', 'rocket_manage_options' );
	}

	public function tear_down()
	{
		$this->removeRoleCap( 'administrator', 'rocket_manage_options' );
		remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'rucss']);
		if ( $this->used_css->exists() ) {
			$this->used_css->uninstall();
		}
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
		$this->setCurrentUser('administrator');
		set_current_screen( 'settings_page_wprocket' );
		Functions\when('rocket_direct_filesystem')->justReturn($filesystem_mock);

		if($config['table_exists']) {
			$this->used_css->install();
		}

		ob_start();
		do_action('admin_notices');
		$result = ob_get_clean();


		if($expected['contains']) {
			$this->assertStringContainsString(
				$this->format_the_html( $expected['content'] ),
				$this->format_the_html( $result )
			);
		} else {
			$this->assertStringNotContainsString(
				$this->format_the_html( $expected['content'] ),
				$this->format_the_html( $result )
			);
		}
	}

	public function rucss() {
		return $this->rucss;
	}
}

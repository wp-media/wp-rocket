<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::display_error_notice
 *
 * @group  RUCSS
 * @group  AdminOnly
 */
class Test_DisplaySaasErrorNotice extends AdminTestCase {

	public function set_up() {
		parent::set_up();
		add_filter('pre_get_rocket_option_remove_unused_css', [$this, 'remove_unused_css']);
		$this->setRoleCap( 'administrator', 'rocket_manage_options' );
	}

	public function tear_down() {
		set_current_screen( 'front' );

		$this->removeRoleCap( 'administrator', 'rocket_manage_options' );
		remove_filter('pre_get_rocket_option_remove_unused_css', [$this, 'remove_unused_css']);
		delete_transient('wp_rocket_rucss_errors_count');
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->config = $config;
		$filesystem_mock = Mockery::mock(WP_Filesystem_Direct::class);
		$filesystem_mock->shouldReceive('is_writable')->zeroOrMoreTimes()->andReturn(true);
		$filesystem_mock->shouldReceive('is_readable')->zeroOrMoreTimes()->andReturn(true);
		$filesystem_mock->shouldReceive('exists')->zeroOrMoreTimes()->andReturn(true);
		$filesystem_mock->shouldReceive('is_dir')->zeroOrMoreTimes()->andReturn(true);
		Functions\when('rocket_direct_filesystem')->justReturn($filesystem_mock);

		$this->setCurrentUser('administrator');

		if($config['transient_exists']) {
			set_transient('wp_rocket_rucss_errors_count', true, MINUTE_IN_SECONDS);
		}
		set_current_screen( 'settings_page_wprocket' );

		ob_start();
		do_action('admin_notices');
		$actual = ob_get_clean();
		if($expected['contains']) {
			$this->assertStringContainsString(
				$this->format_the_html( $expected['content'] ),
				$this->format_the_html( $actual )
			);
		} else {
			$this->assertStringNotContainsString(
				$this->format_the_html( $expected['content'] ),
				$this->format_the_html( $actual )
			);
		}

	}

	public function remove_unused_css() {
		return $this->config['remove_unused_css'];
	}
}

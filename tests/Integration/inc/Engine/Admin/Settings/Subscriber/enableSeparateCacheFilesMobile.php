<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Subscriber::enable_separate_cache_files_mobile
 *
 * @group Settings
 */
class Test_EnableSeparateCacheFilesMobile extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'enable_separate_cache_files_mobile', 10 );
		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function tear_down() {
		parent::tear_down();

		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );
		$this->restoreWpHook( 'wp_rocket_upgrade' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnableSeparateCacheFilesMobile( $config ) {
		$options = get_option( 'wp_rocket_settings', [] );
		$options['cache_mobile'] = $config['cache_mobile'];
		$options['do_caching_mobile_files'] = $config['cache_mobile'];
		update_option( 'wp_rocket_settings', $options );

		do_action( 'wp_rocket_upgrade', '3.16', '3.15' );

		$options   = get_option( 'wp_rocket_settings' );
		$do_caching_mobile_files = $options['do_caching_mobile_files'];

		if ( $config['cache_mobile'] ) {
			$this->assertEquals( 1, $do_caching_mobile_files );
		} else {
			$this->assertEquals( 0, $do_caching_mobile_files );
		}
	}
}

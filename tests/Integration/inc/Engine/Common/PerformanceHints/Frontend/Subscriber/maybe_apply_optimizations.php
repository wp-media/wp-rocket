<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\PerformanceHints\Frontend\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\Frontend\Subscriber::maybe_apply_optimizations
 *
 * @group PerformanceHints
 */
class Test_maybe_apply_optimizations extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Common/PerformanceHints/Frontend/Subscriber/maybe_apply_optimizations.php';

	protected $config;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists().
		self::installAtfTable();
		self::installLrcTable();
	}

	public static function tear_down_after_class() {
		self::uninstallAtfTable();
		self::uninstallLrcTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'maybe_apply_optimizations', 17 );
	}

	public function tear_down() {
		remove_filter( 'rocket_performance_hints_optimization_delay', [ $this, 'add_delay' ] );

		$this->restoreWpHook( 'rocket_buffer' );
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config = $config;

		if ( ! empty( $config['atf']['row'] ) ) {
			self::addLcp( $config['atf']['row'] );
		}
		if ( ! empty( $config['lrc']['row'] ) ) {
			self::addLrc( $config['lrc']['row'] );
		}

		if ( isset( $config['filter_delay'] ) ) {
			add_filter( 'rocket_performance_hints_optimization_delay', [ $this, 'add_delay' ] );
		}

		Functions\when( 'wp_create_nonce' )->justReturn( '96ac96b69e' );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $config['html'] )
		);
	}

	public function add_delay() {
		return $this->config['filter_delay'];
	}
}

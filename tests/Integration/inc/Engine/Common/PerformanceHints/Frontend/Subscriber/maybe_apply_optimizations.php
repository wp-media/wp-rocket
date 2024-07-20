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

	public function set_up() {
		parent::set_up();

		self::installAtfTable();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'maybe_apply_optimizations', 17 );
	}

	public function tear_down() {
		self::uninstallAtfTable();

		remove_filter( 'rocket_lcp_delay', [ $this, 'add_delay' ] );

		$this->restoreWpHook( 'rocket_buffer' );
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config = $config;

		if ( ! empty( $config['row'] ) ) {
			self::addLcp( $config['row'] );
		}

		if ( isset( $config['filter_delay'] ) ) {
			add_filter( 'rocket_lcp_delay', [ $this, 'add_delay' ] );
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

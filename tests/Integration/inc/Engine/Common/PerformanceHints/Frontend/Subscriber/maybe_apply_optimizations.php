<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Common\PerformanceHints\Frontend\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\Frontend\Subscriber::maybe_apply_optimizations
 *
 * @group PerformanceHints
 */
class Test_MaybeApplyOptimizations extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Common/PerformanceHints/Frontend/Subscriber/maybe_apply_optimizations.php';
	protected $config;
	private $cached_user = false;
	private $user_id = 0;

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

		add_filter( 'rocket_disable_meta_generator', '__return_true' );

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'maybe_apply_optimizations', 17 );
	}

	public function tear_down() {
		unset( $_GET );
		remove_filter( 'rocket_performance_hints_optimization_delay', [ $this, 'add_delay' ] );
		remove_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'get_cache_user' ] );
		remove_filter( 'rocket_disable_meta_generator', '__return_true' );
		$this->restoreWpHook( 'rocket_buffer' );

		if ( $this->user_id > 0 ) {
			wp_delete_user( $this->user_id );
		}

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config = $config;
		$this->cached_user = $config['user_cache_enabled'] ?? false;
		$this->donotrocketoptimize = $config['donotrocketoptimize'] ?? null;

		if ( isset( $config['query_string'] ) ) {
			$_GET[ $config['query_string'] ] = 1;
		}

		if ( isset( $config['sass_visit'] ) ) {
			$_GET[ 'wpr_imagedimensions' ] = $config['sass_visit'];
		}

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

		if ( isset( $config['is_logged_in'] ) ) {
			$this->user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
			wp_set_current_user( $this->user_id );
		}

		// Override cache_logged_user option for this specific scenario.
		add_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'get_cache_user' ] );

		$this->assertSame(
			trim($expected),
			trim(apply_filters( 'rocket_buffer', $config['html'] ))
		);
	}

	public function add_delay() {
		return $this->config['filter_delay'];
	}

	public function get_cache_user() {
		return $this->cached_user;
	}
}

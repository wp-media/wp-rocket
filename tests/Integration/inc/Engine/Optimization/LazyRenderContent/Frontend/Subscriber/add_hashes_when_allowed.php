<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\LazyRenderContent\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Subscriber::add_hashes_when_allowed
 *
 * @group PerformanceHints
 */
class Test_AddHashesWhenAllowed extends TestCase {
	private $filter;
	private $cache_logged_user = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install in set_up_before_class because of exists().
		self::installLrcTable();
	}

	public static function tear_down_after_class() {
		self::uninstallLrcTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'add_hashes_when_allowed', 16 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_buffer' );
		remove_filter( 'rocket_lrc_optimization', '__return_false' );
		remove_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'returnCacheLoggedUser' ] );
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->filter            = $config['filter'];
		$this->cache_logged_user = $config['cache_logged_user'] ?? 0;

		add_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'returnCacheLoggedUser' ] );
		add_filter( 'rocket_lrc_optimization', [ $this, 'returnFilter' ] );

		if ( ! empty( $config['logged_in'] ) ) {
			wp_set_current_user( 1 );
		}

		self::addLrc( $config['row'] );

		add_filter( 'rocket_lrc_optimization', '__return_true' );

		$this->assertSame(
			$expected['html'],
			apply_filters( 'rocket_buffer', $config['html'] )
		);
	}

	public function returnFilter() {
		return $this->filter;
	}

	public function returnCacheLoggedUser() {
		return $this->cache_logged_user;
	}
}

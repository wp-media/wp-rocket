<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\LazyRenderContent\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Subscriber::add_hashes
 *
 * @group PerformanceHints
 */
class Test_AddHashes extends TestCase {
  private $max_hashes;

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

		$this->max_hashes = null;
		$this->unregisterAllCallbacksExcept( 'rocket_performance_hints_buffer', 'add_hashes', 16 );

	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_performance_hints_buffer' );
		remove_filter( 'rocket_lrc_optimization', '__return_false' );
		remove_filter( 'rocket_lrc_max_hashes', [ $this, 'set_lrc_max_hashes' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldWorkAsExpected( $config, $expected ) {
		self::addLrc( $config['row'] );

		add_filter( 'rocket_lrc_optimization', '__return_true' );
		add_filter( 'rocket_lrc_exclusions', function() use ($config) {
			return $config['exclusions'] ?? [];
		});


		if ( isset( $config['max_hashes'] ) ) {
			$this->max_hashes = $config['max_hashes'];
			add_filter( 'rocket_lrc_max_hashes', [ $this, 'set_lrc_max_hashes' ] );
		}


		$this->assertSame(
			$expected['html'],
			apply_filters( 'rocket_performance_hints_buffer', $config['html'] )
		);
	}

	public function set_lrc_max_hashes() {
		return $this->max_hashes;
	}
}

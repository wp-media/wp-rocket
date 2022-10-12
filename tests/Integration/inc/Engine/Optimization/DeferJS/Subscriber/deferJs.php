<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DeferJS\Subscriber;

use WP_Rocket\Tests\Integration\ContentTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\Subscriber::defer_js
 *
 * @group  DeferJS
 */
class Test_DeferJs extends TestCase {
	use ContentTrait;

	private $defer_js;
	private $exclude_defer_js;

	public function set_up() {
		parent::set_up();

		set_current_screen( 'front' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'set_defer_js' ] );
		remove_filter( 'pre_get_rocket_option_exclude_defer_js', [ $this, 'set_exclude_defer_js' ] );
		delete_post_meta( 100, '_rocket_exclude_defer_all_js' );
		delete_transient( 'wpr_dynamic_lists' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->donotrocketoptimize = $config['donotrocketoptimize'];
		$this->defer_js            = $config['options']['defer_all_js'];
		$this->exclude_defer_js    = $config['options']['exclude_defer_js'];

		$this->goToContentType(
			[
				'type'      => 'is_post',
				'post_data' => [
					'import_id' => 100,
				],
			]
		);

		add_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'set_defer_js' ] );
		add_filter( 'pre_get_rocket_option_exclude_defer_js', [ $this, 'set_exclude_defer_js' ] );

		set_transient( 'wpr_dynamic_lists', $config['exclusions'], HOUR_IN_SECONDS );

		if ( $config['post_meta'] ) {
			add_post_meta( 100, '_rocket_exclude_defer_all_js', 1, true );
		}

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $html )
		);
	}

	public function set_defer_js() {
		return $this->defer_js;
	}

	public function set_exclude_defer_js() {
		return $this->exclude_defer_js;
	}
}

<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Subscriber;

use WP_Rocket\Tests\Integration\ContentTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DelayJS\Subscriber::delay_js
 *
 * @group Optimize
 * @group DelayJS
 *
 * @uses rocket_get_constant()
 */
class Test_DelayJs extends TestCase {
	use ContentTrait;

	private $delay_js = 0;
	private $delay_js_exclusions = [];
	private $post;

	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'delay_js', 26 );
	}

	public function tear_down() {
		unset( $_GET['nowprocket'] );

		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js' ] );
		remove_filter( 'pre_get_rocket_option_delay_js_exclusions', [ $this, 'set_delay_js_exclusions' ] );
		delete_transient( 'wpr_dynamic_lists' );

		if ( isset( $this->post->ID ) ) {
			delete_post_meta( $this->post->ID, '_rocket_exclude_delay_js', 1, true );
		}

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->donotrocketoptimize = $config['donotoptimize'];
		$this->delay_js            = $config['delay_js'];
		$this->delay_js_exclusions = $config['delay_js_exclusions'];
		$this->post = $this->goToContentType( $config );

		if ( $config['post-excluded'] ) {
			add_post_meta( $this->post->ID, '_rocket_exclude_delay_js', 1, true );
		}

		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js' ] );
		add_filter( 'pre_get_rocket_option_delay_js_exclusions', [ $this, 'set_delay_js_exclusions' ] );

		set_transient( 'wpr_dynamic_lists', $config['exclusions'], HOUR_IN_SECONDS );

		if ( $config['bypass'] ) {
			$_GET['nowprocket'] = 1;
		}

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $html )
		);
	}

	public function set_delay_js() {
		return $this->delay_js;
	}

	public function set_delay_js_exclusions() {
		return $this->delay_js_exclusions;
	}
}

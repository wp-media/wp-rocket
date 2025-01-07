<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\ContentTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering   \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::maybe_disable_preload_fonts
 *
 * @group RUCSS
 */
class Test_MaybeDisablePreloadFonts extends TestCase {
	use ContentTrait;

	private $rucss;
	private $post;

	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'rucss' ] );

		if ( isset( $this->post->ID ) ) {
			delete_post_meta( $this->post->ID, '_rocket_exclude_remove_unused_css', 1 );
		}

		remove_filter( 'pre_option_wp_rocket_prewarmup_stats', [ $this, 'return_prewarmup_stats' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->post = $this->goToContentType( $config );

		$this->donotrocketoptimize = $config['DONOTROCKETOPTIMIZE'];

		$this->rucss = $config['options']['remove_unused_css'];

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'rucss' ] );

		if ( $config['is_rocket_post_excluded_option']['remove_unused_css'] ) {
			add_post_meta( $this->post->ID, '_rocket_exclude_remove_unused_css', 1, true );
		}

		add_filter( 'pre_option_wp_rocket_prewarmup_stats', [ $this, 'return_prewarmup_stats' ] );

		$value = apply_filters( 'rocket_disable_preload_fonts', false );

		if ( $expected ) {
			$this->assertTrue( $value );
		} else {
			$this->assertFalse( $value );
		}
	}

	public function rucss() {
		return $this->rucss;
	}

	public function return_prewarmup_stats( $option_value ) {
		return [
			'allow_optimization' => true,
		];
	}
}

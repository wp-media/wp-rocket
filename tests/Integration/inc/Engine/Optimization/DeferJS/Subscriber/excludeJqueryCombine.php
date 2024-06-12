<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DeferJS\Subscriber;

use WP_Rocket\Tests\Integration\ContentTrait;
use WP_Rocket\Tests\Integration\DynamicListsTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DeferJS\Subscriber::exclude_jquery_combine
 *
 * @group DeferJS
 */
class Test_ExcludeJqueryCombine extends TestCase {
	use ContentTrait, DynamicListsTrait;

	private $defer_js;
	private $combine_js;

	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		set_current_screen( 'front' );
		$this->setup_lists();
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		remove_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'set_defer_js' ] );
		remove_filter( 'pre_get_rocket_option_minify_concatenate_js', [ $this, 'set_minify_concatenate_js' ] );
		delete_post_meta( 100, '_rocket_exclude_defer_all_js' );
		$this->teardown_lists();

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $excluded, $expected ) {
		$this->donotrocketoptimize = $config['donotrocketoptimize'];
		$this->defer_js            = $config['options']['defer_all_js'];
		$this->combine_js          = $config['options']['minify_concatenate_js'];

		$this->goToContentType(
			[
				'type'      => 'is_post',
				'post_data' => [
					'import_id' => 100,
				],
			]
		);

		add_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'set_defer_js' ] );
		add_filter( 'pre_get_rocket_option_minify_concatenate_js', [ $this, 'set_minify_concatenate_js' ] );

		if ( $config['post_meta'] ) {
			add_post_meta( 100, '_rocket_exclude_defer_all_js', 1, true );
		}

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_exclude_js', $excluded )
		);
	}

	public function set_defer_js() {
		return $this->defer_js;
	}

	public function set_minify_concatenate_js() {
		return $this->combine_js;
	}
}

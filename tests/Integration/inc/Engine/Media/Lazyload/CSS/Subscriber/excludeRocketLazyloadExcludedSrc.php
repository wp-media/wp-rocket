<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::exclude_rocket_lazyload_excluded_src
 *
 * @group LazyloadCSS
 */
class Test_ExcludeRocketLazyloadExcludedSrc extends TestCase {
	protected $config;

	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		add_filter( 'rocket_lazyload_excluded_src', [ $this, 'rocket_lazyload_excluded_src' ] );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		remove_filter( 'rocket_lazyload_excluded_src', [ $this, 'rocket_lazyload_excluded_src' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config = $config;
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_css_image_lazyload_images_load', $config['excluded'], $config['urls'] )
		);
	}

	public function rocket_lazyload_excluded_src() {
		return $this->config['excluded_src'];
	}
}

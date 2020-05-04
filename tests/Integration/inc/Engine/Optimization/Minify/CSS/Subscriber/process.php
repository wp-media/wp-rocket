<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\Subscriber;

use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\Subscriber::process
 * @uses   \WP_Rocket\Engine\Optimization\Minify\CSS\Combine::optimize
 * @uses   \WP_Rocket\Engine\Optimization\Minify\CSS\Minify::optimize
 * @uses   ::get_rocket_parse_url
 * @uses   ::get_rocket_i18n_uri
 * @uses   ::rocket_url_to_path
 * @uses   ::rocket_direct_filesystem
 * @uses   ::rocket_mkdir_p
 * @uses   ::rocket_put_content
 *
 * @group  MinifyCSS
 */
class Test_Process extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/Subscriber/process.php';

	public function setUp() {
		$this->wp_content_dir = 'vfs://public/wordpress/wp-content';

		parent::setUp();

		// Mocks constants for the virtual filesystem.
		$this->whenRocketGetConstant();
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_css_key', [ $this, 'return_key' ] );
		$this->unsetSettings();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyCSS( $original, $expected, $settings ) {
		add_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_minify_css_key', [ $this, 'return_key' ] );

		$this->settings = $settings;
		$this->setSettings();

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $original ) )
		);
	}
}

<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

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
class Test_Process extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/Subscriber/process.php';
	protected $cnames;
	protected $zones;
	private   $settings;

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
		$this->unset_settings( $this->settings );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyCSS( $original, $expected, $settings ) {
		add_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_minify_css_key', [ $this, 'return_key' ] );

		$this->settings = $settings;
		$this->set_settings( $settings );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $original ) )
		);
	}

	private function set_settings( array $settings ) {
		foreach ( $settings as $key => $value ) {
			if ( 'minify_concatenate_css' === $key ) {
				$callback = $value === 0 ? 'return_false' : 'return_true';
				add_filter( 'pre_get_rocket_option_minify_concatenate_css', [ $this, $callback ] );
				continue;
			}

			if ( 'cdn' === $key ) {
				$callback = $value === 0 ? 'return_false' : 'return_true';
				add_filter( 'pre_get_rocket_option_cdn', [ $this, $callback ] );
				continue;
			}

			if ( 'cdn_cnames' === $key ) {
				$this->cnames = $value;
				add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'set_cnames' ] );
				continue;
			}

			if ( 'cdn_zone' === $key ) {
				$this->zones = $value;
				add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'set_zones' ] );
				continue;
			}
		}
	}

	private function unset_settings( array $settings ) {
		foreach ( $settings as $key => $value ) {
			if ( 'minify_concatenate_css' === $key ) {
				$callback = $value === 0 ? 'return_false' : 'return_true';
				remove_filter( 'pre_get_rocket_option_minify_concatenate_css', [ $this, $callback ] );
				continue;
			}

			if ( 'cdn' === $key ) {
				$callback = $value === 0 ? 'return_false' : 'return_true';
				remove_filter( 'pre_get_rocket_option_cdn', [ $this, $callback ] );
				continue;
			}

			if ( 'cdn_cnames' === $key ) {
				remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'set_cnames' ] );
				continue;
			}

			if ( 'cdn_zone' === $key ) {
				remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'set_zones' ] );
				continue;
			}
		}
	}

	public function return_key() {
		return 123456;
	}

	public function set_cnames() {
		return $this->cnames;
	}

	public function set_zones() {
		return $this->zones;
	}
}

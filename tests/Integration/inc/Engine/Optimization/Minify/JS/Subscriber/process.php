<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\JS\Subscriber;

use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\JS\Subscriber::process
 * @uses   \WP_Rocket\Engine\Optimization\Minify\JS\Combine::optimize
 * @uses   \WP_Rocket\Engine\Optimization\Minify\JS\Minify::optimize
 * @uses   ::get_rocket_parse_url
 * @uses   ::get_rocket_i18n_uri
 * @uses   ::rocket_url_to_path
 * @uses   ::rocket_direct_filesystem
 * @uses   ::rocket_mkdir_p
 * @uses   ::rocket_put_content
 *
 * @group  Optimize
 * @group  MinifyJS
 * @group  Minify
 */
class Test_Process extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/JS/Subscriber/process.php';

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_minify_js', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_js_key', [ $this, 'return_key' ] );
		remove_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'return_defer_all_js' ] );

		$this->unsetSettings();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyJS( $original, $expected, $settings ) {
		add_filter( 'pre_get_rocket_option_minify_js', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_minify_js_key', [ $this, 'return_key' ] );

		$post_56 = version_compare( get_bloginfo( 'version' ), '5.6', '>=' );

		$original = $post_56
			? $original['post-56']
			: $original['pre-56'];
		$expected['html'] = $post_56
			? $expected['post-56']
			: $expected['pre-56'];
		$files = $post_56 && isset( $expected['files-56'] )
			? $expected['files-56']
			: $expected['files'];

		$this->defer_all_js = $settings['defer_all_js'];

		add_filter( 'pre_get_rocket_option_defer_all_js', [ $this, 'return_defer_all_js' ] );

		$this->settings = $settings;
		$this->setSettings();

		$this->assertSame(
			$this->format_the_html( $expected['html'] ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $original ) )
		);

		$this->assertFilesExists( $files );
	}

	public function return_defer_all_js() {
		return $this->defer_all_js;
	}
}

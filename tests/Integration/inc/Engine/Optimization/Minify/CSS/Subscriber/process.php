<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\Subscriber;

use WP_Rocket\Tests\Integration\inc\Engine\Optimization\TestCase;
use Brain\Monkey\Functions;

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
 * @group  Optimize
 * @group  MinifyCSS
 * @group  Minify
 */
class Test_Process extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/Subscriber/process.php';

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_minify_css_key', [ $this, 'return_key' ] );
	}

	public function tear_down() {
		parent::tear_down();

		remove_filter( 'pre_get_rocket_option_minify_css', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_css_key', [ $this, 'return_key' ] );

		$this->unsetSettings();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyCSS( $original, $expected, $settings ) {
		$this->settings = $settings;
		$this->setSettings();

		$actual = apply_filters( 'rocket_buffer', $original );

		foreach ($expected['files'] as $file) {
			$file_mtime = $this->filesystem->mtime( $file );
			if ( $file_mtime ) {
				$expected['html'] = str_replace( $file."?ver={{mtime}}", $file."?ver=".$file_mtime, $expected['html'] );
			}
		}

		$this->assertSame(
			$this->format_the_html( $expected['html'] ),
			$this->format_the_html( $actual )
		);

		if ( isset( $expected['css'] ) ){
			if ( ! empty( $expected['css'] ) ) {
				$this->assertSame(
					$expected['css'],
					$this->filesystem->get_contents(
						$this->filesystem->getUrl( $expected['files'][0] )
					)
				);
				$this->assertFilesExists( $expected['files'] );
			}else{
				$this->assertFalse( $expected['css'] );
			}
		}
	}
}

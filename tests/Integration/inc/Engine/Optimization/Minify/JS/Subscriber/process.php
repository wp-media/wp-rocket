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
 * @group  MinifyJS
 * @group  Minify
 */
class Test_Process extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/JS/Subscriber/process.php';

	public function setUp() {
		$this->wp_content_dir = 'vfs://public/wp-content';

		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_minify_js', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_js_key', [ $this, 'return_key' ] );
		$this->unsetSettings();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyCSS( $original, $expected, $settings ) {
		add_filter( 'pre_get_rocket_option_minify_js', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_minify_js_key', [ $this, 'return_key' ] );

		$this->settings = $settings;
		$this->setSettings();

		$this->assertSame(
			$this->format_the_html( $expected['html'] ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $original ) )
        );

        foreach( $expected['files'] as $file ) {
            $this->assertTrue( $this->filesystem->exists( $file ) );
        }

		$this->unset_settings( $settings );
		remove_filter( 'pre_get_rocket_option_minify_js', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_minify_js_key', [ $this, 'return_key' ] );
		remove_filter( 'rocket_wp_content_dir', [ $this, 'virtual_wp_content_dir' ] );
    }

    public function virtual_wp_content_dir() {
        return $this->filesystem->getUrl( 'wp-content' );
    }

    public function return_key() {
        return 123456;
    }

    private function set_settings( array $settings ) {
        foreach ( $settings as $key => $value ) {
            if ( 'minify_concatenate_js' === $key ) {
                $callback = 0 === $value ? 'return_false' : 'return_true';
                add_filter( 'pre_get_rocket_option_minify_concatenate_js', [ $this, $callback ] );
                continue;
            }

            if ( 'cdn' === $key ) {
                $callback = 0 === $value ? 'return_false' : 'return_true';
                add_filter( 'pre_get_rocket_option_cdn', [ $this, $callback ] );
                continue;
            }

            if ( 'cdn_cnames' === $key ) {
                $this->cnames = $value;
                add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'set_cnames'] );
                continue;
            }

            if ( 'cdn_zone' === $key ) {
                $this->zones = $value;
                add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'set_zones'] );
                continue;
            }
        }
    }

    private function unset_settings( array $settings ) {
        foreach ( $settings as $key => $value ) {
            if ( 'minify_concatenate_js' === $key ) {
                $callback = 0 === $value ? 'return_false' : 'return_true';
                remove_filter( 'pre_get_rocket_option_minify_concatenate_js', [ $this, $callback ] );
                continue;
            }

            if ( 'cdn' === $key ) {
                $callback = 0 === $value ? 'return_false' : 'return_true';
                remove_filter( 'pre_get_rocket_option_cdn', [ $this, $callback ] );
                continue;
            }

            if ( 'cdn_cnames' === $key ) {
                remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'set_cnames'] );
                continue;
            }

            if ( 'cdn_zone' === $key ) {
                remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'set_zones'] );
                continue;
            }
        }
    }

    public function set_cnames() {
        return $this->cnames;
    }

    public function set_zones() {
        return $this->zones;
    }
}

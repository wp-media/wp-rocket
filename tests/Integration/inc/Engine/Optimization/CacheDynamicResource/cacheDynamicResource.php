<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\CacheDynamicResource;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\ResetsCdnDriverStateTrait;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\CacheDynamicResource::cache_dynamic_resource
 * @group  CacheDynamicResource
 */
class Test_CacheDynamicResource extends FilesystemTestCase {
	use ResetsCdnDriverStateTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/CacheDynamicResource/cacheDynamicResource.php';
	protected $cnames;
	protected $zones;
	private $isCSSTestData;
	private $minify_type;
	private $src;
	private $options;

	public function set_up() {
		parent::set_up();

		$this->isCSSTestData = false;
		$this->minify_type   = '';

		// DriverFactory resolves the active driver from Context::get_effective_cdn_state(),
		// which needs cdn_type === 'byocdn' to route to the Custom (CNAME) driver the
		// CDN-enabled data sets here exercise — the default 'rocketcdn' value would
		// otherwise route to the Disabled driver and silently stop the CDN rewrite.
		add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'return_byocdn' ] );

		// Also reset before the test runs, in case an earlier, unrelated test file resolved
		// these same container singletons under a different cdn_type first.
		$this->reset_cdn_driver_memo();
	}

	public function tear_down() {
		remove_filter( "pre_get_rocket_option_minify_{$this->minify_type}_key", [ $this, 'getMinifyKey' ] );
		remove_filter( 'pre_http_request', [ $this, 'pre_request' ] );
		remove_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'return_byocdn' ] );

		if ( $this->isCSSTestData ) {
			wp_dequeue_style( $this->src );
		} else {
			wp_dequeue_script( $this->src );
		}

		$this->unset_settings( $this->options );

		// cdn_driver_byocdn / cdn_subscriber are container singletons — reset their memoized
		// state so the next data set (which may use different cdn_cnames) isn't affected.
		$this->reset_cdn_driver_memo();

		parent::tear_down();
	}

	/**
	 * @return string
	 */
	public function return_byocdn(): string {
		return 'byocdn';
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCacheDynamicResource( $event, $src, $expected, $settings ) {
		// Set up the test.
		$this->isCSSTestData = ( 'style_loader_src' === $event );
		$this->src = $src;
		$this->options = $settings;
		$this->minify_type   = $this->isCSSTestData ? 'css' : 'js';
		add_filter( "pre_get_rocket_option_minify_{$this->minify_type}_key", [ $this, 'getMinifyKey' ] );

		$this->set_settings( $settings );

		// Enqueue the resource.
		if ( $this->isCSSTestData ) {
			wp_enqueue_style( $src, $src );
		} else {
			wp_enqueue_script( $src, $src );
		}

		add_filter( 'pre_http_request', [ $this, 'pre_request' ] );

		// Apply the filter event. Check the result.
		$this->assertSame( $expected, apply_filters( $event, $src, '' ) );
	}

	public function getMinifyKey() {
		return '123456';
	}

	private function set_settings( array $settings ) {
        foreach ( $settings as $key => $value ) {
            if ( 'cdn' === $key ) {
                $callback = $value === 0 ? 'return_false' : 'return_true';
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
            if ( 'cdn' === $key ) {
                $callback = $value === 0 ? 'return_false' : 'return_true';
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

	public function pre_request() {
		return [
			'headers' => [],
			'body' => 'test',
			'response' => [],
			'cookies' => [],
			'filename' => ''
		];
	}
}

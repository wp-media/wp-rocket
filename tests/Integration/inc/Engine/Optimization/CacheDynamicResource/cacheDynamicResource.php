<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\CacheDynamicResource;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\CacheDynamicResource::cache_dynamic_resource
 * @group  CacheDynamicResource
 */
class Test_CacheDynamicResource extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/CacheDynamicResource/cacheDynamicResource.php';
	protected $cnames;
	protected $zones;
	private $isCSSTestData;
	private $minify_type;

	public function setUp() {
		parent::setUp();

		$this->isCSSTestData = false;
		$this->minify_type   = '';
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( "pre_get_rocket_option_minify_{$this->minify_type}_key", [ $this, 'getMinifyKey' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCacheDynamicResource( $event, $src, $expected, $settings ) {
		// Set up the test.
		$this->isCSSTestData = ( 'style_loader_src' === $event );
		$this->minify_type   = $this->isCSSTestData ? 'css' : 'js';
		add_filter( "pre_get_rocket_option_minify_{$this->minify_type}_key", [ $this, 'getMinifyKey' ] );

		$this->set_settings( $settings );

		// Enqueue the resource.
		if ( $this->isCSSTestData ) {
			wp_enqueue_style( $src, $src );
		} else {
			wp_enqueue_script( $src, $src );
		}

		// Apply the filter event. Check the result.
		$this->assertSame( $expected, apply_filters( $event, $src, '' ) );

		// Clean up.
		if ( $this->isCSSTestData ) {
			wp_dequeue_style( $src );
		} else {
			wp_dequeue_script( $src );
		}

		$this->unset_settings( $settings );
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
}

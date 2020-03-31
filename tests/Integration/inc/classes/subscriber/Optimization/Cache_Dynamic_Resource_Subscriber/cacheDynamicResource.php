<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Optimization\Cache_Dynamic_Resource_Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Cache_Dynamic_Resource_Subscriber::cache_dynamic_resource
 * @group  CacheDynamicResource
 */
class Test_CacheDynamicResource extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/classes/subscriber/Optimization/Cache_Dynamic_Resource_Subscriber/cacheDynamicResource.php';
	private $cdnCname = 'https://123456.rocketcdn.me';
	private $cdnZone;
	private $isCDNTestData;
	private $isCSSTestData;
	private $minify_key;

	public function setUp() {
		parent::setUp();

		$this->isCSSTestData = false;
		$this->isCDNTestData = false;
		$this->cdnZone       = '';
		$this->minify_key    = '';
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( "pre_get_rocket_option_minify_{$this->minify_key}_key", [ $this, 'getMinifyKey' ] );

		if ( $this->isCDNTestData ) {
			remove_filter( 'pre_get_rocket_option_cdn', [ $this, '__return_true' ] );
			remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'getCDNCnames' ] );
			remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'getCDNZone' ] );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCacheDynamicResource( $event, $src, $expected ) {
		// Set up the test.
		$this->isCSSTestData = ( 'style_loader_src' === $event );
		$this->isCDNTestData = ( substr( $expected, 0, strlen( $this->cdnCname ) ) === $this->cdnCname );
		$this->minify_key    = $this->isCSSTestData ? 'css' : 'js';
		add_filter( "pre_get_rocket_option_minify_{$this->minify_key}_key", [ $this, 'getMinifyKey' ] );
		if ( $this->isCDNTestData ) {
			$this->cdnZone = $this->minify_key;
			add_filter( 'pre_get_rocket_option_cdn', [ $this, '__return_true' ] );
			add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'getCDNCnames' ] );
			add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'getCDNZone' ] );
		}

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
	}

	public function getMinifyKey() {
		return '123456';
	}

	public function getCDNCnames() {
		return [ $this->cdnCname ];
	}

	public function getCDNZone() {
		return [ $this->cdnZone ];
	}

	public function __return_true() {
		return true;
	}
}

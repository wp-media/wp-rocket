<?php

namespace WP_Rocket\Tests\Integration\Addon\WebP\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Addon\WebP\Subscriber::convert_to_webp
 * @group WebP
 */
class Test_ConvertToWebp extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Addon/WebP/Subscriber/convertToWebp.php';

	private $cache_webp;
	private $disable_webp_cache;
	private $extensions_webp;
	private $attributes_webp;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'convert_to_webp', 16 );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cache_webp', [ $this, 'set_cache_webp' ] );
		remove_filter( 'rocket_disable_webp_cache', [ $this, 'disable_webp_cache'] );
		remove_filter( 'rocket_file_extensions_for_webp', [ $this, 'set_extensions_webp'] );
		remove_filter( 'rocket_attributes_for_webp', [ $this, 'set_attributes_webp' ] );
		remove_filter( 'rocket_cdn_cnames', [ $this, 'set_cdn_cnames'] );

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $original, $expected ) {
		$this->cache_webp = $config['webp'];
		$this->disable_webp_cache = $config['filter_disable'];
		$this->extensions_webp = $config['filter_ext'];
		$this->attributes_webp = $config['filter_attr'];

		add_filter( 'pre_get_rocket_option_cache_webp', [ $this, 'set_cache_webp' ] );
		add_filter( 'rocket_disable_webp_cache', [ $this, 'disable_webp_cache'] );
		add_filter( 'rocket_file_extensions_for_webp', [ $this, 'set_extensions_webp'] );
		add_filter( 'rocket_attributes_for_webp', [ $this, 'set_attributes_webp' ] );
		add_filter( 'rocket_cdn_cnames', [ $this, 'set_cdn_cnames'] );

		Functions\when( 'apache_request_headers' )
			->alias( function() use ( $config ) {
				return [
					'Accept' => $config['headers'],
				];
			}
		);

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $original )
		);
	}

	public function set_cache_webp() {
		return $this->cache_webp;
	}

	public function disable_webp_cache() {
		return $this->disable_webp_cache;
	}

	public function set_extensions_webp() {
		return $this->extensions_webp;
	}

	public function set_attributes_webp() {
		return $this->attributes_webp;
	}

	public function set_cdn_cnames() {
		return [ 'cdn-example.net' ];
	}
}

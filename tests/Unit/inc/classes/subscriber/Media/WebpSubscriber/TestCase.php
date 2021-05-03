<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Media\WebpSubscriber;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface;
use WPMedia\PHPUnit\Unit\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {

	/**
	 * Get the mocks required by Webp_Subscriber’s constructor.
	 *
	 * @param int   $cache_webp_option_value Value to return for $mocks['optionsData']->get( 'cache_webp' ).
	 * @param array $cdn_hosts               An array of URL hosts.
	 *
	 * @return array An array containing the mocks.
	 */
	protected function getConstructorMocks( $cache_webp_option_value = 1, $cdn_hosts = [ 'cdn-example.net' ] ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = [
			'optionsData' => Mockery::mock( Options_Data::class ),
			'optionsApi'  => Mockery::mock( Options::class ),
			'cdn'         => Mockery::mock( Subscriber::class ),
			'beacon'      => Mockery::mock( Beacon::class ),
		];

		$mocks['optionsData']
			->shouldReceive( 'get' )
			->with( 'cache_webp' )
			->andReturn( $cache_webp_option_value );

		$mocks['cdn']
			->shouldReceive( 'get_cdn_hosts' )
			->with( [], [ 'all', 'images' ] )
			->andReturn( $cdn_hosts );

		$mocks['beacon']
			->shouldReceive( 'get_suggest' )
			->with( 'webp' )
			->andReturn(
				[
					'id'  => 'some-random-id',
					'url' => 'https://docs.wp-rocket.me/some/request-uri/part',
				]
			);

		return $mocks;
	}

	/**
	 * Get a mock for a webp plugin.
	 *
	 * @param bool $convert_to_webp                True if converting to webp.
	 * @param bool $serve_webp                     True if serving webp.
	 * @param bool $serve_webp_compatible_with_cdn True if serving webp in a way compatible with CDN.
	 *
	 * @return object
	 */
	protected function getWebpPluginMock( $convert_to_webp = false, $serve_webp = false, $serve_webp_compatible_with_cdn = false ) {
		$webpPluginMock = Mockery::mock( Webp_Interface::class );
		$webpPluginMock
			->shouldReceive( 'get_name' )
			->andReturn( 'Mock' );
		$webpPluginMock
			->shouldReceive( 'get_id' )
			->andReturn( 'mock' );
		$webpPluginMock
			->shouldReceive( 'is_converting_to_webp' )
			->andReturn( $convert_to_webp );
		$webpPluginMock
			->shouldReceive( 'is_serving_webp' )
			->andReturn( $serve_webp );
		$webpPluginMock
			->shouldReceive( 'is_serving_webp_compatible_with_cdn' )
			->andReturn( $serve_webp_compatible_with_cdn );
		$webpPluginMock
			->shouldReceive( 'get_basename' )
			->andReturn( 'mock/mock.php' );

		return $webpPluginMock;
	}
}

<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Media\WebpSubscriber;

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
			'optionsData' => $this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			'optionsApi'  => $this->createMock( 'WP_Rocket\Admin\Options' ),
			'cdn'         => $this->createMock( 'WP_Rocket\Engine\CDN\Subscriber' ),
			'beacon'      => $this->createMock( 'WP_Rocket\Engine\Admin\Beacon\Beacon' ),
		];

		$mocks['optionsData']
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cache_webp', '', $cache_webp_option_value ],
					]
				)
			);

		$mocks['cdn']
			->method( 'get_cdn_hosts' )
			->will(
				$this->returnValueMap(
					[
						[ [], [ 'all', 'images' ], $cdn_hosts ],
					]
				)
			);

		$mocks['beacon']
			->method( 'get_suggest' )
			->will(
				$this->returnValueMap(
					[
						[
							'webp',
							[
								'id'  => 'some-random-id',
								'url' => 'https://docs.wp-rocket.me/some/request-uri/part',
							],
						],
					]
				)
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
		$webpPluginMock = $this->createMock( '\WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface' );
		$webpPluginMock
			->method( 'get_name' )
			->willReturn( 'Mock' );
		$webpPluginMock
			->method( 'get_id' )
			->willReturn( 'mock' );
		$webpPluginMock
			->method( 'is_converting_to_webp' )
			->willReturn( $convert_to_webp );
		$webpPluginMock
			->method( 'is_serving_webp' )
			->willReturn( $serve_webp );
		$webpPluginMock
			->method( 'is_serving_webp_compatible_with_cdn' )
			->willReturn( $serve_webp_compatible_with_cdn );
		$webpPluginMock
			->method( 'get_basename' )
			->willReturn( 'mock/mock.php' );

		return $webpPluginMock;
	}
}

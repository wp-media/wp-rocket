<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Media\WebpSubscriber;

use WP_Rocket\Subscriber\Media\Webp_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class TestMaybeDisableWebpCache extends TestCase {
	/**
	 * Test Webp_Subscriber->maybe_disable_webp_cache() should return true when webp cache is already disabled.
	 */
	public function testShouldReturnTrueWhenWebpCacheIsAlreadyEnabled() {
		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'] );

		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( true ) );
		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( 1 ) );
		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( 'foo' ) );
	}

	/**
	 * Test Webp_Subscriber->maybe_disable_webp_cache() should return false when webp cache is not already disabled and no webp plugins are enabled.
	 */
	public function testShouldReturnFalseWhenWebpCacheNotAlreadyEnabledAndNoWebpPluginEnabled() {
		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );

		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'] );

		$this->assertFalse( $webpSubscriber->maybe_disable_webp_cache( false ) );

		$webpPluginMock = $this->getWebpPluginMock( true, false, false ); // Not serving webp.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$this->assertFalse( $webpSubscriber->maybe_disable_webp_cache( false ) );
	}

	/**
	 * Test Webp_Subscriber->maybe_disable_webp_cache() should return true when a webp plugin is enabled.
	 */
	public function testShouldReturnTrueWhenWebpPluginEnabled() {
		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );

		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'] );

		$webpPluginMock = $this->getWebpPluginMock( true, true, true ); // Serving webp.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( true ) );
		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( false ) );
	}

	/**
	 * Get the 3 mocks required by Webp_Subscriber’s constructor.
	 *
	 * @since  3.4
	 * @author Grégory Viguier
	 * @access private
	 *
	 * @param  int   $cache_webp_option_value Value to return for $mocks['optionsData']->get( 'cache_webp' ).
	 * @param  array $cdn_hosts               An array of URL hosts.
	 * @return array An array containing the 3 mocks.
	 */
	private function getConstructorMocks( $cache_webp_option_value = 1, $cdn_hosts = [ 'cdn-example.net' ] ) {
		// Mock the 3 required objets for Webp_Subscriber.
		$mocks = [
			'optionsData' => $this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			'optionsApi'  => $this->createMock( 'WP_Rocket\Admin\Options' ),
			'cdn'         => $this->createMock( 'WP_Rocket\Subscriber\CDN\CDNSubscriber' ),
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

		return $mocks;
	}

	/**
	 * Get a mock for a webp plugin.
	 *
	 * @since  3.4
	 * @author Grégory Viguier
	 * @access private
	 *
	 * @param  bool  $convert_to_webp                True if converting to webp.
	 * @param  bool  $serve_webp                     True if serving webp.
	 * @param  bool  $serve_webp_compatible_with_cdn True if serving webp in a way compatible with CDN.
	 * @return object
	 */
	private function getWebpPluginMock( $convert_to_webp = false, $serve_webp = false, $serve_webp_compatible_with_cdn = false ) {
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

<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Media\WebpSubscriber;

use WP_Rocket\Subscriber\Media\Webp_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @group Subscriber
 */
class TestSyncWebpCacheWithThirdPartyPlugins extends TestCase {
	/**
	 * Test Webp_Subscriber->sync_webp_cache_with_third_party_plugins() should not disable the webp cache option when not enabled.
	 */
	public function testShouldNotDisableWebpCacheOptionWhenNotEnabled() {
		Functions\when( 'rocket_generate_config_file' )->justReturn( null );

		$mocks = $this->getConstructorMocks( 0 ); // Cache option disabled.

		$mocks['optionsApi']
			->expects( $this->never() )
			->method( 'set' );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();
	}

	/**
	 * Test Webp_Subscriber->sync_webp_cache_with_third_party_plugins() should not disable the webp cache option when the webp cache option is enabled but no webp plugins are serving webp.
	 */
	public function testShouldNotDisableWebpCacheOptionWhenCacheOptionEnabledAndNoWebpPluginServingWebp() {
		Functions\when( 'rocket_generate_config_file' )->justReturn( null );
		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );

		/**
		 * Cache option enabled, CDN enabled. No third party plugins.
		 */
		$mocks = $this->getConstructorMocks(); // Cache option enabled, CDN enabled.

		$mocks['optionsApi']
			->expects( $this->never() )
			->method( 'set' );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();

		/**
		 * Cache option enabled, CDN enabled. Third party plugin not creating nor serving webp.
		 */
		$webpPluginMock = $this->getWebpPluginMock( false, false, false ); // Not creating nor serving webp.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();

		/**
		 * Cache option enabled, CDN enabled. Third party plugin creating but not serving webp.
		 */
		$webpPluginMock = $this->getWebpPluginMock( true, false, false ); // Creating but still not serving webp.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();

		/**
		 * Cache option enabled, CDN enabled. Third party plugin creating and serving webp, but not compatible with CDN.
		 */
		$webpPluginMock = $this->getWebpPluginMock( true, true, false ); // Creating and serving webp, but not compatible with CDN.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();
	}

	/**
	 * Test Webp_Subscriber->sync_webp_cache_with_third_party_plugins() should disable the webp cache option when the webp cache option is enabled and a webp plugin is serving webp.
	 */
	public function testShouldDisableWebpCacheOptionWhenCacheOptionEnabledAndWebpPluginServingWebp() {
		Functions\when( 'rocket_generate_config_file' )->justReturn( null );
		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );

		/**
		 * Cache option enabled, CDN enabled. Third party plugin not creating but serving webp, compatible with CDN.
		 */
		$mocks = $this->getConstructorMocks(); // Cache option enabled, CDN enabled.

		$mocks['optionsApi']
			->expects( $this->exactly( 2 ) )
			->method( 'set' );

		$webpPluginMock = $this->getWebpPluginMock( false, true, true ); // Not creating but serving webp, compatible with CDN.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();

		/**
		 * Cache option enabled, CDN enabled. Third party plugin creating and serving webp, compatible with CDN.
		 */
		$webpPluginMock = $this->getWebpPluginMock( true, true, true ); // Creating and serving webp, compatible with CDN.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();

		/**
		 * Cache option enabled, CDN disabled. Third party plugin creating and serving webp, but not compatible with CDN.
		 */
		$mocks = $this->getConstructorMocks( 1, [] ); // Cache option enabled, no CDN.

		$mocks['optionsApi']
			->expects( $this->once() )
			->method( 'set' );

		$webpPluginMock = $this->getWebpPluginMock( true, true, false ); // Creating and serving webp, but not compatible with CDN.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();
	}

	/**
	 * Get the mocks required by Webp_Subscriber’s constructor.
	 *
	 * @since  3.4
	 * @author Grégory Viguier
	 * @access private
	 *
	 * @param  int   $cache_webp_option_value Value to return for $mocks['optionsData']->get( 'cache_webp' ).
	 * @param  array $cdn_hosts               An array of URL hosts.
	 * @return array An array containing the mocks.
	 */
	private function getConstructorMocks( $cache_webp_option_value = 1, $cdn_hosts = [ 'cdn-example.net' ] ) {
		// Mock the required objets for Webp_Subscriber.
		$mocks = [
			'optionsData' => $this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			'optionsApi'  => $this->createMock( 'WP_Rocket\Admin\Options' ),
			'cdn'         => $this->createMock( 'WP_Rocket\Subscriber\CDN\CDNSubscriber' ),
			'beacon'      => $this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
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
						[ 'webp', [
							'id'  => 'some-random-id',
							'url' => 'https://docs.wp-rocket.me/some/request-uri/part',
						] ],
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

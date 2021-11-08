<?php
namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Media\WebpSubscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Media\Webp_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Media\Webp_Subscriber::sync_webp_cache_with_third_party_plugins
 *
 * @group WebP
 */
class Test_SyncWebpCacheWithThirdPartyPlugins extends TestCase {

	public function testShouldNotDisableWebpCacheOptionWhenNotEnabled() {
		Functions\when( 'rocket_generate_config_file' )->justReturn( null );

		$mocks = $this->getConstructorMocks( 0 ); // Cache option disabled.

		$mocks['optionsApi']
			->shouldReceive( 'set' )
			->never();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();
	}

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
			->shouldReceive( 'set' )
			->never();

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
			->shouldReceive( 'set' )
			->with( 'settings', [
				'cache_webp' => 0
			] );

		$mocks['optionsData']
			->shouldReceive( 'set' )
			->with( 'cache_webp', 0 );
		
		$mocks['optionsData']
			->shouldReceive( 'get_options' )
			->andReturn( [
				'cache_webp' => 0
			] );

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
			->shouldReceive( 'set' )
			->with( 'settings', [
				'cache_webp' => 0
			] );

		$mocks['optionsData']
			->shouldReceive( 'set' )
			->with( 'cache_webp', 0 );

		$mocks['optionsData']
			->shouldReceive( 'get_options' )
			->andReturn( [
				'cache_webp' => 0
			] );

		$webpPluginMock = $this->getWebpPluginMock( true, true, false ); // Creating and serving webp, but not compatible with CDN.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$webpSubscriber->sync_webp_cache_with_third_party_plugins();
	}
}

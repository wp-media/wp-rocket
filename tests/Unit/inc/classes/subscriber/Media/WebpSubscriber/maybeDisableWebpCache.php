<?php
namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Media\WebpSubscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Media\Webp_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Media\Webp_Subscriber::maybe_disable_webp_cache
 * @group Subscriber
 */
class Test_MaybeDisableWebpCache extends TestCase {

	public function testShouldReturnTrueWhenWebpCacheIsAlreadyEnabled() {
		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( true ) );
		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( 1 ) );
		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( 'foo' ) );
	}

	public function testShouldReturnFalseWhenWebpCacheNotAlreadyEnabledAndNoWebpPluginEnabled() {
		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );

		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertFalse( $webpSubscriber->maybe_disable_webp_cache( false ) );

		$webpPluginMock = $this->getWebpPluginMock( true, false, false ); // Not serving webp.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$this->assertFalse( $webpSubscriber->maybe_disable_webp_cache( false ) );
	}

	public function testShouldReturnTrueWhenWebpPluginEnabled() {
		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );

		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$webpPluginMock = $this->getWebpPluginMock( true, true, true ); // Serving webp.

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( true ) );
		$this->assertTrue( $webpSubscriber->maybe_disable_webp_cache( false ) );
	}
}

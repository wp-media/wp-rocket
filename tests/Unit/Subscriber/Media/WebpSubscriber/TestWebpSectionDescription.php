<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Media\WebpSubscriber;

use WP_Rocket\Subscriber\Media\Webp_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class TestWebpSectionDescription extends TestCase {
	/**
	 * Test Webp_Subscriber->webp_section_description() should return specific text when no webp plugin is available and cache option is disabled.
	 */
	public function testShouldReturnTextWhenNoPluginsAndCacheOptionDisabled() {
		$expectedText = 'If you activate this option, WP Rocket will create separate cache files to serve WebP images.';

		$this->mockCommonWpFunctions();

		// Webp cache option disabled.
		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertTrue( strpos( $webpSubscriber->webp_section_description( '' ), $expectedText ) === 0 );
	}

	/**
	 * Test Webp_Subscriber->webp_section_description() should return specific text when no webp plugin is available and cache option is enabled.
	 */
	public function testShouldReturnTextWhenNoPluginsAndCacheOptionEnabled() {
		$expectedText = 'WP Rocket will create separate cache files to serve WebP images.';

		$this->mockCommonWpFunctions();

		// Webp cache option enabled.
		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertTrue( strpos( $webpSubscriber->webp_section_description( '' ), $expectedText ) === 0 );
	}

	/**
	 * Test Webp_Subscriber->webp_section_description() should return specific text when a plugin creating webp (but not serving) is available and cache option is disabled.
	 */
	public function testShouldReturnTextWhenPluginCreatingWebpAvailableAndCacheOptionDisabled() {
		$expectedText = 'You are using Mock to convert images to WebP. If you activate this option,';

		$this->mockCommonWpFunctions();

		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );
		Functions\when( 'wp_sprintf_l' )->alias( function( $pattern, $args ) {
			return implode( ', ', $args );
		} );

		$webpPluginMock = $this->createMock( '\WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface' );
		$webpPluginMock
			->method( 'get_name' )
			->willReturn( 'Mock' );
		$webpPluginMock
			->method( 'get_id' )
			->willReturn( 'mock' );
		$webpPluginMock
			->method( 'is_converting_to_webp' )
			->willReturn( true );
		$webpPluginMock
			->method( 'is_serving_webp' )
			->willReturn( false );
		$webpPluginMock
			->method( 'is_serving_webp_compatible_with_cdn' )
			->willReturn( false );
		$webpPluginMock
			->method( 'get_basename' )
			->willReturn( 'mock/mock.php' );

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		// Webp cache option disabled.
		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertTrue( strpos( $webpSubscriber->webp_section_description( '' ), $expectedText ) === 0 );
	}

	/**
	 * Test Webp_Subscriber->webp_section_description() should return specific text when a plugin creating webp (but not serving) is available and cache option is enabled.
	 */
	public function testShouldReturnTextWhenPluginCreatingWebpAvailableAndCacheOptionEnabled() {
		$expectedText = 'You are using Mock to convert images to WebP. WP Rocket will create';

		$this->mockCommonWpFunctions();

		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );
		Functions\when( 'wp_sprintf_l' )->alias( function( $pattern, $args ) {
			return implode( ', ', $args );
		} );

		$webpPluginMock = $this->createMock( '\WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface' );
		$webpPluginMock
			->method( 'get_name' )
			->willReturn( 'Mock' );
		$webpPluginMock
			->method( 'get_id' )
			->willReturn( 'mock' );
		$webpPluginMock
			->method( 'is_converting_to_webp' )
			->willReturn( true );
		$webpPluginMock
			->method( 'is_serving_webp' )
			->willReturn( false );
		$webpPluginMock
			->method( 'is_serving_webp_compatible_with_cdn' )
			->willReturn( false );
		$webpPluginMock
			->method( 'get_basename' )
			->willReturn( 'mock/mock.php' );

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		// Webp cache option enabled.
		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertTrue( strpos( $webpSubscriber->webp_section_description( '' ), $expectedText ) === 0 );
	}

	/**
	 * Test Webp_Subscriber->webp_section_description() should return specific text when a plugin serving webp is available.
	 */
	public function testShouldReturnTextWhenPluginServingWebpAvailable() {
		$expectedText = 'You are using Mock to serve images as WebP.';

		$this->mockCommonWpFunctions();

		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );
		Functions\when( 'wp_sprintf_l' )->alias( function( $pattern, $args ) {
			return implode( ', ', $args );
		} );

		$webpPluginMock = $this->createMock( '\WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface' );
		$webpPluginMock
			->method( 'get_name' )
			->willReturn( 'Mock' );
		$webpPluginMock
			->method( 'get_id' )
			->willReturn( 'mock' );
		$webpPluginMock
			->method( 'is_converting_to_webp' )
			->willReturn( true );
		$webpPluginMock
			->method( 'is_serving_webp' )
			->willReturn( true );
		$webpPluginMock
			->method( 'is_serving_webp_compatible_with_cdn' )
			->willReturn( true );
		$webpPluginMock
			->method( 'get_basename' )
			->willReturn( 'mock/mock.php' );

		Filters\expectApplied( 'rocket_webp_plugins' )
			->twice()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		// Webp cache option disabled.
		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertTrue( strpos( $webpSubscriber->webp_section_description( '' ), $expectedText ) === 0 );

		// Webp cache option enabled.
		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertTrue( strpos( $webpSubscriber->webp_section_description( '' ), $expectedText ) === 0 );
	}

	/**
	 * Test Webp_Subscriber->webp_section_description() should return specific text when cache option is disabled by filter.
	 */
	public function testShouldReturnTextWhenCacheOptionDisabledByFilter() {
		$expectedText = 'WebP cache is disabled by filter.';

		$this->mockCommonWpFunctions();

		Functions\when( 'rocket_is_plugin_active' )->alias( function( $plugin ) {
			return 'mock/mock.php' === $plugin;
		} );
		Functions\when( 'get_rocket_option' )->alias( function( $option, $default = '' ) {
			return 'cdn' === $option;
		} );
		Functions\when( 'wp_sprintf_l' )->alias( function( $pattern, $args ) {
			return implode( ', ', $args );
		} );

		Filters\expectApplied( 'rocket_disable_webp_cache' )
			->times( 4 )
			->andReturn( true ); // Simulate a filter.

		// Not generating nor serving webp.
		$webpPluginMock = $this->createMock( '\WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface' );
		$webpPluginMock
			->method( 'get_name' )
			->willReturn( 'Mock' );
		$webpPluginMock
			->method( 'get_id' )
			->willReturn( 'mock' );
		$webpPluginMock
			->method( 'is_converting_to_webp' )
			->willReturn( false );
		$webpPluginMock
			->method( 'is_serving_webp' )
			->willReturn( false );
		$webpPluginMock
			->method( 'is_serving_webp_compatible_with_cdn' )
			->willReturn( false );
		$webpPluginMock
			->method( 'get_basename' )
			->willReturn( 'mock/mock.php' );

		Filters\expectApplied( 'rocket_webp_plugins' )
			->times( 2 )
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		// Webp cache option disabled.
		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriberNoCache = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertSame( $expectedText, $webpSubscriberNoCache->webp_section_description( '' ) );

		// Webp cache option enabled.
		$mocks = $this->getConstructorMocks( 1 );

		$webpSubscriberWithCache = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );

		$this->assertSame( $expectedText, $webpSubscriberWithCache->webp_section_description( '' ) );

		// Generating but not serving webp.
		$webpPluginMock
			->method( 'is_converting_to_webp' )
			->willReturn( true );

		Filters\expectApplied( 'rocket_webp_plugins' )
			->times( 2 )
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		// Webp cache option disabled.
		$this->assertSame( $expectedText, $webpSubscriberNoCache->webp_section_description( '' ) );

		// Webp cache option enabled.
		$this->assertSame( $expectedText, $webpSubscriberWithCache->webp_section_description( '' ) );
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
}

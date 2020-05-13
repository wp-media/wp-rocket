<?php
namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Media\WebpSubscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Media\Webp_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Media\Webp_Subscriber::webp_section_description
 * @group Subscriber
 */
class Test_WebpSectionDescription extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	public function testShouldReturnTextWhenNoPluginsAndCacheOptionDisabled() {
		Functions\when( 'rocket_valid_key' )->justReturn( false );

		// Webp cache option disabled.
		$mocks = $this->getConstructorMocks( 0 );

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );
		$field          = $webpSubscriber->webp_section_description( [] );

		// Text under field.
		$expectedText = 'You don’t seem to be using a method to create and serve WebP that we are auto-compatible with.';

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertStringStartsWith( $expectedText, $field['helper'] );

		// Double check.
		$expectedText = 'If you activate this option WP Rocket will create separate cache files to serve WebP images.';

		$this->assertArrayHasKey( 'warning', $field );
		$this->assertStringStartsWith( $expectedText, $field['warning']['description'] );
	}

	public function testShouldReturnTextWhenNoPluginsAndCacheOptionEnabled() {
		$expectedText = 'WP Rocket will create separate cache files to serve your WebP images.';

		// Webp cache option enabled.
		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );
		$field          = $webpSubscriber->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertSame( $expectedText, $field['helper'] );
	}

	public function testShouldReturnTextWhenPluginCreatingWebpAvailableAndCacheOptionDisabled() {
		$expectedText = 'You are using Mock to convert images to WebP. If you want WP Rocket to serve them for you,';

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
		$field          = $webpSubscriber->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertStringStartsWith( $expectedText, $field['helper'] );
	}

	public function testShouldReturnTextWhenPluginCreatingWebpAvailableAndCacheOptionEnabled() {
		$expectedText = 'You are using Mock to convert images to WebP. WP Rocket will create separate cache files';

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
		$field          = $webpSubscriber->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertStringStartsWith( $expectedText, $field['helper'] );
	}

	public function testShouldReturnTextWhenPluginServingWebpNotCompatibleAndCacheOptionDisabled() {
		$expectedText = 'You are using Mock to convert images to WebP. If you want WP Rocket to serve them for you,';

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
		$field          = $webpSubscriber->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertStringStartsWith( $expectedText, $field['helper'] );
	}

	public function testShouldReturnTextWhenPluginServingWebpNotCompatibleAndCacheOptionEnabled() {
		$expectedText = 'You are using Mock to convert images to WebP. WP Rocket will create separate cache files';

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
		$field          = $webpSubscriber->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertStringStartsWith( $expectedText, $field['helper'] );
	}

	public function testShouldReturnTextWhenPluginServingWebpAvailable() {
		$expectedText = 'You are using Mock to serve WebP images so you do not need to enable this option.';

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
		$field          = $webpSubscriber->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertStringStartsWith( $expectedText, $field['helper'] );

		// Webp cache option enabled.
		$mocks = $this->getConstructorMocks();

		$webpSubscriber = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );
		$field          = $webpSubscriber->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertStringStartsWith( $expectedText, $field['helper'] );
	}

	public function testShouldReturnTextWhenCacheOptionDisabledByFilter() {
		$expectedText = 'WebP cache is disabled by filter.';

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
		$field                 = $webpSubscriberNoCache->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertSame( $expectedText, $field['helper'] );

		// Webp cache option enabled.
		$mocks = $this->getConstructorMocks( 1 );

		$webpSubscriberWithCache = new Webp_Subscriber( $mocks['optionsData'], $mocks['optionsApi'], $mocks['cdn'], $mocks['beacon'] );
		$field                   = $webpSubscriberWithCache->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertSame( $expectedText, $field['helper'] );

		// Generating but not serving webp.
		$webpPluginMock
			->method( 'is_converting_to_webp' )
			->willReturn( true );

		Filters\expectApplied( 'rocket_webp_plugins' )
			->times( 2 )
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		// Webp cache option disabled.
		$field = $webpSubscriberNoCache->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertSame( $expectedText, $field['helper'] );

		// Webp cache option enabled.
		$field = $webpSubscriberWithCache->webp_section_description( [] );

		$this->assertArrayHasKey( 'helper', $field );
		$this->assertSame( $expectedText, $field['helper'] );
	}
}

<?php
namespace WP_Rocket\Tests\Unit\inc\Addon\WebP\Subscriber;

use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Subscriber as CDNSubscriber;
use WP_Rocket\Addon\WebP\Subscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Addon\WebP\Subscriber::sync_webp_cache_with_third_party_plugins
 *
 * @group WebP
 */
class Test_SyncWebpCacheWithThirdPartyPlugins extends TestCase {
	private $subscriber;
	private $options_api;
	private $options;

	public function setUp(): void {
		parent::setUp();

		$this->options_api = Mockery::mock( Options::class );
		$this->options = Mockery::mock( Options_Data::class );

		$this->subscriber = new Subscriber(
			$this->options,
			$this->options_api,
			Mockery::mock( CDNSubscriber::class ),
			''
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $option, $serving, $expected ) {
		$this->options
			->shouldReceive( 'get' )
			->with( 'cache_webp', 0 )
			->once()
			->andReturn( $option );

		if ( ! $serving ) {
			$webpPluginMock = $this->getWebpPluginMock( true, false, false );
		} else {
			$webpPluginMock = $this->getWebpPluginMock( true, true, false );
		}

		Filters\expectApplied( 'rocket_webp_plugins' )
			->atMost()
			->once()
			->andReturn( [ $webpPluginMock ] );

		Functions\expect( 'rocket_is_plugin_active' )
			->with( 'mock/mock.php' )
			->andReturn( true );

		Functions\expect( 'get_rocket_option' )
			->with( 'cdn', 0 )
			->andReturn( false );

		if ( ! $expected ) {
			$this->options
				->shouldReceive( 'set' )
				->never();

			$this->options
				->shouldReceive( 'get_options' )
				->never();

			$this->options_api
				->shouldReceive( 'set' )
				->never();
		} else {
			$this->options
				->shouldReceive( 'set' )
				->once()
				->with( 'cache_webp', 0 );

			$this->options
				->shouldReceive( 'get_options' )
				->once()
				->andReturn( [ 'cache_webp' => 0 ] );

			$this->options_api
				->shouldReceive( 'set' )
				->once()
				->with( 'settings', [ 'cache_webp' => 0 ] );
		}

		Functions\expect( 'rocket_generate_config_file' )
			->once();

		$this->subscriber->sync_webp_cache_with_third_party_plugins();
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

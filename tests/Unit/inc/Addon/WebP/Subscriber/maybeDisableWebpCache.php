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
 * @covers \WP_Rocket\Addon\WebP\Subscriber::maybe_disable_webp_cache
 *
 * @group WebP
 */
class Test_MaybeDisableWebpCache extends TestCase {
	private $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->subscriber = new Subscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Options::class ),
			Mockery::mock( CDNSubscriber::class ),
			''
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $value, $expected ) {
		Functions\expect( 'rocket_is_plugin_active' )
			->with( 'mock/mock.php' )
			->andReturn( $config['plugin_active'] );

		Functions\expect( 'get_rocket_option' )
			->with( 'cdn', 0 )
			->andReturn( $config['cdn'] );

		$webpPluginMock = $this->getWebpPluginMock( $config['convert_to_webp'], $config['serve_webp'], $config['serve_webp_compatible_with_cdn'] );

		Filters\expectApplied( 'rocket_webp_plugins' )
			->atMost()
			->once()
			->andReturn( [ $webpPluginMock ] );

		$this->assertSame(
			$expected,
			$this->subscriber->maybe_disable_webp_cache( $value )
		);
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

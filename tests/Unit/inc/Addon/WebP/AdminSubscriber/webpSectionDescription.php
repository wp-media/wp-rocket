<?php
namespace WP_Rocket\Tests\Unit\inc\Addon\Webp\AdminSubscriber;

use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Addon\Webp\AdminSubscriber;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface;

/**
 * @covers \WP_Rocket\Addon\Webp\AdminSubscriber::webp_section_description
 * @group WebP
 */
class Test_WebpSectionDescription extends TestCase {
	private $subscriber;
	private $options;
	private $beacon;

	public function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();

		$this->options = Mockery::mock( Options_Data::class );
		$this->beacon = Mockery::mock( Beacon::class );
		$this->subscriber = new AdminSubscriber(
			$this->options,
			Mockery::mock( Subscriber::class ),
			$this->beacon
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->beacon
			->shouldReceive( 'get_suggest' )
			->with( 'webp' )
			->once()
			->andReturn(
				[
					'id' => 123,
					'url' => '',
				]
			);

		Functions\when( 'rocket_valid_key' )->justReturn( false );

		Functions\expect( 'rocket_is_plugin_active' )
			->andReturn( true );

		Functions\expect( 'get_rocket_option' )
			->andReturn( $config['cdn'] );

		Functions\when( 'wp_sprintf_l' )->alias( function( $pattern, $args ) {
				return implode( ', ', $args );
			} );

		$this->options
			->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'cache_webp', 0 )
			->andReturn( $config['webp'] );

		Filters\expectApplied( 'rocket_disable_webp_cache' )
			->andReturn( $config['filter'] );

		$webpPluginMock = Mockery::mock( Webp_Interface::class );
		$webpPluginMock
			->shouldReceive( 'get_name' )
			->andReturn( 'Mock' );
		$webpPluginMock
			->shouldReceive( 'get_id' )
			->andReturn( 'mock' );
		$webpPluginMock
			->shouldReceive( 'is_converting_to_webp' )
			->andReturn( $config['convert_webp'] );
		$webpPluginMock
			->shouldReceive( 'is_serving_webp' )
			->andReturn( $config['serving_webp'] );
		$webpPluginMock
			->shouldReceive( 'is_serving_webp_compatible_with_cdn' )
			->andReturn( $config['serving_webp_cdn'] );
		$webpPluginMock
			->shouldReceive( 'get_basename' )
			->andReturn( 'mock/mock.php' );

		Filters\expectApplied( 'rocket_webp_plugins' )
			->once()
			->andReturn( [ $webpPluginMock ] ); // Simulate a filter.

		$field = $this->subscriber->webp_section_description( [] );

		$this->assertArrayHasKey( 'description', $field );
		$this->assertStringStartsWith( $expected, $field['description'] );
	}
}

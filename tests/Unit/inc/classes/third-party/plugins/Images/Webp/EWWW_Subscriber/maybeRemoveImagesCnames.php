<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use SebastianBergmann\Exporter\Exporter;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber::maybe_remove_images_cnames
 * @group  ThirdParty
 * @group  Webp
 */
class Test_MaybeRemoveImagesCnames extends TestCase {

	public function testShouldReturnIdenticalWhenExactdnNotEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( false );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'foo.com', 'bar.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images' ] );

		$this->assertSame( $hosts, $result );
	}

	public function testShouldReturnIdenticalWhenNoImagesZone() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->never();

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'foo.com', 'bar.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all' ] );

		$this->assertSame( $hosts, $result );
	}

	public function testShouldReturnEmptyArrayWhenOnlyImagesAllZones() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->never();

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'foo.com', 'bar.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images' ] );

		$this->assertSame( [], $result );

		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'images', 'all' ] );

		$this->assertSame( [], $result );
	}

	public function testShouldReturnIdenticalWhenNoCdnNames() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->andReturnUsing( function( $option_name, $default = '' ) {
				// `->get()` can be called any number of times, but never with 'cdn_zone' as first argument.
				if ( 'cdn_zone' !== $option_name ) {
					return $default;
				}

				$exporter = new Exporter;
				$message  = sprintf(
					'%s::%s(%s)%s',
					'WP_Rocket\Admin\Options_Data',
					'get',
					implode(
						', ',
						array_map(
							[ $exporter, 'shortenedExport' ],
							[ 'cdn_zone', $default ]
						)
					),
					' was not expected to be called.'
				);
				$this->fail( $message );
			} );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'foo.com', 'bar.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images', 'js' ] );

		$this->assertSame( $hosts, $result );
	}

	public function testShouldReturnIdenticalWhenNoImagesHosts() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cdn_cnames', [] )
			->andReturn( [ 'dns.example.com, all.example.com', 'evil.example.com' ] );

		$optionsData
			->shouldReceive( 'get' )
			->with( 'cdn_zone', [] )
			->andReturn( [ 'all', 'js' ] );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'dns.example.com', 'evil.example.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images', 'js' ] );

		$this->assertSame( $hosts, $result );
	}

	public function testShouldReturnIdenticalWhenImagesHostSameForAll() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cdn_cnames', [] )
			->andReturn( [
				'dns.example.com, all.example.com',
				'dns.example.com, images.example.com',
				'evil.example.com',
			] );

		$optionsData
			->shouldReceive( 'get' )
			->with( 'cdn_zone', [] )
			->andReturn( [ 'all', 'images', 'js' ] );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'dns.example.com', 'evil.example.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images', 'js' ] );

		$this->assertSame( $hosts, $result );
	}

	public function testShouldRemoveHostForImages() {
		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cdn_cnames', [] )
			->andReturn(
				[
					'dns.example.com, all.example.com',
					'dns.example.com, images.example.com',
					'evil.example.com',
				]
			);

		$optionsData
			->shouldReceive( 'get' )
			->with( 'cdn_zone', [] )
			->andReturn(
				[ 'all', 'images', 'js' ]
			);

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts    = [ 'dns.example.com', 'images.example.com', 'evil.example.com' ];
		$expected = [ 'dns.example.com', 'evil.example.com' ];
		$result   = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images', 'js' ] );
		sort( $expected );
		sort( $result );

		$this->assertSame( $expected, $result );

		$optionsData = Mockery::mock( Options_Data::class );
		$optionsData
			->shouldReceive( 'get' )
			->with( 'cdn_cnames', [] )
			->andReturn(
				[ 'all.example.com', 'dns.example.com, images.example.com', 'evil.example.com' ]
			);

		$optionsData
			->shouldReceive( 'get' )
			->with( 'cdn_zone', [] )
			->andReturn(
				[ 'all', 'images', 'js' ]
			);

		$subscriber = new EWWW_Subscriber( $optionsData );

		$expected = [ 'evil.example.com' ];
		$result   = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images', 'js' ] );
		sort( $expected );
		sort( $result );

		$this->assertSame( $expected, $result );
	}
}

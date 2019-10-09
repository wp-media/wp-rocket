<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\EwwwSubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use SebastianBergmann\Exporter\Exporter;

class TestMaybeRemoveImagesCnames extends TestCase {
	/**
	 * Test EWWW_Subscriber->maybe_remove_images_cnames() should return identical when not using ExactDN.
	 */
	public function testShouldReturnIdenticalWhenExactdnNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( false );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'foo.com', 'bar.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images' ] );

		$this->assertSame( $hosts, $result );
	}

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_cnames() should return identical when no 'images' value in zones list.
	 */
	public function testShouldReturnIdenticalWhenNoImagesZone() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->never() )
			->method( 'get' );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'foo.com', 'bar.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all' ] );

		$this->assertSame( $hosts, $result );
	}

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_cnames() should return an empty array when only 'all' and 'images' values are listed in zones list.
	 */
	public function testShouldReturnEmptyArrayWhenOnlyImagesAllZones() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->never() )
			->method( 'get' );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'foo.com', 'bar.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images' ] );

		$this->assertSame( [], $result );

		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'images', 'all' ] );

		$this->assertSame( [], $result );
	}

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_cnames() should return identical when no CDN names are set.
	 */
	public function testShouldReturnIdenticalWhenNoCdnNames() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->any() )
			->method( 'get' )
			->willReturnCallback( function( $option_name, $default = '' ) {
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

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_cnames() should return identical when there are no hosts for images.
	 */
	public function testShouldReturnIdenticalWhenNoImagesHosts() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->exactly( 2 ) )
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cdn_cnames', [], [ 'dns.example.com, all.example.com', 'evil.example.com' ] ],
						[ 'cdn_zone', [], [ 'all', 'js' ] ],
					]
				)
			);

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'dns.example.com', 'evil.example.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images', 'js' ] );

		$this->assertSame( $hosts, $result );
	}

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_cnames() should return identical when host for images is also used for 'all'.
	 */
	public function testShouldReturnIdenticalWhenImagesHostSameForAll() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->exactly( 2 ) )
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cdn_cnames', [], [ 'dns.example.com, all.example.com', 'dns.example.com, images.example.com', 'evil.example.com' ] ],
						[ 'cdn_zone', [], [ 'all', 'images', 'js' ] ],
					]
				)
			);

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$hosts  = [ 'dns.example.com', 'evil.example.com' ];
		$result = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images', 'js' ] );

		$this->assertSame( $hosts, $result );
	}

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_cnames() should remove hosts for images.
	 */
	public function testShouldRemoveHostForImages() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->exactly( 2 ) )
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cdn_cnames', [], [ 'dns.example.com, all.example.com', 'dns.example.com, images.example.com', 'evil.example.com' ] ],
						[ 'cdn_zone', [], [ 'all', 'images', 'js' ] ],
					]
				)
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

		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->exactly( 2 ) )
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cdn_cnames', [], [ 'all.example.com', 'dns.example.com, images.example.com', 'evil.example.com' ] ],
						[ 'cdn_zone', [], [ 'all', 'images', 'js' ] ],
					]
				)
			);

		$subscriber = new EWWW_Subscriber( $optionsData );

		$expected = [ 'evil.example.com' ];
		$result   = $subscriber->maybe_remove_images_cnames( $hosts, [ 'all', 'images', 'js' ] );
		sort( $expected );
		sort( $result );

		$this->assertSame( $expected, $result );
	}
}

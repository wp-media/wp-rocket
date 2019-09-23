<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\EwwwSubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class TestMaybeAddCdnWarning extends TestCase {
	/**
	 * Test EWWW_Subscriber->maybe_add_cdn_warning() should return identical when no CDNs are set.
	 */
	public function testShouldReturnIdenticalWhenNoCdnSet() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->once() )
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cdn', '', '' ],
					]
				)
			);

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->never();

		$subscriber = new EWWW_Subscriber( $optionsData );

		$field  = [ 'foo' => 'bar' ];
		$result = $subscriber->maybe_add_cdn_warning( $field );

		$this->assertSame( $field, $result );
	}

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_from_cdn_dropdown() should return identical when not using ExactDN.
	 */
	public function testShouldReturnIdenticalWhenExactdnNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->once() )
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cdn', '', 1 ],
					]
				)
			);

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( false );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$field  = [ 'foo' => 'bar' ];
		$result = $subscriber->maybe_add_cdn_warning( $field );

		$this->assertSame( $field, $result );
	}

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_from_cdn_dropdown() should add a warning entry when using ExactDN.
	 */
	public function testShouldAddWarningWhenExactdnIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$optionsData
			->expects( $this->once() )
			->method( 'get' )
			->will(
				$this->returnValueMap(
					[
						[ 'cdn', '', 1 ],
					]
				)
			);

		$this->mockCommonWpFunctions();

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$result = $subscriber->maybe_add_cdn_warning( [ 'foo' => 'bar' ] );

		$this->assertArrayHasKey( 'warning', $result );
	}
}

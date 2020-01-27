<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\EwwwSubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @group ThirdParty
 */
class TestMaybeRemoveImagesFromCdnDropdown extends TestCase {
	/**
	 * Test EWWW_Subscriber->maybe_remove_images_from_cdn_dropdown() should return identical when not using ExactDN.
	 */
	public function testShouldReturnIdenticalWhenExactdnNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( false );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( false ) );
		$this->assertTrue( $subscriber->maybe_remove_images_from_cdn_dropdown( true ) );
	}

	/**
	 * Test EWWW_Subscriber->maybe_remove_images_from_cdn_dropdown() should return false when using ExactDN.
	 */
	public function testShouldReturnFalseWhenExactdnIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( false ) );
		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( true ) );
	}
}

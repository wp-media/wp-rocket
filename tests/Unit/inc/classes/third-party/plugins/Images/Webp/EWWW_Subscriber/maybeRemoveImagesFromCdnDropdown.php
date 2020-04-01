<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber::::maybe_remove_images_from_cdn_dropdown
 * @group  ThirdParty
 * @group  Webp
 */
class Test_MaybeRemoveImagesFromCdnDropdown extends TestCase {

	public function testShouldReturnIdenticalWhenExactdnNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( false );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( false ) );
		$this->assertTrue( $subscriber->maybe_remove_images_from_cdn_dropdown( true ) );
	}

	public function testShouldReturnFalseWhenExactdnIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( false ) );
		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( true ) );
	}
}

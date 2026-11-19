<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\EWWW;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\EWWW;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\EWWW::maybe_remove_images_from_cdn_dropdown
 * @group  ThirdParty
 * @group  Webp
 */
class MaybeRemoveImagesFromCdnDropdownTest extends TestCase {

	public function testShouldReturnIdenticalWhenExactdnNotEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( false );

		$subscriber = new EWWW( $optionsData );

		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( false ) );
		$this->assertTrue( $subscriber->maybe_remove_images_from_cdn_dropdown( true ) );
	}

	public function testShouldReturnFalseWhenExactdnIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );

		Functions\when( 'ewww_image_optimizer_get_option' )
			->justReturn( true );

		$subscriber = new EWWW( $optionsData );

		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( false ) );
		$this->assertFalse( $subscriber->maybe_remove_images_from_cdn_dropdown( true ) );
	}
}

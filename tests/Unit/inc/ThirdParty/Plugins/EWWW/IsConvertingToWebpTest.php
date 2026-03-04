<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\EWWW;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\EWWW;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\EWWW::is_converting_to_webp
 * @group  ThirdParty
 * @group  Webp
 */
class IsConvertingToWebpTest extends TestCase {
	public function testShouldReturnFalseWhenEwwwOptionNotEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( false );

		$this->assertFalse( $subscriber->is_converting_to_webp() );
	}

	public function testShouldReturnTrueWhenEwwwOptionIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_converting_to_webp() );
	}
}

<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber::is_converting_to_webp
 * @group  ThirdParty
 * @group  Webp
 */
class Test_IsConvertingToWebp extends TestCase {

	public function testShouldReturnFalseWhenEwwwOptionNotEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( false );

		$this->assertFalse( $subscriber->is_converting_to_webp() );
	}

	public function testShouldReturnTrueWhenEwwwOptionIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_converting_to_webp() );
	}
}

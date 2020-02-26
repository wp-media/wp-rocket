<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber::is_converting_to_webp
 * @group  ThirdParty
 * @group  Webp
 */
class Test_IsConvertingToWebp extends TestCase {

	public function testShouldReturnFalseWhenEwwwOptionNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( false );

		$this->assertFalse( $subscriber->is_converting_to_webp() );
	}

	public function testShouldReturnTrueWhenEwwwOptionIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_converting_to_webp() );
	}
}

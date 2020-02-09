<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\EwwwSubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @group ThirdParty
 */
class TestIsConvertingToWebp extends TestCase {
	/**
	 * Test EWWW_Subscriber->is_converting_to_webp() should return false when EWWW option not enabled.
	 */
	public function testShouldReturnFalseWhenEwwwOptionNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( false );

		$this->assertFalse( $subscriber->is_converting_to_webp() );
	}

	/**
	 * Test EWWW_Subscriber->is_converting_to_webp() should return true when EWWW option is enabled.
	 */
	public function testShouldReturnTrueWhenEwwwOptionIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_converting_to_webp() );
	}
}

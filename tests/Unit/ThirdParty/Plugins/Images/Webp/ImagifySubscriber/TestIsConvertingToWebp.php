<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\ImagifySubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @group ThirdParty
 */
class TestIsConvertingToWebp extends TestCase {
	/**
	 * Test Imagify_Subscriber->is_converting_to_webp() should return false when Imagify option not enabled.
	 */
	public function testShouldReturnFalseWhenImagifyOptionNotEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->is_converting_to_webp() );

		Functions\expect( 'get_imagify_option' )
			->once()
			->andReturn( false );

		$this->assertFalse( $subscriber->is_converting_to_webp() );
	}

	/**
	 * Test Imagify_Subscriber->is_converting_to_webp() should return true when Imagify option is enabled.
	 */
	public function testShouldReturnTrueWhenImagifyOptionIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Functions\expect( 'get_imagify_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_converting_to_webp() );
	}
}

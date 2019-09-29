<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\ImagifySubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

class TestSyncOnOptionDelete extends TestCase {
	/**
	 * Test Imagify_Subscriber->sync_on_option_delete() should sync when serving webp.
	 */
	public function testShouldSyncWhenServingWebp() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = $this->getMockBuilder( Imagify_Subscriber::class )
			->setConstructorArgs( [ $optionsData ] )
			->setMethods( [ 'is_serving_webp' ] )
			->getMock();
		$subscriber
			->expects( $this->once() )
			->method( 'is_serving_webp' )
			->willReturn( true );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->once();

		$subscriber->store_option_value_before_delete( 'imagify_settings' );
		$subscriber->sync_on_option_delete( 'imagify_settings' );
	}

	/**
	 * Test Imagify_Subscriber->sync_on_option_delete() should not sync when not serving webp.
	 */
	public function testShouldNotSyncWhenNotServingWebp() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = $this->getMockBuilder( Imagify_Subscriber::class )
			->setConstructorArgs( [ $optionsData ] )
			->setMethods( [ 'is_serving_webp' ] )
			->getMock();
		$subscriber
			->expects( $this->once() )
			->method( 'is_serving_webp' )
			->willReturn( false );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->never();

		$subscriber->store_option_value_before_delete( 'imagify_settings' );
		$subscriber->sync_on_option_delete( 'imagify_settings' );
	}
}

<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\ImagifySubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @group ThirdParty
 */
class TestSyncOnNetworkOptionDelete extends TestCase {
	/**
	 * Test Imagify_Subscriber->sync_on_network_option_delete() should sync when on the same network and serving webp.
	 */
	public function testShouldSyncWhenSameNetworkAndServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )
			->justReturn( $network_id );

		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		$subscriber = $this->getMockBuilder( Imagify_Subscriber::class )
			->setConstructorArgs( [ $optionsData ] )
			->setMethods( [ 'is_serving_webp' ] )
			->getMock();
		$subscriber
			->expects( $this->once() )
			->method( 'is_serving_webp' )
			->willReturn( true );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->once();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}

	/**
	 * Test Imagify_Subscriber->sync_on_network_option_delete() should not sync when on the same network but not serving webp.
	 */
	public function testShouldNotSyncWhenSameNetworkAndNotServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )
			->justReturn( $network_id );

		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		$subscriber = $this->getMockBuilder( Imagify_Subscriber::class )
			->setConstructorArgs( [ $optionsData ] )
			->setMethods( [ 'is_serving_webp' ] )
			->getMock();
		$subscriber
			->expects( $this->once() )
			->method( 'is_serving_webp' )
			->willReturn( false );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->never();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}

	/**
	 * Test Imagify_Subscriber->sync_on_network_option_delete() should not sync when not on the same network but serving webp.
	 */
	public function testShouldNotSyncWhenNotSameNetworkAndServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )
			->justReturn( 2 );

		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		$subscriber = $this->getMockBuilder( Imagify_Subscriber::class )
			->setConstructorArgs( [ $optionsData ] )
			->setMethods( [ 'is_serving_webp' ] )
			->getMock();
		$subscriber
			->expects( $this->never() )
			->method( 'is_serving_webp' )
			->willReturn( true );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->never();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}
}

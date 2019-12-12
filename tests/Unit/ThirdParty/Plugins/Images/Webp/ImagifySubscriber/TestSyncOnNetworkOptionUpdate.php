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
class TestSyncOnNetworkOptionUpdate extends TestCase {
	/**
	 * Test Imagify_Subscriber->sync_on_network_option_update() should sync when on the same network.
	 */
	public function testShouldSyncWhenSameNetwork() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = $this->getMockBuilder( Imagify_Subscriber::class )
			->setConstructorArgs( [ $optionsData ] )
			->setMethods( [ 'sync_on_option_update' ] )
			->getMock();
		$subscriber
			->expects( $this->once() )
			->method( 'sync_on_option_update' );

		$option     = 'imagify_settings';
		$value      = [ 'display_webp' => 1 ];
		$old_value  = [ 'display_webp' => 1 ];
		$network_id = 3;

		Functions\when( 'get_current_network_id' )
			->justReturn( $network_id );

		$subscriber->sync_on_network_option_update( $option, $value, $old_value, $network_id );
	}

	/**
	 * Test Imagify_Subscriber->sync_on_network_option_update() should not sync when not on the same network.
	 */
	public function testShouldNotSyncWhenNotSameNetwork() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = $this->getMockBuilder( Imagify_Subscriber::class )
			->setConstructorArgs( [ $optionsData ] )
			->setMethods( [ 'sync_on_option_update' ] )
			->getMock();
		$subscriber
			->expects( $this->never() )
			->method( 'sync_on_option_update' );

		$option     = 'imagify_settings';
		$value      = [ 'display_webp' => 1 ];
		$old_value  = [ 'display_webp' => 1 ];
		$network_id = 3;

		Functions\when( 'get_current_network_id' )
			->justReturn( 2 );

		$subscriber->sync_on_network_option_update( $option, $value, $old_value, $network_id );
	}
}

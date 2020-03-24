<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::sync_on_network_option_update
 * @group  ThirdParty
 * @group  Webp
 */
class Test_SyncOnNetworkOptionUpdate extends TestCase {

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

		Functions\when( 'get_current_network_id' )->justReturn( $network_id );

		$subscriber->sync_on_network_option_update( $option, $value, $old_value, $network_id );
	}

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

		Functions\when( 'get_current_network_id' )->justReturn( 2 );

		$subscriber->sync_on_network_option_update( $option, $value, $old_value, $network_id );
	}
}

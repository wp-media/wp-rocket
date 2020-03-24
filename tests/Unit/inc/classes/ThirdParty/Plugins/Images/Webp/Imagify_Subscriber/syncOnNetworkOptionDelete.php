<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::sync_on_network_option_delete
 * @group  ThirdParty
 * @group  Webp
 */
class Test_SyncOnNetworkOptionDelete extends TestCase {

	public function testShouldSyncWhenSameNetworkAndServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )->justReturn( $network_id );

		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		$subscriber = $this->getMockBuilder( Imagify_Subscriber::class )
		                   ->setConstructorArgs( [ $optionsData ] )
		                   ->setMethods( [ 'is_serving_webp' ] )
		                   ->getMock();
		$subscriber
			->expects( $this->once() )
			->method( 'is_serving_webp' )
			->willReturn( true );

		Actions\expectDone( 'rocket_third_party_webp_change' )->once();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}

	public function testShouldNotSyncWhenSameNetworkAndNotServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )->justReturn( $network_id );

		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		$subscriber = $this->getMockBuilder( Imagify_Subscriber::class )
		                   ->setConstructorArgs( [ $optionsData ] )
		                   ->setMethods( [ 'is_serving_webp' ] )
		                   ->getMock();
		$subscriber
			->expects( $this->once() )
			->method( 'is_serving_webp' )
			->willReturn( false );

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}

	public function testShouldNotSyncWhenNotSameNetworkAndServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )->justReturn( 2 );

		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );

		$subscriber = $this->getMockBuilder( Imagify_Subscriber::class )
		                   ->setConstructorArgs( [ $optionsData ] )
		                   ->setMethods( [ 'is_serving_webp' ] )
		                   ->getMock();
		$subscriber
			->expects( $this->never() )
			->method( 'is_serving_webp' )
			->willReturn( true );

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}
}

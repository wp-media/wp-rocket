<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::sync_on_network_option_delete
 * @group  ThirdParty
 * @group  Webp
 */
class Test_SyncOnNetworkOptionDelete extends TestCase {

	public function testShouldSyncWhenSameNetworkAndServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )->justReturn( $network_id );
		Functions\when( 'get_imagify_option' )->justReturn( true );

		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->once();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}

	public function testShouldNotSyncWhenSameNetworkAndNotServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )->justReturn( $network_id );
		Functions\when( 'get_imagify_option' )->justReturn( false );

		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}

	public function testShouldNotSyncWhenNotSameNetworkAndServingWebp() {
		$option     = 'imagify_settings';
		$network_id = 3;

		Functions\when( 'get_current_network_id' )->justReturn( 2 );
		Functions\when( 'get_imagify_option' )->justReturn( true );

		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();

		$subscriber->store_option_value_before_network_delete( $option, $network_id );
		$subscriber->sync_on_network_option_delete( $option, $network_id );
	}
}

<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::sync_on_network_option_update
 * @group  ThirdParty
 * @group  Webp
 */
class Test_SyncOnNetworkOptionUpdate extends TestCase {

	public function testShouldSyncWhenSameNetwork() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber = new Imagify_Subscriber( $optionsData );

		$option     = 'imagify_settings';
		$value      = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'htaccess' ];
		$old_value  = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'picture' ];
		$network_id = 3;

		Functions\when( 'get_current_network_id' )->justReturn( $network_id );
		Functions\when( 'get_imagify_option' )->justReturn( true );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->once();

		$subscriber->sync_on_network_option_update( $option, $value, $old_value, $network_id );
	}

	public function testShouldNotSyncWhenNotSameNetwork() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber = new Imagify_Subscriber( $optionsData );

		$option     = 'imagify_settings';
		$value      = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'htaccess' ];
		$old_value  = [ 'display_nextgen' => 1, 'display_nextgen_method' => 'picture' ];
		$network_id = 3;

		Functions\when( 'get_current_network_id' )->justReturn( 2 );
		Functions\when( 'get_imagify_option' )->justReturn( true );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->never();

		$subscriber->sync_on_network_option_update( $option, $value, $old_value, $network_id );
	}
}

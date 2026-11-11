<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::sync_on_network_option_add
 * @group  ThirdParty
 * @group  Webp
 */
class Test_SyncOnNetworkOptionAdd extends TestCase {

	public function testShouldTriggerHookWhenDisplayWebpOptionEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option      = 'imagify_settings';
		$value       = [ 'display_webp' => 1 ];
		$network_id  = 3;

		Actions\expectDone( 'rocket_third_party_webp_change' )->once();

		Functions\when( 'get_current_network_id' )->justReturn( $network_id );

		$subscriber->sync_on_network_option_add( $option, $value, $network_id );
	}

	public function testShouldNotTriggerHook() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option      = 'imagify_settings';
		$value       = [ 'display_webp' => 1 ];
		$network_id  = 3;

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();

		// Different network ID.
		Functions\when( 'get_current_network_id' )->justReturn( 2 );

		$subscriber->sync_on_network_option_add( $option, $value, $network_id );

		// The 'display_webp' option is false.
		Functions\when( 'get_current_network_id' )->justReturn( $network_id );

		$value = [ 'display_webp' => 0 ];

		$subscriber->sync_on_network_option_add( $option, $value, $network_id );

		$value = [ 'foobar' => 1 ];

		$subscriber->sync_on_network_option_add( $option, $value, $network_id );
	}
}

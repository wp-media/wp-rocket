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
class TestSyncOnNetworkOptionAdd extends TestCase {
	/**
	 * Test Imagify_Subscriber->sync_on_network_option_add() should trigger a hook when the "display webp" option is enabled on option creation.
	 */
	public function testShouldTriggerHookWhenDisplayWebpOptionEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option      = 'imagify_settings';
		$value       = [ 'display_webp' => 1 ];
		$network_id  = 3;

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->once();

		Functions\when( 'get_current_network_id' )
			->justReturn( $network_id );

		$subscriber->sync_on_network_option_add( $option, $value, $network_id );

		$this->assertTrue( true ); // Prevent "risky" warning.
	}

	/**
	 * Test Imagify_Subscriber->sync_on_network_option_add() should not trigger a hook.
	 */
	public function testShouldNotTriggerHook() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option      = 'imagify_settings';
		$value       = [ 'display_webp' => 1 ];
		$network_id  = 3;

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->never();

		// Different network ID.
		Functions\when( 'get_current_network_id' )
			->justReturn( 2 );

		$subscriber->sync_on_network_option_add( $option, $value, $network_id );

		// The 'display_webp' option is false.
		Functions\when( 'get_current_network_id' )
			->justReturn( $network_id );

		$value = [ 'display_webp' => 0 ];

		$subscriber->sync_on_network_option_add( $option, $value, $network_id );

		$value = [ 'foobar' => 1 ];

		$subscriber->sync_on_network_option_add( $option, $value, $network_id );

		$this->assertTrue( true ); // Prevent "risky" warning.
	}
}

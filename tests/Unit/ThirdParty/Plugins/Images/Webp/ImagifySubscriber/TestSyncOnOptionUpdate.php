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
class TestSyncOnOptionUpdate extends TestCase {
	/**
	 * Test Imagify_Subscriber->sync_on_option_update() should sync when the plugin’s webp options change.
	 */
	public function testShouldSyncWhenOptionsChange() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->times( 4 );

		$old_value = [ 'display_webp' => 1, 'display_webp_method' => 'a' ];
		$new_value = [ 'display_webp' => 0, 'display_webp_method' => 'a' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$old_value = [ 'display_webp' => 0, 'display_webp_method' => 'a' ];
		$new_value = [ 'display_webp' => 1, 'display_webp_method' => 'a' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$old_value = [ 'display_webp' => 0, 'display_webp_method' => 'a' ];
		$new_value = [ 'display_webp' => 0, 'display_webp_method' => 'b' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$old_value = [ 'display_webp' => 1, 'display_webp_method' => 'a' ];
		$new_value = [ 'display_webp' => 1, 'display_webp_method' => 'b' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$this->assertTrue( true ); // Prevent "risky" warning.
	}

	/**
	 * Test Imagify_Subscriber->sync_on_option_update() should not sync when the plugin’s webp options do not change.
	 */
	public function testShouldNotSyncWhenOptionsDontChange() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->never();

		$old_value = [ 'display_webp' => 1, 'display_webp_method' => 'a' ];
		$new_value = [ 'display_webp' => 1, 'display_webp_method' => 'a' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$subscriber->sync_on_option_update( $old_value, $new_value );

		$this->assertTrue( true ); // Prevent "risky" warning.
	}
}

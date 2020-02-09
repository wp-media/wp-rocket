<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\ImagifySubscriber;

use Brain\Monkey\Actions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers Imagify_Subscriber::sync_on_option_update
 * @group ThirdParty
 * @group Webp
 */
class TestSyncOnOptionUpdate extends TestCase {
	/**
	 * Test Imagify_Subscriber->sync_on_option_update() should sync when the plugin’s webp options change.
	 */
	public function testShouldSyncWhenOptionsChange() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->times( 4 );

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
	}

	/**
	 * Test Imagify_Subscriber->sync_on_option_update() should not sync when the plugin’s webp options do not change.
	 */
	public function testShouldNotSyncWhenOptionsDontChange() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();

		$old_value = [ 'display_webp' => 1, 'display_webp_method' => 'a' ];
		$new_value = [ 'display_webp' => 1, 'display_webp_method' => 'a' ];

		$subscriber->sync_on_option_update( $old_value, $new_value );
		$subscriber->sync_on_option_update( $old_value, $new_value );
	}
}

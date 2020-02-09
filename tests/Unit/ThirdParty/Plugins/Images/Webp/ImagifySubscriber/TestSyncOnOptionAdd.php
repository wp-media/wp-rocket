<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\ImagifySubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @group ThirdParty
 */
class TestSyncOnOptionAdd extends TestCase {
	/**
	 * Test Imagify_Subscriber->sync_on_option_add() should trigger a hook when the "display webp" option is enabled on option creation.
	 */
	public function testShouldTriggerHookWhenDisplayWebpOptionEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option      = 'imagify_settings';
		$value       = [ 'display_webp' => 1 ];

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->once();

		$subscriber->sync_on_option_add( $option, $value );

		$this->assertTrue( true ); // Prevent "risky" warning.
	}

	/**
	 * Test Imagify_Subscriber->sync_on_option_add() should not trigger a hook when the "display webp" option is disabled on option creation.
	 */
	public function testShouldNotTriggerHookDisplayWebpOptionDisabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option      = 'imagify_settings';
		$value       = [ 'display_webp' => 0 ];

		Actions\expectDone( 'rocket_third_party_webp_change' )
			->never();

		$subscriber->sync_on_option_add( $option, $value );

		$value = [ 'foobar' => 1 ];

		$subscriber->sync_on_option_add( $option, $value );

		$this->assertTrue( true ); // Prevent "risky" warning.
	}
}

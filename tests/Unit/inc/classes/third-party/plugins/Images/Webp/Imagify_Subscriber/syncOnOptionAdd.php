<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Actions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::sync_on_option_add
 * @group  ThirdParty
 * @group  Webp
 */
class Test_SyncOnOptionAdd extends TestCase {

	public function testShouldTriggerHookWhenDisplayWebpOptionEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option      = 'imagify_settings';
		$value       = [ 'display_webp' => 1 ];

		Actions\expectDone( 'rocket_third_party_webp_change' )->once();

		$subscriber->sync_on_option_add( $option, $value );
	}

	public function testShouldNotTriggerHookDisplayWebpOptionDisabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );
		$option      = 'imagify_settings';
		$value       = [ 'display_webp' => 0 ];

		Actions\expectDone( 'rocket_third_party_webp_change' )->never();

		$subscriber->sync_on_option_add( $option, $value );

		$value = [ 'foobar' => 1 ];

		$subscriber->sync_on_option_add( $option, $value );
	}
}

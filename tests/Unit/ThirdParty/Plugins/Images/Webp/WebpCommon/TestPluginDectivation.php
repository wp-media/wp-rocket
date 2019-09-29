<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\WebpCommon;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Common;
use WP_Rocket\Tests\Unit\TestCase;

class TestPluginDeactivation extends TestCase {
	/**
	 * Test Webp_Common->plugin_deactivation() should trigger a hook when serving webp.
	 */
	public function testShouldTriggerHookWhenServingWebp() {
		$mock = $this->getMockForTrait( Webp_Common::class, [], '', true, true, true, [ 'trigger_webp_change', 'is_serving_webp' ] );
		$mock
			->expects( $this->any() )
			->method( 'is_serving_webp' )
			->willReturnOnConsecutiveCalls( true, false );
		$mock
			->expects( $this->once() )
			->method( 'trigger_webp_change' );

		$mock->plugin_deactivation();
		$mock->plugin_deactivation();
	}
}

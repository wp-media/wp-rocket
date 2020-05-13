<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\inc\classes\third_party\plugins\Images\Webp\Webp_Common;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Common;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Common::plugin_activation
 * @group ThirdParty
 * @group Webp
 */
class Test_PluginActivation extends TestCase {

	public function testShouldTriggerHookWhenServingWebp() {
		$mock = $this->getMockForTrait( Webp_Common::class, [], '', true, true, true, [ 'trigger_webp_change', 'is_serving_webp' ] );
		$mock
			->expects( $this->any() )
			->method( 'is_serving_webp' )
			->willReturnOnConsecutiveCalls( true, false );
		$mock
			->expects( $this->once() )
			->method( 'trigger_webp_change' );

		$mock->plugin_activation();
		$mock->plugin_activation();
	}
}

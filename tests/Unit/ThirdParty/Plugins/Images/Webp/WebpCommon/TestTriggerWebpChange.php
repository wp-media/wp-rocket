<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\WebpCommon;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Common;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;

/**
 * @group ThirdParty
 */
class TestTriggerWebpChange extends TestCase {
	/**
	 * Test Webp_Common->trigger_webp_change() should trigger a hook.
	 */
	public function testShouldTriggerHook() {
		Actions\expectDone( 'rocket_third_party_webp_change' )
			->once();

		$mock = $this->getMockForTrait( Webp_Common::class );

		$mock->trigger_webp_change();

		$this->assertTrue( true ); // Prevent "risky" warning.
	}
}

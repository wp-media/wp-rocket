<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\WebpCommon;

use Brain\Monkey\Actions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Common;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers Webp_Common::trigger_webp_change
 * @group ThirdParty
 * @group Webp
 */
class TestTriggerWebpChange extends TestCase {
	/**
	 * Test Webp_Common->trigger_webp_change() should trigger a hook.
	 */
	public function testShouldTriggerHook() {
		Actions\expectDone( 'rocket_third_party_webp_change' )->once();

		$mock = $this->getMockForTrait( Webp_Common::class );

		$mock->trigger_webp_change();
	}
}

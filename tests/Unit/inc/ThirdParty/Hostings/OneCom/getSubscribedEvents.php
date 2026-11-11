<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\ThirdParty\Hostings\OneCom;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\OneCom::get_subscribed_events
 *
 * @group OneCom
 * @group ThirdParty
 */
class Test_GetSubscribedEvents extends TestCase {
	/**
	 * Test that the expected callback is subscribed to each hook.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param mixed $expected Expected callback.
	 */
	public function testShouldSubscribeExpectedCallback( $config, $expected ) {
		$events = OneCom::get_subscribed_events();

		$this->assertArrayHasKey( $config['hook'], $events );
		$this->assertSame( $expected, $events[ $config['hook'] ] );
	}
}

<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SubscriberFactory;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SubscriberFactory;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SubscriberFactory::get_registry
 *
 * @group  Plugins
 * @group  ThirdParty
 */
class Test_GetRegistry extends TestCase {
	/**
	 * Get_registry() returns the factory-owned plugin ids in the expected order.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $expected Expected registry ids.
	 */
	public function testShouldReturnExpectedRegistryIds( $expected ) {
		$this->assertSame( $expected, array_keys( ( new SubscriberFactory() )->get_registry() ) );
	}
}

<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SubscriberFactory;

use Brain\Monkey\Functions;
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
	 * Registry-iterating tests may enumerate classes implementing
	 * PluginCompatibilityInterface; stub the real global is_plugin_active()
	 * so any incidental call doesn't fatal (issue #8790 slice 2).
	 *
	 * @inheritDoc
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'is_plugin_active' )->justReturn( false );
	}

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

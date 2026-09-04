<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SubscriberFactory;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SubscriberFactory;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SubscriberFactory::get_arguments
 *
 * @group  Plugins
 * @group  ThirdParty
 */
class Test_GetArguments extends TestCase {
	/**
	 * Registry-iterating tests may enumerate classes implementing
	 * PluginCompatibilityInterface; stub the real global is_plugin_active()
	 * and is_admin() so any incidental call doesn't fatal (issue #8790
	 * slices 2-3).
	 *
	 * @inheritDoc
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'is_plugin_active' )->justReturn( false );
		Functions\when( 'is_admin' )->justReturn( false );
	}

	/**
	 * Get_arguments() returns non-empty args only for the documented ids, [] otherwise.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $ids_with_arguments Ids whose arguments are expected to be non-empty.
	 */
	public function testShouldReturnArgumentsOnlyForDocumentedIds( $ids_with_arguments ) {
		Functions\when( 'rocket_direct_filesystem' )->justReturn( new \stdClass() );

		$factory = new SubscriberFactory();

		foreach ( array_keys( $factory->get_registry() ) as $id ) {
			$arguments = $factory->get_arguments( $id );

			if ( in_array( $id, $ids_with_arguments, true ) ) {
				$this->assertNotEmpty( $arguments, "Expected non-empty arguments for {$id}" );
				continue;
			}

			$this->assertSame( [], $arguments, "Expected empty arguments for {$id}" );
		}
	}
}

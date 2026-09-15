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

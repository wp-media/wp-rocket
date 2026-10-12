<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Container\IndexedDefinitionAggregate;

use WP_Rocket\Engine\Container\IndexedDefinitionAggregate;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Container\IndexedDefinitionAggregate::has
 *
 * @group Container
 * @group IndexedDefinitionAggregate
 */
class Test_Has extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config ) {
		$aggregate = new IndexedDefinitionAggregate();

		if ( null !== $config['add'] ) {
			$aggregate->add( $config['add'], 'Concrete' );
		}

		$this->assertSame( $config['expected'], $aggregate->has( $config['check'] ) );
	}
}

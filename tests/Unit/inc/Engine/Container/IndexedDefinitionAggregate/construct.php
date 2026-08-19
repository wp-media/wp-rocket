<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Container\IndexedDefinitionAggregate;

use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Dependencies\League\Container\Definition\Definition;
use WP_Rocket\Engine\Container\IndexedDefinitionAggregate;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Container\IndexedDefinitionAggregate::__construct
 *
 * @group Container
 * @group IndexedDefinitionAggregate
 */
class Test_Construct extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config ) {
		$definitions = [];

		foreach ( $config['seed'] as $seed ) {
			$definitions[] = new Definition( $seed[0], $seed[1] );
		}

		$definitions = array_merge( $definitions, $config['raw'] );

		$aggregate = new IndexedDefinitionAggregate( $definitions );
		$aggregate->setContainer( new Container() );

		$this->assertSame( $config['has'], $aggregate->has( $config['check'] ) );

		if ( null !== $config['expected_index'] ) {
			$this->assertSame( $definitions[ $config['expected_index'] ], $aggregate->getDefinition( $config['check'] ) );
		}
	}
}

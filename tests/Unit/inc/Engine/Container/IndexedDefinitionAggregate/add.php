<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Container\IndexedDefinitionAggregate;

use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Engine\Container\IndexedDefinitionAggregate;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Container\IndexedDefinitionAggregate::add
 *
 * @group Container
 * @group IndexedDefinitionAggregate
 */
class Test_Add extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config ) {
		$aggregate = new IndexedDefinitionAggregate();
		$aggregate->setContainer( new Container() );

		$first = $aggregate->{ $config['method'] }( $config['add'], 'FirstConcrete' );

		if ( $config['duplicate'] ) {
			$second = $aggregate->add( $config['add'], 'SecondConcrete' );

			$this->assertNotSame( $first, $second );
		}

		$this->assertTrue( $aggregate->has( $config['check'] ) );
		$this->assertSame( $first, $aggregate->getDefinition( $config['check'] ) );

		if ( $config['shared'] ) {
			$this->assertTrue( $aggregate->getDefinition( $config['check'] )->isShared() );
		}
	}
}

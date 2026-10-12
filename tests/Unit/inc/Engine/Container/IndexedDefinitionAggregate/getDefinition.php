<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Container\IndexedDefinitionAggregate;

use WP_Rocket\Dependencies\League\Container\Container;
use WP_Rocket\Dependencies\League\Container\Exception\NotFoundException;
use WP_Rocket\Engine\Container\IndexedDefinitionAggregate;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Container\IndexedDefinitionAggregate::getDefinition
 *
 * @group Container
 * @group IndexedDefinitionAggregate
 */
class Test_GetDefinition extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config ) {
		$aggregate = new IndexedDefinitionAggregate();
		$container = new Container( $aggregate );

		$added = null !== $config['add'] ? $aggregate->add( $config['add'], 'Concrete' ) : null;

		if ( $config['exception'] ) {
			$this->expectException( NotFoundException::class );
			$this->expectExceptionMessage( $config['message'] );
		}

		$result = $aggregate->getDefinition( $config['get'] );

		$this->assertSame( $added, $result );
		$this->assertSame( $container, $result->getContainer() );
	}
}

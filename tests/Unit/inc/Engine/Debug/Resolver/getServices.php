<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Debug\Resolver;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Debug\Resolver;

/**
 * @covers \WP_Rocket\Engine\Debug\Resolver::get_services
 */
class Test_GetServices extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {

        if ( empty( $config['options'] ) ) {
            Functions\expect( 'get_rocket_option' )->never();
            return;
        }

        foreach ( $config['options'] as $option => $services ) {
            Functions\expect( 'get_rocket_option' )
				->with( $option )
				->andReturn($services['enabled']);
        }

		$this->assertSame(
			$expected,
			Resolver::get_services()
		);
	}
}

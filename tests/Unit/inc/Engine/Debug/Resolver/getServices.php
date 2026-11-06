<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Debug\Resolver;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Debug\Resolver;

/**
 * Test class covering \WP_Rocket\Engine\Debug\Resolver::get_services
 */
class Test_GetServices extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
        $options = Mockery::mock( Options_Data::class );
        $resolver = new Resolver( $options );

        if ( empty( $config['options'] ) ) {
            $options->shouldReceive( 'get' )->never();
            return;
        }

        foreach ( $config['options'] as $option => $services ) {
            $options->shouldReceive( 'get' )
                ->with( $option, 0 )
                ->andReturn( $services['enabled'] );
        }

		$this->assertSame(
			$expected,
			$resolver->get_services()
		);
	}
}

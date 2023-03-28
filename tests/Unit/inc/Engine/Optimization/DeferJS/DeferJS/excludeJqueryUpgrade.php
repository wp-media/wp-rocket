<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DeferJS\DeferJS;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\DeferJS::exclude_jquery_upgrade
 *
 * @group  DeferJS
 */
class Test_ExcludeJqueryUpgrade extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$options  = Mockery::mock( Options_Data::class );
		$defer_js = new DeferJS( $options, Mockery::mock( DataManager::class ) );

        Functions\expect( 'get_option' )
            ->once()
            ->with( 'wp_rocket_settings' )
            ->andReturn( $config['options'] );

        if ( $expected ) {
            Functions\expect( 'update_option' )
                ->once()
                ->with( 'wp_rocket_settings', $expected );
        } else {
            Functions\expect( 'update_option' )->never();
        }

        $defer_js->exclude_jquery_upgrade();
	}
}

<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Options\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Abilities\Context;
use WP_Rocket\Engine\Abilities\Options\GetOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Engine\Abilities\Options\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Options\Subscriber::register_options_category()
 *
 * @group Abilities
 */
class Test_RegisterOptionsCategory extends TestCase {
	/**
	 * Test that register_options_category() honors the abilities gate.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$get_options = Mockery::mock( GetOptions::class );
		$set_option  = Mockery::mock( SetOption::class );
		$context     = Mockery::mock( Context::class );

		$context->shouldReceive( 'is_enabled' )
			->once()
			->andReturn( $config['is_enabled'] );

		if ( $expected['wp_register_ability_category_called'] ) {
			Functions\expect( 'wp_register_ability_category' )
				->once()
				->with( 'wp-rocket-options', Mockery::type( 'array' ) );
		} else {
			Functions\expect( 'wp_register_ability_category' )->never();
		}

		if ( $config['is_enabled'] ) {
			Functions\when( 'wp_register_ability_category' )->justReturn( null );
		}

		$subscriber = new Subscriber( $get_options, $set_option, $context );
		$subscriber->register_options_category();
	}
}

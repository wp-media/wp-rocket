<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Options\Subscriber;

use Mockery;
use WP_Rocket\Engine\Abilities\Context;
use WP_Rocket\Engine\Abilities\Options\GetOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Engine\Abilities\Options\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Options\Subscriber::register_get_options_ability()
 *
 * @group Abilities
 */
class Test_RegisterGetOptionsAbility extends TestCase {
	/**
	 * Test that register_get_options_ability() honors the abilities gate.
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

		if ( $expected['register_called'] ) {
			$get_options->shouldReceive( 'register' )->once();
		} else {
			$get_options->shouldReceive( 'register' )->never();
		}

		$subscriber = new Subscriber( $get_options, $set_option, $context );
		$subscriber->register_get_options_ability();
	}
}

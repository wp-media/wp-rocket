<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Context;

use Brain\Monkey\Filters;
use WP_Rocket\Engine\Abilities\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Context::is_enabled()
 *
 * @group Abilities
 */
class IsEnabledTest extends TestCase {
	/**
	 * Test should return expected result based on filter value.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, bool $expected ): void {
		Filters\expectApplied( 'rocket_enable_abilities' )
			->once()
			->with( true )
			->andReturn( $config['filter_value'] );

		$context = new Context();

		$this->assertSame( $expected, $context->is_enabled() );
	}
}

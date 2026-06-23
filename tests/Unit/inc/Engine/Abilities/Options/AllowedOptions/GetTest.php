<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Options\AllowedOptions;

use Brain\Monkey\Filters;
use WP_Rocket\Engine\Abilities\Options\AllowedOptions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Options\AllowedOptions::get()
 *
 * @group Abilities
 */
class GetTest extends TestCase {
	/**
	 * Test get() returns the expected allowlist.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected assertion data.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$filter = Filters\expectApplied( 'rocket_mcp_options_allowlist' )
			->once();

		if ( null !== $config['filter_callback'] ) {
			$filter->andReturnUsing( $config['filter_callback'] );
		} else {
			$filter->andReturnFirstArg();
		}

		$allowed_options = new AllowedOptions();
		$result          = $allowed_options->get();

		$this->assertIsArray( $result );

		if ( null !== $expected['contains'] ) {
			$this->assertContains( $expected['contains'], $result );
		}

		if ( null !== $expected['does_not_contain'] ) {
			$this->assertNotContains( $expected['does_not_contain'], $result );
		}
	}
}

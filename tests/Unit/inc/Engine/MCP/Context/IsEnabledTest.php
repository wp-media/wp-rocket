<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Context;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\MCP\Context::is_enabled()
 *
 * @group MCP
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
		Functions\when( '_doing_it_wrong' )->justReturn( null );
		Functions\when( 'esc_attr' )->returnArg();

		Filters\expectApplied( 'rocket_mcp_oauth_server_enabled' )
			->once()
			->with( true )
			->andReturn( $config['filter_value'] );

		$context = new Context();

		$this->assertSame( $expected, $context->is_enabled() );
	}
}

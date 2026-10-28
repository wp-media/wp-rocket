<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Context\Context;

use Brain\Monkey\Filters;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Context\Context::is_available
 *
 * @group RocketInsights
 */
class TestIsAvailable extends TestCase {
	/**
	 * Context under test.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Build the context without its unused dependencies.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// The query class cannot be mocked (BerlinDB __isset signature) and is unused by is_available().
		$this->context = ( new \ReflectionClass( Context::class ) )->newInstanceWithoutConstructor();
	}

	/**
	 * The filter default must be true.
	 *
	 * @return void
	 */
	public function testShouldReturnTrueByDefault() {
		Filters\expectApplied( 'rocket_rocket_insights_enabled' )
			->once()
			->with( true )
			->andReturnFirstArg();

		$this->assertTrue( $this->context->is_available() );
	}

	/**
	 * A false filter result must be honoured.
	 *
	 * @return void
	 */
	public function testShouldReturnFalseWhenFilterReturnsFalse() {
		Filters\expectApplied( 'rocket_rocket_insights_enabled' )
			->once()
			->with( true )
			->andReturn( false );

		$this->assertFalse( $this->context->is_available() );
	}
}

<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Options;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Abilities\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Options::execute()
 *
 * @group Abilities
 */
class ExecuteTest extends TestCase {
	/**
	 * Options_Data mock.
	 *
	 * @var Options_Data|Mockery\MockInterface
	 */
	private $options_data;

	/**
	 * Options instance under test.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->options_data = Mockery::mock( Options_Data::class );
		$this->options      = new Options( $this->options_data );
	}

	/**
	 * Test execute() filters out denylist keys and returns allowed options.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration containing 'options' key.
	 * @param array $expected Expected result array.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$this->options_data
			->shouldReceive( 'get_options' )
			->once()
			->andReturn( $config['options'] );

		$result = $this->options->execute();

		$this->assertSame( $expected, $result );
	}
}

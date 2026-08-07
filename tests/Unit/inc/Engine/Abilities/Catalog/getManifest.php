<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Catalog;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Abilities\Catalog;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Catalog::get_manifest()
 *
 * @group Abilities
 */
class Test_GetManifest extends TestCase {
	/**
	 * Catalog instance under test.
	 *
	 * @var Catalog
	 */
	private $catalog;

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->catalog = new Catalog();
	}

	/**
	 * Builds a mocked ability object exposing the getters Catalog reads.
	 *
	 * @param array $ability Ability data.
	 *
	 * @return Mockery\MockInterface
	 */
	private function mockAbility( array $ability ) {
		$mock = Mockery::mock();
		$mock->shouldReceive( 'get_name' )->andReturn( $ability['name'] );
		$mock->shouldReceive( 'get_label' )->andReturn( $ability['label'] );
		$mock->shouldReceive( 'get_description' )->andReturn( $ability['description'] );
		$mock->shouldReceive( 'get_category' )->andReturn( $ability['category'] );
		$mock->shouldReceive( 'get_input_schema' )->andReturn( $ability['input_schema'] );
		$mock->shouldReceive( 'get_output_schema' )->andReturn( $ability['output_schema'] );
		$mock->shouldReceive( 'get_meta' )->andReturn( $ability['meta'] );

		return $mock;
	}

	/**
	 * Test get_manifest() filters and maps the registered abilities.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration containing an 'abilities' key.
	 * @param array $expected Expected manifest result.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$abilities = array_map( [ $this, 'mockAbility' ], $config['abilities'] );

		Functions\when( 'wp_get_abilities' )->justReturn( $abilities );

		$this->assertSame( $expected, $this->catalog->get_manifest() );
	}
}

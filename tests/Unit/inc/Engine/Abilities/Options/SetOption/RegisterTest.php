<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Options\SetOption;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Abilities\Options\AllowedOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Options\SetOption::register()
 *
 * @group Abilities
 */
class RegisterTest extends TestCase {
	/**
	 * AllowedOptions mock.
	 *
	 * @var AllowedOptions|Mockery\MockInterface
	 */
	private $allowed_options;

	/**
	 * SetOption instance under test.
	 *
	 * @var SetOption
	 */
	private $set_option;

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->allowed_options = Mockery::mock( AllowedOptions::class );
		$this->set_option      = new SetOption( $this->allowed_options );

		$this->stubTranslationFunctions();
	}

	/**
	 * Test the option_name enum registered for the ability is a JSON-encodable list.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected enum and its JSON encoding.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$captured = null;

		$this->allowed_options
			->shouldReceive( 'get' )
			->andReturn( $config['allowed_keys'] );

		Functions\expect( 'wp_register_ability' )
			->once()
			->with(
				'wp-rocket/set-option',
				Mockery::on(
					function ( $args ) use ( &$captured ) {
						$captured = $args;
						return true;
					}
				)
			);

		$this->set_option->register();

		$enum = $captured['input_schema']['properties']['option_name']['enum'];

		$this->assertSame( array_values( $enum ), $enum );
		$this->assertSame( $expected['enum'], $enum );
		$this->assertSame( $expected['enum_json'], json_encode( $enum ) );
		$this->assertNotContains( 'cdn_state', $enum );
	}
}

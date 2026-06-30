<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Options\SetOption;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Options\SetOption::execute()
 *
 * @group Abilities
 */
class ExecuteTest extends TestCase {
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

		$this->set_option = new SetOption();

		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();
		$this->stubWpParseUrl();
	}

	/**
	 * Test execute() handles various input scenarios correctly.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result array.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$input = $config['input'];

		$option_name    = $input['option_name'];
		$previous_value = $config['previous_value'];

		Functions\when( 'rocket_sanitize_textarea_field' )->returnArg( 2 );
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_strip_all_tags' )->alias( function ( $string, $remove_breaks ) {
			$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
			$string = strip_tags( $string );

			if ( $remove_breaks ) {
				$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
			}

			return trim( $string );
		} );

		if ( $expected['success'] ) {
		Functions\expect( 'get_rocket_option' )
			->with( $option_name )
			->andReturn( $previous_value );

		Functions\expect( 'update_rocket_option' )
			->once()
			->with( $option_name, $expected['new_value'] );
		}

		$result = $this->set_option->execute( $input );

		$this->assertSame( $expected['success'], $result['success'] );

		if ( $expected['success'] ) {
			$this->assertSame( $expected['previous_value'], $result['previous_value'] );
			$this->assertSame( $expected['new_value'], $result['new_value'] );
			$this->assertArrayNotHasKey( 'error', $result );
		} else {
			$this->assertSame( $expected['error'], $result['error'] );
			$this->assertArrayNotHasKey( 'previous_value', $result );
			$this->assertArrayNotHasKey( 'new_value', $result );
		}
	}
}

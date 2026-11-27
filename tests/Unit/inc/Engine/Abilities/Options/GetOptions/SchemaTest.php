<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Abilities\Options\GetOptions;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Abilities\Options\AllowedOptions;
use WP_Rocket\Engine\Abilities\Options\GetOptions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Abilities\Options\GetOptions::schema()
 *
 * @group Abilities
 */
class SchemaTest extends TestCase {
	/**
	 * Test schema() keeps only allowlisted options that have a definition.
	 *
	 * @return void
	 */
	public function testShouldReturnDefinitionsOfAllowedOptionsOnly(): void {
		$allowed_options = Mockery::mock( AllowedOptions::class );
		$allowed_options
			->shouldReceive( 'get' )
			->once()
			->andReturn( [ 'cache_logged_user', 'not_a_defined_option' ] );

		$get_options = new GetOptions( Mockery::mock( Options_Data::class ), $allowed_options );

		$schema = $get_options->schema();

		$this->assertSame( [ 'cache_logged_user' ], array_keys( $schema ) );
		$this->assertSame( 'integer', $schema['cache_logged_user']['type'] );
		$this->assertSame( [ 0, 1 ], $schema['cache_logged_user']['enum'] );
	}
}

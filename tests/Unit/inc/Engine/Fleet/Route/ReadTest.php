<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Fleet\Route;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Abilities\Options\AllowedOptions;
use WP_Rocket\Engine\Abilities\Options\GetOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Engine\Fleet\Route;
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\FleetBridge\Contract\Verifier;

/**
 * Tests for WP_Rocket\Engine\Fleet\Route::read()
 *
 * Fleet parses this response, so its keys are a contract: one renamed or
 * dropped here breaks every site Fleet reads.
 *
 * @group Fleet
 */
class ReadTest extends TestCase {
	/**
	 * Test the response carries exactly the keys Fleet reads.
	 *
	 * @return void
	 */
	public function testShouldAnswerWithTheContractKeys(): void {
		$data = $this->read();

		$this->assertSame(
			[ 'site', 'settings', 'schema', 'unset', 'writable' ],
			array_keys( $data )
		);
	}

	/**
	 * Test a stored option is reported as stored, and an unwritten one as unset.
	 *
	 * @return void
	 */
	public function testShouldReportStoredAndUnsetOptions(): void {
		$data = $this->read();

		$this->assertSame( 'example.com', $data['site'] );
		$this->assertEquals(
			(object) [
				'cache_mobile' => 1,
				'minify_css'   => 0,
			],
			$data['settings']
		);
		$this->assertSame( [ 'minify_css' ], $data['unset'] );
		$this->assertSame( [ 'cache_mobile', 'minify_css' ], $data['writable'] );
	}

	/**
	 * The response data of a read on a site that saved `cache_mobile` only.
	 *
	 * @return array
	 */
	private function read(): array {
		$schema = [
			'cache_mobile' => [ 'type' => 'boolean' ],
			'minify_css'   => [ 'type' => 'boolean' ],
		];

		$get_options = Mockery::mock( GetOptions::class );
		$get_options->shouldReceive( 'schema' )->andReturn( $schema );
		$get_options->shouldReceive( 'execute' )->andReturn( [ 'cache_mobile' => 1 ] );

		$allowed = Mockery::mock( AllowedOptions::class );
		$allowed->shouldReceive( 'get' )->andReturn( array_keys( $schema ) );

		Functions\when( 'home_url' )->justReturn( 'https://example.com' );
		Functions\when( 'wp_parse_url' )->justReturn( 'example.com' );
		Functions\when( 'get_rocket_option' )->justReturn( 0 );

		$route = new Route(
			Mockery::mock( Verifier::class ),
			$get_options,
			Mockery::mock( SetOption::class ),
			$allowed
		);

		$response = $route->read();

		$this->assertSame( 200, $response->get_status() );

		return $response->get_data();
	}
}

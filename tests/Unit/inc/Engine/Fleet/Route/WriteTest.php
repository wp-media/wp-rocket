<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Fleet\Route;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_REST_Request;
use WP_Rocket\Engine\Abilities\Options\AllowedOptions;
use WP_Rocket\Engine\Abilities\Options\GetOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Engine\Fleet\Route;
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\FleetBridge\Contract\Verifier;

/**
 * Tests for WP_Rocket\Engine\Fleet\Route::write()
 *
 * `write()` runs only after `is_fleet()` has believed the request, so nothing
 * here is about authentication. What it is about is the request being shaped
 * the way it claims: a body that is not what it says it is must be refused
 * before any option is written, because a partially applied batch leaves a
 * customer's configuration in a state neither side asked for.
 *
 * @group Fleet
 */
class WriteTest extends TestCase {
	/**
	 * The allowlist size used throughout, so the batch bound is a known number.
	 *
	 * @var array
	 */
	private $allowlist = [ 'cache_logged_user', 'cache_mobile', 'minify_css' ];

	/**
	 * Test a malformed body is refused, and nothing is written.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected assertion data.
	 *
	 * @return void
	 */
	public function testShouldRefuseAMalformedBody( array $config, array $expected ): void {
		$set_option = Mockery::mock( SetOption::class );

		// The point of every case below. A body we cannot read must cost the
		// customer nothing, so no option may be touched on the way to refusing.
		$set_option->shouldNotReceive( 'execute' );

		$route = $this->route( $set_option );

		$refusal = $route->write( $this->request( $config['body'] ) );

		$this->assertInstanceOf( WP_Error::class, $refusal );
		$this->assertSame( 'rocket_fleet_bad_request', $refusal->get_error_code() );
		$this->assertSame( [ 'status' => 400 ], $refusal->get_error_data() );
		$this->assertSame( $expected['message'], $refusal->get_error_message() );
	}

	/**
	 * Test a batch is bounded by the size of the allowlist.
	 *
	 * The route is reachable by anyone and the bound is the only thing between
	 * a believed caller and an arbitrarily long list of writes. The allowlist
	 * is the right bound because a batch longer than it must contain either a
	 * repeat or something not writable.
	 *
	 * @return void
	 */
	public function testShouldRefuseABatchLongerThanTheAllowlist(): void {
		$set_option = Mockery::mock( SetOption::class );
		$set_option->shouldNotReceive( 'execute' );

		$options      = [];
		$one_too_many = count( $this->allowlist ) + 1;

		for ( $i = 0; $i < $one_too_many; $i++ ) {
			$options[] = [
				'option_name'  => 'cache_logged_user',
				'option_value' => 1,
			];
		}

		$refusal = $this->route( $set_option )->write(
			$this->request( (string) wp_json_encode( [ 'options' => $options ] ) )
		);

		$this->assertInstanceOf( WP_Error::class, $refusal );
		$this->assertSame(
			'At most ' . count( $this->allowlist ) . ' options may be sent in one request.',
			$refusal->get_error_message()
		);
	}

	/**
	 * Test a change setting an option to null is answered, not called malformed.
	 *
	 * `isset()` here rather than `array_key_exists()` would refuse it, and null
	 * is a legitimate value for a WP Rocket option.
	 *
	 * @return void
	 */
	public function testShouldAcceptAnOptionValueOfNull(): void {
		$set_option = Mockery::mock( SetOption::class );
		$set_option->shouldReceive( 'execute' )
			->once()
			->andReturn(
				[
					'success'        => true,
					'option_name'    => 'cache_logged_user',
					'previous_value' => 1,
					'new_value'      => null,
				]
			);

		Functions\when( 'home_url' )->justReturn( 'https://example.com' );
		Functions\when( 'wp_parse_url' )->justReturn( 'example.com' );

		$response = $this->route( $set_option )->write(
			$this->request( '{"option_name":"cache_logged_user","option_value":null}' )
		);

		$this->assertNotInstanceOf( WP_Error::class, $response );
		$this->assertSame( 200, $response->get_status() );
	}

	/**
	 * A route whose write ability is the given double.
	 *
	 * @param SetOption $set_option The write ability.
	 *
	 * @return Route
	 */
	private function route( SetOption $set_option ): Route {
		$allowed = Mockery::mock( AllowedOptions::class );
		$allowed->shouldReceive( 'get' )->andReturn( $this->allowlist );

		return new Route(
			Mockery::mock( Verifier::class ),
			Mockery::mock( GetOptions::class ),
			$set_option,
			$allowed
		);
	}

	/**
	 * A request carrying the given raw body.
	 *
	 * @param string $body The raw body.
	 *
	 * @return WP_REST_Request
	 */
	private function request( string $body ): WP_REST_Request {
		$request = Mockery::mock( WP_REST_Request::class );
		$request->shouldReceive( 'get_body' )->andReturn( $body );

		return $request;
	}
}

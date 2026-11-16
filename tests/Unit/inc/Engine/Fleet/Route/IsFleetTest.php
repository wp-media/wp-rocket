<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Fleet\Route;

use Mockery;
use WP_Error;
use WP_REST_Request;
use WP_Rocket\Engine\Abilities\Options\AllowedOptions;
use WP_Rocket\Engine\Abilities\Options\GetOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;
use WP_Rocket\Engine\Fleet\Route;
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\FleetBridge\Bridge;
use WPMedia\FleetBridge\Claims;
use WPMedia\FleetBridge\Contract\Verifier;
use WPMedia\FleetBridge\Exception\NotAuthorised;

/**
 * Tests for WP_Rocket\Engine\Fleet\Route::is_fleet()
 *
 * This is the permission callback on a route anyone on the internet can reach,
 * so what is asserted here is what stands between a stranger and a customer's
 * settings. The checks themselves belong to wp-media/fleet-bridge and are
 * tested there; what belongs to WP Rocket is *which* checks are demanded, with
 * which scope, over which bytes — and that a refusal says nothing useful.
 *
 * @group Fleet
 */
class IsFleetTest extends TestCase {
	/**
	 * Test the scope demanded is derived from the method, not from the caller.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected assertion data.
	 *
	 * @return void
	 */
	public function testShouldDemandTheScopeForTheMethod( array $config, array $expected ): void {
		$verifier = Mockery::mock( Verifier::class );

		$verifier->shouldReceive( 'verifyCommand' )
			->once()
			->with( $config['authorization'], $config['body'] )
			->andReturn( new Claims( [] ) );

		$verifier->shouldReceive( 'verifyGrant' )
			->once()
			->with( $config['consent'], $expected['scope'] )
			->andReturn( new Claims( [] ) );

		$route = $this->route( $verifier );

		$this->assertTrue( $route->is_fleet( $this->request( $config ) ) );
	}

	/**
	 * Test a request with no command is refused, whatever else it carries.
	 *
	 * @return void
	 */
	public function testShouldRefuseWhenTheCommandIsNotBelieved(): void {
		$verifier = Mockery::mock( Verifier::class );

		$verifier->shouldReceive( 'verifyCommand' )
			->once()
			->andThrow( new NotAuthorised( 'No token was presented.' ) );

		// Never reached. A grant is only interesting once we know who is asking,
		// and checking it first would spend a valid grant on an unsigned request.
		$verifier->shouldNotReceive( 'verifyGrant' );

		$refusal = $this->route( $verifier )->is_fleet( $this->request() );

		$this->assertRefusal( $refusal );
	}

	/**
	 * Test a signed command with no consent behind it is still refused.
	 *
	 * The half Fleet cannot assert about itself. Without this, a valid Fleet
	 * command would be enough on its own and the customer's switch would mean
	 * nothing.
	 *
	 * @return void
	 */
	public function testShouldRefuseWhenTheGrantIsNotBelieved(): void {
		$verifier = Mockery::mock( Verifier::class );

		$verifier->shouldReceive( 'verifyCommand' )
			->once()
			->andReturn( new Claims( [] ) );

		$verifier->shouldReceive( 'verifyGrant' )
			->once()
			->andThrow( new NotAuthorised( 'This grant does not carry settings:write.' ) );

		$this->assertRefusal( $this->route( $verifier )->is_fleet( $this->request() ) );
	}

	/**
	 * Test the refusal never reports which check failed.
	 *
	 * The route is reachable by anyone, so distinguishing "unknown issuer" from
	 * "bad signature" would tell a stranger whether the issuer they guessed is
	 * the one this site trusts.
	 *
	 * @return void
	 */
	public function testShouldNotReportWhyItRefused(): void {
		$secret = 'issuer grn:2@int:grn::environment/g1:wprocket:fleet.localhost is not trusted here';

		$verifier = Mockery::mock( Verifier::class );
		$verifier->shouldReceive( 'verifyCommand' )->once()->andThrow( new NotAuthorised( $secret ) );

		$refusal = $this->route( $verifier )->is_fleet( $this->request() );

		$this->assertSame( Bridge::REFUSED, $refusal->get_error_message() );
		$this->assertStringNotContainsString( 'issuer', $refusal->get_error_message() );
		$this->assertStringNotContainsString( 'fleet.localhost', $refusal->get_error_message() );
	}

	/**
	 * A route wired to the given verifier.
	 *
	 * @param Verifier $verifier The verifier.
	 *
	 * @return Route
	 */
	private function route( Verifier $verifier ): Route {
		return new Route(
			$verifier,
			Mockery::mock( GetOptions::class ),
			Mockery::mock( SetOption::class ),
			Mockery::mock( AllowedOptions::class )
		);
	}

	/**
	 * A request carrying the given headers, method and body.
	 *
	 * @param array $config Test configuration.
	 *
	 * @return WP_REST_Request
	 */
	private function request( array $config = [] ): WP_REST_Request {
		$config = array_merge(
			[
				'method'        => 'GET',
				'authorization' => 'Bearer command',
				'consent'       => 'grant',
				'body'          => '',
			],
			$config
		);

		$request = Mockery::mock( WP_REST_Request::class );
		$request->shouldReceive( 'get_method' )->andReturn( $config['method'] );
		$request->shouldReceive( 'get_body' )->andReturn( $config['body'] );
		$request->shouldReceive( 'get_header' )
			->with( 'authorization' )
			->andReturn( $config['authorization'] );
		$request->shouldReceive( 'get_header' )
			->with( Route::CONSENT_HEADER )
			->andReturn( $config['consent'] );

		return $request;
	}

	/**
	 * Assert a refusal is a 401 carrying the one fixed message.
	 *
	 * @param mixed $refusal What is_fleet() returned.
	 *
	 * @return void
	 */
	private function assertRefusal( $refusal ): void {
		$this->assertInstanceOf( WP_Error::class, $refusal );
		$this->assertSame( 'rocket_fleet_unauthorised', $refusal->get_error_code() );
		$this->assertSame( Bridge::REFUSED, $refusal->get_error_message() );
		$this->assertSame( [ 'status' => 401 ], $refusal->get_error_data() );
	}
}

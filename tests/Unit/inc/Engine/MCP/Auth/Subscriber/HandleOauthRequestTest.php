<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\MCP\Auth\AuthorizeCallback;
use WP_Rocket\Engine\MCP\Auth\AuthorizeEndpoint;
use WP_Rocket\Engine\MCP\Auth\ConsentEndpoint;
use WP_Rocket\Engine\MCP\Auth\Rewrite;
use WP_Rocket\Engine\MCP\Auth\RevokeEndpoint;
use WP_Rocket\Engine\MCP\Auth\Subscriber;
use WP_Rocket\Engine\MCP\Auth\TokenEndpoint;
use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\MCP\Auth\Subscriber::handle_oauth_request()
 *
 * @group MCP
 */
class HandleOauthRequestTest extends TestCase {
	/**
	 * Context mock.
	 *
	 * @var Context|Mockery\MockInterface
	 */
	private $context;

	/**
	 * Authorize endpoint mock.
	 *
	 * @var AuthorizeEndpoint|Mockery\MockInterface
	 */
	private $authorize_endpoint;

	/**
	 * Authorize callback mock.
	 *
	 * @var AuthorizeCallback|Mockery\MockInterface
	 */
	private $authorize_callback;

	/**
	 * Token endpoint mock.
	 *
	 * @var TokenEndpoint|Mockery\MockInterface
	 */
	private $token_endpoint;

	/**
	 * Consent endpoint mock.
	 *
	 * @var ConsentEndpoint|Mockery\MockInterface
	 */
	private $consent_endpoint;

	/**
	 * Revoke endpoint mock.
	 *
	 * @var RevokeEndpoint|Mockery\MockInterface
	 */
	private $revoke_endpoint;

	/**
	 * Subscriber instance under test.
	 *
	 * @var Subscriber
	 */
	private $subscriber;

	/**
	 * Set up the test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->context            = Mockery::mock( Context::class );
		$this->authorize_endpoint = Mockery::mock( AuthorizeEndpoint::class );
		$this->authorize_callback = Mockery::mock( AuthorizeCallback::class );
		$this->token_endpoint     = Mockery::mock( TokenEndpoint::class );
		$this->consent_endpoint   = Mockery::mock( ConsentEndpoint::class );
		$this->revoke_endpoint    = Mockery::mock( RevokeEndpoint::class );

		$this->subscriber = new Subscriber(
			new Rewrite(),
			$this->authorize_endpoint,
			$this->authorize_callback,
			$this->token_endpoint,
			$this->consent_endpoint,
			$this->revoke_endpoint,
			$this->context
		);
	}

	/**
	 * Test nothing happens when no route matched, regardless of enabled state.
	 *
	 * @return void
	 */
	public function testShouldNotDispatchWhenDisabled(): void {
		$this->context->shouldNotReceive( 'is_enabled' );

		Functions\expect( 'get_query_var' )
			->once()
			->with( Rewrite::OAUTH_QUERY_VAR, '' )
			->andReturn( '' );

		Functions\expect( 'status_header' )->never();

		$this->authorize_endpoint->shouldNotReceive( 'handle_request' );
		$this->authorize_callback->shouldNotReceive( 'handle_request' );
		$this->token_endpoint->shouldNotReceive( 'handle_request' );
		$this->consent_endpoint->shouldNotReceive( 'handle_request' );
		$this->revoke_endpoint->shouldNotReceive( 'handle_request' );

		$this->subscriber->handle_oauth_request();
	}

	/**
	 * Test a clean 404 is forced when a stale rewrite rule routes a request
	 * here while the OAuth server is disabled.
	 *
	 * @return void
	 */
	public function testShouldForce404WhenDisabledButRouteMatchedByStaleRewriteRule(): void {
		global $wp_query;

		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( false );

		Functions\expect( 'get_query_var' )
			->once()
			->with( Rewrite::OAUTH_QUERY_VAR, '' )
			->andReturn( 'authorize' );

		$wp_query = Mockery::mock(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_query->shouldReceive( 'set_404' )->once();

		Functions\expect( 'status_header' )->once()->with( 404 );

		$this->authorize_endpoint->shouldNotReceive( 'handle_request' );
		$this->authorize_callback->shouldNotReceive( 'handle_request' );
		$this->token_endpoint->shouldNotReceive( 'handle_request' );
		$this->consent_endpoint->shouldNotReceive( 'handle_request' );
		$this->revoke_endpoint->shouldNotReceive( 'handle_request' );

		$this->subscriber->handle_oauth_request();

		$wp_query = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}

	/**
	 * Test the request is dispatched to the matching endpoint handler when enabled.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config Test configuration containing the 'endpoint' query var value
	 *                      and 'mock' the mock property name expected to receive the call.
	 *
	 * @return void
	 */
	public function testShouldDispatchToMatchingHandlerWhenEnabled( array $config ): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( true );

		Functions\expect( 'get_query_var' )
			->once()
			->with( Rewrite::OAUTH_QUERY_VAR, '' )
			->andReturn( $config['endpoint'] );

		$this->{$config['mock']}->shouldReceive( 'handle_request' )->once();

		$this->subscriber->handle_oauth_request();
	}
}

<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\Discovery\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\MCP\Auth\Discovery\Endpoints;
use WP_Rocket\Engine\MCP\Auth\Discovery\Subscriber;
use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\MCP\Auth\Discovery\Subscriber::handle_request()
 *
 * @group MCP
 */
class HandleRequestTest extends TestCase {
	/**
	 * Context mock.
	 *
	 * @var Context|Mockery\MockInterface
	 */
	private $context;

	/**
	 * Endpoints mock.
	 *
	 * @var Endpoints|Mockery\MockInterface
	 */
	private $endpoints;

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

		$this->context   = Mockery::mock( Context::class );
		$this->endpoints = Mockery::mock( Endpoints::class );

		$this->subscriber = new Subscriber( $this->endpoints, $this->context );
	}

	/**
	 * Test nothing happens when no route matched, regardless of enabled state.
	 *
	 * @return void
	 */
	public function testShouldNotHandleRequestWhenDisabled(): void {
		$this->context->shouldNotReceive( 'is_enabled' );

		Functions\expect( 'get_query_var' )
			->once()
			->with( Endpoints::QUERY_VAR, '' )
			->andReturn( '' );

		Functions\expect( 'status_header' )->never();

		$this->endpoints->shouldNotReceive( 'handle_request' );

		$this->subscriber->handle_request();
	}

	/**
	 * Test the discovery document is served when the OAuth server is enabled.
	 *
	 * @return void
	 */
	public function testShouldHandleRequestWhenEnabled(): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( true );

		Functions\expect( 'get_query_var' )
			->once()
			->with( Endpoints::QUERY_VAR, '' )
			->andReturn( 'protected-resource' );

		$this->endpoints->shouldReceive( 'handle_request' )->once();

		$this->subscriber->handle_request();
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
			->with( Endpoints::QUERY_VAR, '' )
			->andReturn( 'protected-resource' );

		$wp_query = Mockery::mock(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$wp_query->shouldReceive( 'set_404' )->once();

		Functions\expect( 'status_header' )->once()->with( 404 );

		$this->endpoints->shouldNotReceive( 'handle_request' );

		$this->subscriber->handle_request();

		$wp_query = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	}
}

<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Transport\Subscriber;

use Mockery;
use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Engine\MCP\Transport\Server;
use WP_Rocket\Engine\MCP\Transport\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\MCP\Transport\Subscriber::register_server()
 *
 * @group MCP
 */
class RegisterServerTest extends TestCase {
	/**
	 * Context mock.
	 *
	 * @var Context|Mockery\MockInterface
	 */
	private $context;

	/**
	 * Server mock.
	 *
	 * @var Server|Mockery\MockInterface
	 */
	private $server;

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

		$this->context = Mockery::mock( Context::class );
		$this->server  = Mockery::mock( Server::class );

		$this->subscriber = new Subscriber( $this->server, $this->context );
	}

	/**
	 * Test the MCP server is not registered when the OAuth server is disabled.
	 *
	 * @return void
	 */
	public function testShouldNotRegisterServerWhenDisabled(): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( false );

		$this->server->shouldNotReceive( 'register_server' );

		$this->subscriber->register_server();
	}

	/**
	 * Test the MCP server is registered when the OAuth server is enabled.
	 *
	 * @return void
	 */
	public function testShouldRegisterServerWhenEnabled(): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( true );

		$this->server->shouldReceive( 'register_server' )->once();

		$this->subscriber->register_server();
	}
}

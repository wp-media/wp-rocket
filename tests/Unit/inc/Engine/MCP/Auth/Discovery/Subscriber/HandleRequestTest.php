<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\Discovery\Subscriber;

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
	 * Test the discovery document is not served when the OAuth server is disabled.
	 *
	 * @return void
	 */
	public function testShouldNotHandleRequestWhenDisabled(): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( false );

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

		$this->endpoints->shouldReceive( 'handle_request' )->once();

		$this->subscriber->handle_request();
	}
}

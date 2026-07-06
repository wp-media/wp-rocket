<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\Discovery\Subscriber;

use Mockery;
use WP_Rocket\Engine\MCP\Auth\Discovery\Endpoints;
use WP_Rocket\Engine\MCP\Auth\Discovery\Subscriber;
use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\MCP\Auth\Discovery\Subscriber::add_rewrite_rules()
 *
 * @group MCP
 */
class AddRewriteRulesTest extends TestCase {
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
	 * Test rewrite rules are not registered when the OAuth server is disabled.
	 *
	 * @return void
	 */
	public function testShouldNotAddRewriteRulesWhenDisabled(): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( false );

		$this->endpoints->shouldNotReceive( 'add_rewrite_rules' );

		$this->subscriber->add_rewrite_rules();
	}

	/**
	 * Test rewrite rules are registered when the OAuth server is enabled.
	 *
	 * @return void
	 */
	public function testShouldAddRewriteRulesWhenEnabled(): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( true );

		$this->endpoints->shouldReceive( 'add_rewrite_rules' )->once();

		$this->subscriber->add_rewrite_rules();
	}
}

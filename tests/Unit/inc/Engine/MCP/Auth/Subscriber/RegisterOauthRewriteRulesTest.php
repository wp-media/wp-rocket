<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\MCP\Auth\AuthorizeCallback;
use WP_Rocket\Engine\MCP\Auth\AuthorizeEndpoint;
use WP_Rocket\Engine\MCP\Auth\ConsentEndpoint;
use WP_Rocket\Engine\MCP\Auth\RevokeEndpoint;
use WP_Rocket\Engine\MCP\Auth\Subscriber;
use WP_Rocket\Engine\MCP\Auth\TokenEndpoint;
use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\MCP\Auth\Subscriber::register_oauth_rewrite_rules()
 *
 * @group MCP
 */
class RegisterOauthRewriteRulesTest extends TestCase {
	/**
	 * Context mock.
	 *
	 * @var Context|Mockery\MockInterface
	 */
	private $context;

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

		$this->subscriber = new Subscriber(
			Mockery::mock( AuthorizeEndpoint::class ),
			Mockery::mock( AuthorizeCallback::class ),
			Mockery::mock( TokenEndpoint::class ),
			Mockery::mock( ConsentEndpoint::class ),
			Mockery::mock( RevokeEndpoint::class ),
			$this->context
		);
	}

	/**
	 * Test rewrite rules are not registered when the OAuth server is disabled.
	 *
	 * @return void
	 */
	public function testShouldNotRegisterRewriteRulesWhenDisabled(): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( false );

		Functions\expect( 'add_rewrite_rule' )->never();

		$this->subscriber->register_oauth_rewrite_rules();
	}

	/**
	 * Test all five rewrite rules are registered when the OAuth server is enabled.
	 *
	 * @return void
	 */
	public function testShouldRegisterAllRewriteRulesWhenEnabled(): void {
		$this->context->shouldReceive( 'is_enabled' )->once()->andReturn( true );

		Functions\expect( 'add_rewrite_rule' )->times( 5 );

		$this->subscriber->register_oauth_rewrite_rules();
	}
}

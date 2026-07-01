<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use WP_Rocket\Engine\MCP\Auth\AuthorizeCallback;
use WP_Rocket\Engine\MCP\Auth\AuthorizeEndpoint;
use WP_Rocket\Engine\MCP\Auth\ConsentEndpoint;
use WP_Rocket\Engine\MCP\Auth\RevokeEndpoint;
use WP_Rocket\Engine\MCP\Auth\Subscriber;
use WP_Rocket\Engine\MCP\Auth\TokenEndpoint;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group MCP
 */
class Test_RegisterOauthRewriteRules extends TestCase {
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->subscriber = new Subscriber(
			Mockery::mock( AuthorizeEndpoint::class ),
			Mockery::mock( AuthorizeCallback::class ),
			Mockery::mock( TokenEndpoint::class ),
			Mockery::mock( ConsentEndpoint::class ),
			Mockery::mock( RevokeEndpoint::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Filters\expectApplied( 'rocket_mcp_oauth_server_enabled' )
			->andReturn( $config['enabled'] );

		Functions\expect( 'add_rewrite_rule' )->times( $expected['called'] );

		$this->subscriber->register_oauth_rewrite_rules();
	}
}

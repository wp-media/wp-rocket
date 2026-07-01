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
class Test_HandleOauthRequest extends TestCase {
	private $subscriber;
	private $token_endpoint;

	public function set_up() {
		parent::set_up();

		$this->token_endpoint = Mockery::mock( TokenEndpoint::class );

		$this->subscriber = new Subscriber(
			Mockery::mock( AuthorizeEndpoint::class ),
			Mockery::mock( AuthorizeCallback::class ),
			$this->token_endpoint,
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

		if ( ! $config['enabled'] ) {
			Functions\expect( 'get_query_var' )->never();
			$this->subscriber->handle_oauth_request();
			return;
		}

		Functions\expect( 'get_query_var' )
			->with( Subscriber::OAUTH_QUERY_VAR, '' )
			->andReturn( $config['query_var'] );

		$this->token_endpoint->expects()->handle_request()->once();

		$this->subscriber->handle_oauth_request();
	}
}

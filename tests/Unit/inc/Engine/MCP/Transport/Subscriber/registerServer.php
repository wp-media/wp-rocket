<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Transport\Subscriber;

use Mockery;
use Brain\Monkey\Filters;
use WP_Rocket\Engine\MCP\Transport\Server;
use WP_Rocket\Engine\MCP\Transport\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group MCP
 */
class Test_RegisterServer extends TestCase {
	private $subscriber;
	private $server;

	public function set_up() {
		parent::set_up();

		$this->server = Mockery::mock( Server::class );

		$this->subscriber = new Subscriber( $this->server );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Filters\expectApplied( 'rocket_mcp_oauth_server_enabled' )
			->andReturn( $config['enabled'] );

		$this->server->expects()
			->register_server()
			->times( $config['enabled'] ? 1 : 0 );

		$this->subscriber->register_server();
	}
}

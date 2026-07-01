<?php

declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\Discovery\Subscriber;

use Mockery;
use Brain\Monkey\Filters;
use WP_Rocket\Engine\MCP\Auth\Discovery\Endpoints;
use WP_Rocket\Engine\MCP\Auth\Discovery\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group MCP
 */
class Test_HandleRequest extends TestCase {
	private $subscriber;
	private $endpoints;

	public function set_up() {
		parent::set_up();

		$this->endpoints = Mockery::mock( Endpoints::class );

		$this->subscriber = new Subscriber( $this->endpoints );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Filters\expectApplied( 'rocket_mcp_oauth_server_enabled' )
			->andReturn( $config['enabled'] );

		$this->endpoints->expects()
			->handle_request()
			->times( $config['enabled'] ? 1 : 0 );

		$this->subscriber->handle_request();
	}
}

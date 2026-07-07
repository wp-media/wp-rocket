<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\MCP\Auth\Discovery\Endpoints;

use WP_Rocket\Engine\MCP\Auth\Discovery\Endpoints;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\MCP\Auth\Discovery\Endpoints::activate
 *
 * @group MCP
 */
class ActivateTest extends TestCase {
	public function testShouldSetCorrectHooks() {
		$endpoints = new Endpoints();

		$endpoints->activate();

		$this->assertNotFalse(
			has_action( 'rocket_activation', [ $endpoints, 'add_rewrite_rules' ] )
		);
	}
}

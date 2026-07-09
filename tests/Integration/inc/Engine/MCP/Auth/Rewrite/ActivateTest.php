<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\MCP\Auth\Rewrite;

use WP_Rocket\Engine\MCP\Auth\Rewrite;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\MCP\Auth\Rewrite::activate
 *
 * @group MCP
 */
class ActivateTest extends TestCase {
	public function testShouldSetCorrectHooks() {
		$rewrite = new Rewrite();

		$rewrite->activate();

		$this->assertNotFalse(
			has_action( 'rocket_activation', [ $rewrite, 'register_oauth_rewrite_rules' ] )
		);
	}
}

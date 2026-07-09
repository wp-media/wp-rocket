<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\MCP\Auth\SecretManager;

use WP_Rocket\Engine\MCP\Auth\SecretManager;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\MCP\Auth\SecretManager::activate
 *
 * @group MCP
 */
class ActivateTest extends TestCase {
	public function testShouldSetCorrectHooks() {
		$secret_manager = new SecretManager();

		$secret_manager->activate();

		$this->assertNotFalse(
			has_action( 'rocket_activation', [ SecretManager::class, 'ensure_secret' ] )
		);
	}
}

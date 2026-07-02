<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\MCP\Auth;

use WP_Rocket\Engine\MCP\Auth\AuthorizeCallback;
use WP_Rocket\Engine\MCP\Auth\AuthorizeEndpoint;
use WP_Rocket\Engine\MCP\Auth\CimdResolver;
use WP_Rocket\Engine\MCP\Auth\ClaudeClientVerifier;
use WP_Rocket\Engine\MCP\Auth\ConsentEndpoint;
use WP_Rocket\Engine\MCP\Auth\RevokeEndpoint;
use WP_Rocket\Engine\MCP\Auth\Subscriber;
use WP_Rocket\Engine\MCP\Auth\TokenEndpoint;
use WP_Rocket\Engine\MCP\Context;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Integration tests asserting the OAuth rewrite rules respect the
 * `rocket_mcp_oauth_server_enabled` filter.
 *
 * The subscriber is instantiated directly (with its real collaborators) and
 * its rewrite-registration method invoked in isolation, rather than via
 * `do_action( 'init' )`. This test bootstrap never runs a real plugin
 * activation, so WP Rocket's BerlinDB custom tables (hooked to 'init' by
 * unrelated subscribers) are never created; calling the full 'init' action
 * more than once per process trips their own table-creation logic. Testing
 * the Subscriber method directly keeps this test scoped to the guard clause
 * under test without depending on that unrelated setup.
 *
 * @group MCP
 */
class OauthRewriteRulesTest extends TestCase {
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
	public function set_up() {
		parent::set_up();

		$verifier = new ClaudeClientVerifier();
		$resolver = new CimdResolver( $verifier );
		$context  = new Context();

		$this->subscriber = new Subscriber(
			new AuthorizeEndpoint( $resolver ),
			new AuthorizeCallback(),
			new TokenEndpoint(),
			new ConsentEndpoint(),
			new RevokeEndpoint(),
			$context
		);

		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = [];
	}

	/**
	 * Tear down the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_all_filters( 'rocket_mcp_oauth_server_enabled' );

		global $wp_rewrite;
		$wp_rewrite->extra_rules_top = [];

		parent::tear_down();
	}

	/**
	 * Test the OAuth rewrite rules are present by default (filter unset).
	 *
	 * @return void
	 */
	public function testShouldRegisterRewriteRulesByDefault(): void {
		$this->subscriber->register_oauth_rewrite_rules();

		global $wp_rewrite;

		$this->assertArrayHasKey( '^oauth/authorize$', $wp_rewrite->extra_rules_top );
		$this->assertArrayHasKey( '^oauth/authorize-callback$', $wp_rewrite->extra_rules_top );
		$this->assertArrayHasKey( '^oauth/token$', $wp_rewrite->extra_rules_top );
		$this->assertArrayHasKey( '^oauth/consent$', $wp_rewrite->extra_rules_top );
		$this->assertArrayHasKey( '^oauth/revoke$', $wp_rewrite->extra_rules_top );
	}

	/**
	 * Test the OAuth rewrite rules are absent when the filter disables the server.
	 *
	 * @return void
	 */
	public function testShouldNotRegisterRewriteRulesWhenDisabled(): void {
		add_filter( 'rocket_mcp_oauth_server_enabled', '__return_false' );

		$this->subscriber->register_oauth_rewrite_rules();

		global $wp_rewrite;

		$this->assertArrayNotHasKey( '^oauth/authorize$', $wp_rewrite->extra_rules_top );
		$this->assertArrayNotHasKey( '^oauth/authorize-callback$', $wp_rewrite->extra_rules_top );
		$this->assertArrayNotHasKey( '^oauth/token$', $wp_rewrite->extra_rules_top );
		$this->assertArrayNotHasKey( '^oauth/consent$', $wp_rewrite->extra_rules_top );
		$this->assertArrayNotHasKey( '^oauth/revoke$', $wp_rewrite->extra_rules_top );
	}
}

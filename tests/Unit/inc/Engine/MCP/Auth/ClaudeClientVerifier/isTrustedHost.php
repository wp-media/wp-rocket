<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\ClaudeClientVerifier;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\MCP\Auth\ClaudeClientVerifier;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ClaudeClientVerifier::is_trusted_host
 *
 * @group MCP
 * @group Auth
 */
class Test_IsTrustedHost extends TestCase {
	/**
	 * Instance under test.
	 *
	 * @var ClaudeClientVerifier
	 */
	private $verifier;

	/**
	 * Default trusted-publisher allowlist, mirroring get_trusted_publishers()'s default.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private $default_trusted_publishers;

	/**
	 * Sets up the test fixtures.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'wp_parse_url' )->alias(
			function ( $url, $component = -1 ) {
				return parse_url( $url, $component );
			}
		);

		$this->default_trusted_publishers = [
			'claude' => [
				'client_ids' => [
					'https://claude.ai/oauth/claude-code-client-metadata',
					'https://claude.ai/oauth/mcp-oauth-client-metadata',
				],
				'host'       => 'claude.ai',
			],
		];

		$this->verifier = new ClaudeClientVerifier();
	}

	/**
	 * The default claude.ai host is trusted when the rocket_mcp_trusted_publishers filter
	 * has no listeners.
	 *
	 * @return void
	 */
	public function testShouldTrustDefaultClaudeHostWhenNoFilterListener() {
		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $this->default_trusted_publishers );

		$this->assertTrue( $this->verifier->is_trusted_host( 'https://claude.ai/oauth/claude-code-client-metadata' ) );
	}

	/**
	 * A host not present anywhere in the default allowlist is rejected.
	 *
	 * @return void
	 */
	public function testShouldNotTrustUnlistedHostWhenNoFilterListener() {
		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $this->default_trusted_publishers );

		$this->assertFalse( $this->verifier->is_trusted_host( 'https://example.com/oauth/client-metadata' ) );
	}

	/**
	 * A host belonging to a publisher added via the rocket_mcp_trusted_publishers filter
	 * is trusted.
	 *
	 * @return void
	 */
	public function testShouldTrustHostAddedViaFilter() {
		$filtered_trusted_publishers         = $this->default_trusted_publishers;
		$filtered_trusted_publishers['acme'] = [
			'client_ids' => [
				'https://acme.example/oauth/client-metadata',
			],
			'host'       => 'acme.example',
		];

		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $filtered_trusted_publishers );

		$this->assertTrue( $this->verifier->is_trusted_host( 'https://acme.example/oauth/client-metadata' ) );
	}

	/**
	 * A host not present in the default or filtered list is rejected.
	 *
	 * @return void
	 */
	public function testShouldNotTrustHostNotPresentInFilteredList() {
		$filtered_trusted_publishers         = $this->default_trusted_publishers;
		$filtered_trusted_publishers['acme'] = [
			'client_ids' => [
				'https://acme.example/oauth/client-metadata',
			],
			'host'       => 'acme.example',
		];

		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $filtered_trusted_publishers );

		$this->assertFalse( $this->verifier->is_trusted_host( 'https://not-trusted.example/oauth/client-metadata' ) );
	}
}

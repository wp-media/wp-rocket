<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\ClaudeClientVerifier;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\MCP\Auth\ClaudeClientVerifier;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ClaudeClientVerifier::verify
 *
 * @group MCP
 * @group Auth
 */
class Test_Verify extends TestCase {
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
		Functions\when( 'wp_json_encode' )->alias(
			function ( $data, $options = 0, $depth = 512 ) {
				return json_encode( $data, $options, $depth );
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
	 * Existing behaviour is unchanged when the rocket_mcp_trusted_publishers filter has no
	 * listeners: the default claude.ai client_id still verifies successfully.
	 *
	 * @return void
	 */
	public function testShouldVerifyDefaultClaudePublisherWhenNoFilterListener() {
		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $this->default_trusted_publishers );

		$doc = [ 'token_endpoint_auth_method' => 'none' ];

		$result = $this->verifier->verify( 'https://claude.ai/oauth/claude-code-client-metadata', $doc );

		$this->assertTrue( $result['verified'] );
		$this->assertSame( 'claude', $result['publisher'] );
	}

	/**
	 * A client_id that is in neither the default nor a filtered-in list is rejected.
	 *
	 * @return void
	 */
	public function testShouldNotVerifyUnlistedClientIdWhenNoFilterListener() {
		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $this->default_trusted_publishers );

		$doc = [ 'token_endpoint_auth_method' => 'none' ];

		$result = $this->verifier->verify( 'https://example.com/oauth/client-metadata', $doc );

		$this->assertFalse( $result['verified'] );
		$this->assertSame( '', $result['publisher'] );
	}

	/**
	 * A publisher added via the rocket_mcp_trusted_publishers filter verifies successfully.
	 *
	 * @return void
	 */
	public function testShouldVerifyPublisherAddedViaFilter() {
		$filtered_trusted_publishers         = $this->default_trusted_publishers;
		$filtered_trusted_publishers['acme'] = [
			'client_ids' => [
				'https://acme.example/oauth/client-metadata',
			],
			'host'       => 'acme.example',
		];

		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $filtered_trusted_publishers );

		$doc = [ 'token_endpoint_auth_method' => 'none' ];

		$result = $this->verifier->verify( 'https://acme.example/oauth/client-metadata', $doc );

		$this->assertTrue( $result['verified'] );
		$this->assertSame( 'acme', $result['publisher'] );
	}

	/**
	 * A client_id not present in the filtered (extended) list still returns verified false.
	 *
	 * @return void
	 */
	public function testShouldNotVerifyClientIdNotPresentInFilteredList() {
		$filtered_trusted_publishers         = $this->default_trusted_publishers;
		$filtered_trusted_publishers['acme'] = [
			'client_ids' => [
				'https://acme.example/oauth/client-metadata',
			],
			'host'       => 'acme.example',
		];

		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $filtered_trusted_publishers );

		$doc = [ 'token_endpoint_auth_method' => 'none' ];

		$result = $this->verifier->verify( 'https://not-trusted.example/oauth/client-metadata', $doc );

		$this->assertFalse( $result['verified'] );
		$this->assertSame( '', $result['publisher'] );
	}
}

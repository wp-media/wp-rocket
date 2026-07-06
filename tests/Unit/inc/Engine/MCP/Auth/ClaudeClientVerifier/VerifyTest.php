<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\ClaudeClientVerifier;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\MCP\Auth\ClaudeClientVerifier;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ClaudeClientVerifier::verify
 *
 * @group MCP
 * @group Auth
 */
class VerifyTest extends TestCase {
	/**
	 * Instance under test.
	 *
	 * @var ClaudeClientVerifier
	 */
	private $verifier;

	/**
	 * Sets up the test fixtures.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->stubWpParseUrl();

		Functions\when( 'wp_json_encode' )->alias(
			function ( $data, $options = 0, $depth = 512 ) {
				return json_encode( $data, $options, $depth );
			}
		);

		$this->verifier = new ClaudeClientVerifier();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $trusted_publishers, $client_id, $expected ) {
		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $trusted_publishers );

		$doc = [ 'token_endpoint_auth_method' => 'none' ];

		$result = $this->verifier->verify( $client_id, $doc );

		$this->assertSame( $expected['verified'], $result['verified'] );
		$this->assertSame( $expected['publisher'], $result['publisher'] );
	}
}

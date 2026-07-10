<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\MCP\Auth\ClaudeClientVerifier;

use Brain\Monkey\Filters;
use WP_Rocket\Engine\MCP\Auth\ClaudeClientVerifier;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ClaudeClientVerifier::is_trusted_host
 *
 * @group MCP
 * @group Auth
 */
class IsTrustedHostTest extends TestCase {
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

		$this->verifier = new ClaudeClientVerifier();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $trusted_publishers, $host, $expected ) {
		Filters\expectApplied( 'rocket_mcp_trusted_publishers' )
			->andReturn( $trusted_publishers );

		$this->assertSame( $expected, $this->verifier->is_trusted_host( $host ) );
	}
}

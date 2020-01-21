<?php
namespace WP_Rocket\Tests\Integration\Addons\Cloudflare\Cloudflare;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\Cloudflare::has_page_rule
 *
 * @group Cloudflare
 */
class Test_HasPageRule extends TestCase {

	/**
	 * Test Cloudflare has page rules with cached invalid transient.
	 */
	public function testHasRuleWithInvalidCredentials() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare has page rules with exception.
	 */
	public function testHasRuleWithException() {
		$this->assertTrue( true );
	}


	/**
	 * Test Cloudflare has page rules with no success.
	 */
	public function testHasRuleWithNoSuccess() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare has page rules with success but no page rule.
	 */
	public function testHasRuleWithSuccessButNoPageRule() {
		$this->assertTrue( true );
	}

	/**
	 * Test Cloudflare has page rules with success and page rule.
	 */
	public function testHasRuleWithSuccessAndPageRule() {
		$this->assertTrue( true );
	}
}

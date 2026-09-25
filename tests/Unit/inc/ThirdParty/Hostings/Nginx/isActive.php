<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Nginx;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Nginx;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Nginx::is_active
 *
 * @group Nginx
 * @group ThirdParty
 * @group Hostings
 */
class Test_IsActive extends TestCase {
	protected function tearDown(): void {
		global $is_nginx;
		$is_nginx = null;

		parent::tearDown();
	}

	public function testShouldReturnFalseWhenIsNginxIsEmpty() {
		global $is_nginx;
		$is_nginx = false;

		$this->assertFalse( Nginx::is_active() );
	}

	public function testShouldReturnFalseWhenIsNginxIsUnset() {
		global $is_nginx;
		unset( $is_nginx );

		$this->assertFalse( Nginx::is_active() );
	}

	public function testShouldReturnTrueWhenIsNginxIsTrue() {
		global $is_nginx;
		$is_nginx = true;

		$this->assertTrue( Nginx::is_active() );
	}
}

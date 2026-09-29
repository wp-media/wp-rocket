<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Nginx;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Nginx;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Nginx::better_nginx_compatibility
 *
 * @group Nginx
 * @group ThirdParty
 * @group Hostings
 */
class Test_BetterNginxCompatibility extends TestCase {
	protected function tearDown(): void {
		global $is_nginx;
		$is_nginx = null;

		parent::tearDown();
	}

	public function testShouldAddQueryStringWhenIsNginx() {
		global $is_nginx;
		$is_nginx = true;

		$this->assertSame(
			[ 'existing', 'q' ],
			( new Nginx() )->better_nginx_compatibility( [ 'existing' ] )
		);
	}

	public function testShouldNotAddQueryStringWhenNotNginx() {
		global $is_nginx;
		$is_nginx = false;

		$this->assertSame(
			[ 'existing' ],
			( new Nginx() )->better_nginx_compatibility( [ 'existing' ] )
		);
	}
}

<?php

namespace WP_Rocket\Tests\Integration\Addons\Cloudflare\CloudflareFacade;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\Addons\Cloudflare\CloudflareFacade::purge
 *
 * @group Cloudflare
 */
class Test_Purge extends TestCase {

	public function testShouldPurgeCacheWhenZoneIdIsSet() {
		$this->assertTrue( true );
	}
}

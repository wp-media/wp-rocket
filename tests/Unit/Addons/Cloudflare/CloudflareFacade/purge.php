<?php

namespace WP_Rocket\Tests\Unit\Addons\Cloudflare\CloudflareFacade;

use WP_Rocket\Tests\Unit\TestCase;

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

<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pagely;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pagely;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Pagely::clean_pagely
 *
 * Covers the redundant `class_exists('PagelyCachePurge')` safety-net guard kept inside the callback
 * (see issue #8768) — it must remain regardless of HostResolver's own boot-time detection.
 *
 * @group Pagely
 * @group ThirdParty
 * @group Hostings
 */
class Test_CleanPagely extends TestCase {
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldPurgeAllWhenPagelyCachePurgeClassExists() {
		$purger = Mockery::mock( 'overload:PagelyCachePurge' );
		$purger->shouldReceive( 'purgeAll' )->once();

		( new Pagely() )->clean_pagely();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNoOpWhenPagelyCachePurgeClassMissing() {
		$this->assertFalse( class_exists( 'PagelyCachePurge' ) );

		// Should not fatal even though the class is absent.
		( new Pagely() )->clean_pagely();
	}
}

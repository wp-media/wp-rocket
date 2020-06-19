<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Wpengine;

use WpeCommon;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::clean_wpengine
 *
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_CleanWpengine extends WpengineTestCase {
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		WpeCommon::resetCounters();
	}

	public function setUp() {
		parent::setUp();

		WpeCommon::resetCounters();
	}

	public function testShouldCleanWPEngine() {
		$this->wpengine->clean_wpengine();

		$this->assertEquals( 1, WpeCommon::getNumberTimesPurgeMemcachedCalled() );
		$this->assertEquals( 1, WpeCommon::getNumberTimesVarnishCacheCalled() );
	}
}

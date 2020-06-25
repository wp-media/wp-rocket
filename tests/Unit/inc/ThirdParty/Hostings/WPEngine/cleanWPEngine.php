<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPEngine;

use WpeCommon;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPEngine::clean_wpengine
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_CleanWPEngine extends WPEngineTestCase {
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

<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Wpengine;

use WpeCommon;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Wpengine::clean_wpengine
 *
 * @group  Wpengine
 * @group  ThirdParty
 */
class Test_CleanWpengine extends TestCase {

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		WpeCommon::resetCounters();
	}

	public function setUp() {
		parent::setUp();

		WpeCommon::resetCounters();
	}

	public function testShouldCleanWPEngine() {
		do_action( 'after_rocket_clean_domain' );

		$this->assertEquals( 1, WpeCommon::getNumberTimesPurgeMemcachedCalled() );
		$this->assertEquals( 1, WpeCommon::getNumberTimesVarnishCacheCalled() );
	}
}

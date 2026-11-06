<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPEngine;

use WpeCommon;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\WPEngine::clean_wpengine
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_CleanWPEngine extends TestCase {

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		WpeCommon::resetCounters();
	}

	public function set_up() {
		parent::set_up();

		WpeCommon::resetCounters();
	}

	public function testShouldCleanWPEngine() {
		do_action( 'rocket_after_clean_domain' );

		$this->assertEquals( 1, WpeCommon::getNumberTimesPurgeMemcachedCalled() );
		$this->assertEquals( 1, WpeCommon::getNumberTimesVarnishCacheCalled() );
	}
}

<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PWA;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PWA;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PWA::is_activated
 *
 * Reuses the WP_Rocket\ThirdParty\Plugins-namespaced function_exists()
 * override already declared at the bottom of excludeServiceWorker.php: PHP
 * forbids declaring the same namespaced function twice within one process,
 * and the full unit suite runs in a single process, so this file drives the
 * existing override through Test_ExcludeServiceWorker's static toggle
 * instead of redeclaring it.
 *
 * @group PWA
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	protected function tearDown(): void {
		Test_ExcludeServiceWorker::$function_exists = false;

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Test_ExcludeServiceWorker::$function_exists = $config['function_exists'];

		$this->assertSame( $expected, PWA::is_activated() );
	}
}

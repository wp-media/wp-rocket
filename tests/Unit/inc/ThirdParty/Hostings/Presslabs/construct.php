<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Presslabs;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Presslabs;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Presslabs::__construct
 *
 * @group Presslabs
 * @group ThirdParty
 * @group Hostings
 */
class Test_Construct extends TestCase {
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldRequireAdvancedCacheFileFromExpectedPath() {
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Presslabs' );
		}

		// The stub advanced-cache.php fixture is a no-op; the constructor must not fatal.
		new Presslabs();

		$this->assertFileExists( WP_CONTENT_DIR . '/advanced-cache.php' );
	}
}

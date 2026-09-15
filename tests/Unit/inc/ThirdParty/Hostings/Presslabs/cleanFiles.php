<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Presslabs;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Presslabs;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Presslabs::clean_files
 *
 * @group Presslabs
 * @group ThirdParty
 * @group Hostings
 */
class Test_CleanFiles extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Presslabs' );
		}
	}

	public function testShouldForwardToRocketCleanFiles() {
		Functions\expect( 'rocket_clean_files' )->once()->with( [ 'http://example.org/' ] );

		( new Presslabs() )->clean_files( [ 'http://example.org/' ] );
	}
}

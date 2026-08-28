<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Presslabs;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Presslabs;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Presslabs::add_pl_cdn
 *
 * @group Presslabs
 * @group ThirdParty
 * @group Hostings
 */
class Test_AddPlCdn extends TestCase {
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldAppendPlCdnHostToHostsList() {
		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Presslabs' );
		}
		if ( ! defined( 'PL_CDN_HOST' ) ) {
			define( 'PL_CDN_HOST', 'cdn.example.com' );
		}

		$this->assertSame(
			[ 'existing.example.com', 'cdn.example.com' ],
			( new Presslabs() )->add_pl_cdn( [ 'existing.example.com' ] )
		);
	}
}

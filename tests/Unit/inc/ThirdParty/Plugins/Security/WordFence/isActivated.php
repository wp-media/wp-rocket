<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Security\WordFence;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility::is_activated
 *
 * @group  WordFence
 * @group  ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests WordFenceCompatibility::is_activated() against the presence/absence of WORDFENCE_VERSION.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_wordfence_version'] ) {
			define( 'WORDFENCE_VERSION', '7.11.0' );
		}

		$this->assertSame( $expected, WordFenceCompatibility::is_activated() );
	}
}

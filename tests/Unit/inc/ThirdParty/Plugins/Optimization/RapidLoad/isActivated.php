<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\RapidLoad;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\RapidLoad::is_activated
 *
 * @group RapidLoad
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests RapidLoad::is_activated() against the presence/absence of UUCSS_VERSION.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_uucss_version'] ) {
			define( 'UUCSS_VERSION', '1.6.34' );
		}

		$this->assertSame( $expected, RapidLoad::is_activated() );
	}
}

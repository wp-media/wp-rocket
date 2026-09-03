<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\AllInOneSEOPack;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\AllInOneSEOPack::is_activated
 *
 * @group AllInOneSEOPack
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests AllInOneSEOPack::is_activated() against the v3/v4 constants and the aioseo() function.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_aioseop_version'] ) {
			define( 'AIOSEOP_VERSION', '3.7.0' );
		}

		if ( $config['define_aioseo_version'] ) {
			define( 'AIOSEO_VERSION', '4.7.0' );
		}

		if ( $config['define_aioseo_function'] ) {
			Functions\when( 'aioseo' )->justReturn( true );
		}

		$this->assertSame( $expected, AllInOneSEOPack::is_activated() );
	}
}

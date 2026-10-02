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
	 * Runs in a separate process because the aioseo() presence is simulated with a function
	 * stub that declares a real global function which cannot be undeclared; without isolation
	 * it would leak into the "aioseo absent" data sets and into the sibling
	 * addAllInOneSeoSitemap test, which relies on its own aioseo() stub.
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
			$this->constants['AIOSEOP_VERSION'] = '3.7.0';
		}

		if ( $config['define_aioseo_version'] ) {
			$this->constants['AIOSEO_VERSION'] = '4.7.0';
		}

		if ( $config['define_aioseo_function'] ) {
			Functions\when( 'aioseo' )->justReturn( true );
		}

		$this->assertSame( $expected, AllInOneSEOPack::is_activated() );
	}
}

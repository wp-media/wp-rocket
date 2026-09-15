<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Rating\KKStarRatings;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Rating\KKStarRatings;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Rating\KKStarRatings::is_activated
 *
 * eval() declares BhittaniPlugin_kkStarRatings in the global namespace for the present
 * case; @runInSeparateProcess keeps it isolated. A namespaced class_exists() override is
 * avoided because it would break PHPStan's class-exists narrowing for other guards.
 *
 * @group KKStarRatings
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests is_activated() against the presence/absence of the BhittaniPlugin_kkStarRatings class.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_kksr'] ) {
			eval( 'class BhittaniPlugin_kkStarRatings {}' );
		}

		$this->assertSame( $expected, KKStarRatings::is_activated() );
	}
}

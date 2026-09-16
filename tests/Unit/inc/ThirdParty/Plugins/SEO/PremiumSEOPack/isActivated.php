<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\PremiumSEOPack;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\PremiumSEOPack;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\PremiumSEOPack::is_activated
 *
 * A namespaced class_exists() override is avoided here: declaring one in the analyzed
 * fileset makes PHPStan resolve every unqualified class_exists() in the namespace to it
 * instead of the builtin, breaking its class-exists narrowing for unrelated guards.
 * eval() + @runInSeparateProcess keeps the defined class isolated to its own process.
 *
 * @group PremiumSEOPack
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests PremiumSEOPack::is_activated() against the presence/absence of the psp class.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_psp'] ) {
			eval( 'class psp {}' );
		}

		$this->assertSame( $expected, PremiumSEOPack::is_activated() );
	}
}

<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Security\WordFence;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility;
use wordfence;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility::whitelist_wordfence_firewall_ips
 *
 * @group  WordFence
 * @group  ThirdParty
 */
class Test_WordFenceWhitelistIPs extends TestCase {

	public function setUp() : void {
		parent::setup();
		require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/Security/WordFence/wordfence.php';
		require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/Security/WordFence/wfConfig.php';
		
		wordfence::$white_listed_ips =[];
		$this->WordFenceCompatibility        = new WordFenceCompatibility();
	}
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddWitelistIPs( $expected ) {

		Filters\expectApplied( 'rocket_wordfence_whitelisted_ips')->with($expected)->once()
			->andReturn( $expected );

		$this->WordFenceCompatibility->whitelist_wordfence_firewall_ips();

		$this->assertEquals( $expected, wordfence::getWhiteListedIPs() );

	}
	public function providerTestData() {
		return $this->getTestData( __DIR__, 'whitelistIPs' );
	}
}

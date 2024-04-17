<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Security\WordFence;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility;
use wfConfig;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility::pop_ip_from_whitelist
 *
 * @group  WordFence
 * @group  ThirdParty
 */
class Test_popIpFromWhitelist extends TestCase {

    protected $wordFenceCompatibility;

	public function setUp() : void {
		parent::setup();
        
		require WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Plugins/Security/WordFence/wfConfig.php';
		
		$this->wordFenceCompatibility = new WordFenceCompatibility();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldPopIpFromWhitelist( $whitelisted, $expected ) {

        wfConfig::$whitelisted['whitelisted'] = $whitelisted;

		$this->wordFenceCompatibility->pop_ip_from_whitelist();

		$this->assertEquals( $expected, wfConfig::get( 'whitelisted' ) );

	}

    public function providerTestData() {
		return $this->getTestData( __DIR__, 'popIpFromWhitelist' );
	}
}

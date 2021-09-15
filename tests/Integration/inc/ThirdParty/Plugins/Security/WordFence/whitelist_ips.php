<?php

namespace WP_Rocket\ThirdParty\Plugins\Security\WordFence;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility;
use wordfence;
use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Security\WordFenceCompatibility::whitelist_wordfence_firewall_ips
 *
 * @group  WordFence
 * @group  ThirdParty
 */
class Test_WordFence_Whitelist extends TestCase {

	public function setUp() : void {

		parent::setup();
		wordfence::$white_listed_ips =[];
		$this->WordFenceCompatibility        = new WordFenceCompatibility();
	}

	public function testShouldAddWitelistIPs() {


		$ips=['135.125.83.227'];


		$this->WordFenceCompatibility->whitelist_wordfence_firewall_ips();

		$this->assertEquals( apply_filters( 'rocket_varnish_ip', $ips ), wordfence::getWhiteListedIPs() );

	}
}

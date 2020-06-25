<?php

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SpinUpWP;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\SpinUpWP::purge_site
 *
 * @group  SpinUpWP
 * @group  ThirdParty
 */
class Test_PurgeSite extends TestCase {

	public function testShouldCallSpinUpPurgeSite() {
		Functions\expect( 'spinupwp_purge_site' )
			->once();

		$spinup = new SpinUpWP();
		$spinup->purge_site();
	}

}

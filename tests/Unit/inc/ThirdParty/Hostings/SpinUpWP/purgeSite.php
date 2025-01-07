<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SpinUpWP;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SpinUpWP;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SpinUpWP::purge_site
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

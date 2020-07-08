<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\SpinUpWP;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\SpinUpWP::purge_site
 *
 * @group  SpinUpWP
 * @group  ThirdParty
 */
class Test_PurgeSite extends TestCase {

	public function testShouldCallSpinUpPurgeSite( ) {
		Functions\expect( 'spinupwp_purge_site' )->once();

		do_action( 'after_rocket_clean_domain' );
	}

}

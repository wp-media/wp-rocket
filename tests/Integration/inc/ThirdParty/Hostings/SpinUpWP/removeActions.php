<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\SpinUpWP;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\SpinUpWP::remove_actions
 *
 * @group  SpinUpWP
 * @group  ThirdParty
 */
class Test_RemoveActions extends TestCase {

	public function testShouldRemoveRocketRegisteredActions() {

		Functions\expect( 'rocket_clean_domain' )->never();

		switch_theme('twentynineteen/style.css');

	}

}

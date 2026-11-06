<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SpinUpWP;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SpinUpWP;
use Brain\Monkey\Actions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SpinUpWP::remove_actions
 *
 * @group  SpinUpWP
 * @group  ThirdParty
 */
class Test_RemoveActions extends TestCase {

	public function testShouldRemoveRocketRegisteredActions() {
		Actions\expectRemoved('switch_theme')->once()->with('rocket_clean_domain');
		$spinup = new SpinUpWP();
		$spinup->remove_actions();
	}

}

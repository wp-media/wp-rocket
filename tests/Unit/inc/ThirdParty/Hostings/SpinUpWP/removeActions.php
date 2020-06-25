<?php

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SpinUpWP;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\SpinUpWP::remove_actions
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

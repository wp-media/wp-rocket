<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\SpinUpWP;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SpinUpWP::remove_actions
 *
 * @group SpinUpWP
 * @group ThirdParty
 */
class Test_RemoveActions extends TestCase {
	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		parent::tear_down();
	}

	public function testShouldRemoveRocketRegisteredActions() {

		Functions\expect( 'rocket_clean_domain' )->never();

		switch_theme( 'twentynineteen/style.css' );
	}
}

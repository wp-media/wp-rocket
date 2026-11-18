<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\SpinUpWP;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SpinUpWP::remove_actions
 *
 * @group SpinUpWP
 * @group ThirdParty
 */
class RemoveActionsTest extends TestCase {

	public function testShouldRemoveRocketRegisteredActions() {
		$this->assertFalse( has_filter( 'switch_theme', 'rocket_clean_domain' ) );
	}
}

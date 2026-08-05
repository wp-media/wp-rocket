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

	public static function set_up_before_class() {
		parent::set_up_before_class();

		// switch_theme triggers rocket_clean_domain, which truncates the optimization tables.
		self::installAtfTable();
		self::installLrcTable();
		self::installPreloadFontsTable();
		self::installPreconnectExternalDomainsTable();
	}

	public static function tear_down_after_class() {
		self::uninstallAtfTable();
		self::uninstallLrcTable();
		self::uninstallPreloadFontsTable();
		self::uninstallPreconnectDomainsTable();

		parent::tear_down_after_class();
	}

	public function testShouldRemoveRocketRegisteredActions() {

		Functions\expect( 'rocket_clean_domain' )->never();

		switch_theme( 'twentynineteen/style.css' );
	}
}

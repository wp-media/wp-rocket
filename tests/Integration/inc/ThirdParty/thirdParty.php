<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @group ThirdParty
 */
class Test_ThirdParty extends TestCase {
	private static $included_files;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$included_files = get_included_files();
	}

	public function testShouldNotLoadHostingFilesWhenNotPresent() {
		$this->assertNotContains( WP_ROCKET_3RD_PARTY_PATH . 'hosting/wpengine.php', self::$included_files );
		$this->assertNotContains( WP_ROCKET_3RD_PARTY_PATH . 'hosting/o2switch.php', self::$included_files );
		$this->assertNotContains( WP_ROCKET_3RD_PARTY_PATH . 'hosting/flywheel.php', self::$included_files );
		$this->assertNotContains( WP_ROCKET_3RD_PARTY_PATH . 'hosting/siteground.php', self::$included_files );
	}
}

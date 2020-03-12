<?php

namespace WP_Rocket\Tests\Integration\inc\main;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::rocket_activation
 * @group AdminOnly
 * @group Activation
 */
class Test_RocketActivation extends TestCase {
	private static $included_files;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		Functions\expect( 'rocket_get_constant' )
			->with( 'O2SWITCH_VARNISH_PURGE_KEY' )
			->andReturn( 'varnish_key' );

		self::$included_files = get_included_files();
	}

	public function testShouldNotLoadHostingGoDaddyWPEngine() {
		$this->assertFalse( in_array( WP_ROCKET_3RD_PARTY_PATH . 'hosting/godaddy.php', self::$included_files ) );
		$this->assertFalse( in_array( WP_ROCKET_3RD_PARTY_PATH . 'hosting/wpengine.php', self::$included_files ) );
	}

	public function testShouldLoadoHostingO2SWITCH() {
		$this->assertTrue( in_array( WP_ROCKET_3RD_PARTY_PATH . 'hosting/o2switch.php', self::$included_files ) );
	}

}

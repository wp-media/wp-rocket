<?php

namespace WP_Rocket\Tests\Unit\inc\API;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers ::rocket_bypass
 * @group API
 */
class Bypass extends TestCase {
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/API/bypass.php';
	}

	public function setUp() {
		parent::setUp();

		$this->stubWpParseUrl();
	}

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp'] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $wp, $url, $expected ) {
		$GLOBALS['wp'] = $wp;

		Functions\when( 'add_query_arg' )->justReturn( $url );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

		if ( $expected ) {
			$this->assertTrue( rocket_bypass() );
		} else {
			$this->assertFalse( rocket_bypass() );
		}
	}
}

<?php

namespace WP_Rocket\Tests\Unit\inc\API;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers ::rocket_bypass
 * @group API
 */
class Bypass extends TestCase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/API/bypass.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$this->stubWpParseUrl();
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wp'] );

		parent::tearDown();
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

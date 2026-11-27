<?php

namespace WP_Rocket\Tests\Unit\inc\functions\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_need_api_key
 *
 * @group Functions
 * @group Admin
 */
class Test_RocketNeedApiKey extends TestCase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Required: the notice echoes the bare WP_ROCKET_PLUGIN_NAME constant, which the
		// base TestCase constant stubbing (rocket_get_constant) does not cover.
		if ( ! defined( 'WP_ROCKET_PLUGIN_NAME' ) ) {
			define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
		}

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/admin.php';
	}

	public function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();
		Functions\when( 'wp_kses_post' )->returnArg();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderExpected( $errors, $contains, $not_contains ) {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_check_key_errors' )
			->andReturn( $errors );

		ob_start();
		rocket_need_api_key();
		$output = ob_get_clean();

		foreach ( $contains as $needle ) {
			$this->assertStringContainsString( $needle, $output );
		}

		foreach ( $not_contains as $needle ) {
			$this->assertStringNotContainsString( $needle, $output );
		}
	}
}

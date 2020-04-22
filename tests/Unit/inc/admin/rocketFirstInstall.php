<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_first_install
 * @group admin
 * @group upgrade
 * @group AdminOnly
 */
class Test_RocketFirstInstall extends TestCase {
	public function setUp() {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/upgrader.php';
	}

	/**
	 * @dataProvider addProvider
	 */
	public function testShouldAddOption( $expected ) {
		$uniqids = [
			'secret_cache_key' => $expected['secret_cache_key'],
			'minify_css_key'   => $expected['minify_css_key'],
			'minify_js_key'    => $expected['minify_js_key'],
		];

		Functions\expect( 'create_rocket_uniqid' )
			->times( 3 )
			->andReturnValues( $uniqids );
		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_SLUG' )
			->andReturn( 'wp_rocket_settings' );
		Functions\expect( 'add_option' )
			->once()
			->with( 'wp_rocket_settings', $expected );
		Functions\expect( 'rocket_dismiss_box' )
			->once()
			->with( 'rocket_warning_plugin_modification' );

		rocket_first_install();

		$this->assertSame( 1, Filters\applied( 'rocket_first_install_options' ) );
	}

	public function addProvider() {
		return $this->getTestData( __DIR__, 'rocketFirstInstall' );
	}
}

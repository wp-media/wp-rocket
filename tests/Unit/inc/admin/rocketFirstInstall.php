<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_first_install
 * @group admin
 * @group upgrade
 * @group AdminOnly
 */
class Test_RocketFirstInstall extends TestCase {
	public function setUp() : void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/upgrader.php';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddOption( $expected ) {
		$uniqids = [
			'secret_cache_key' => $expected['unit']['secret_cache_key'],
			'minify_css_key'   => $expected['unit']['minify_css_key'],
			'minify_js_key'    => $expected['unit']['minify_js_key'],
		];

		Functions\expect( 'create_rocket_uniqid' )
			->times( 3 )
			->andReturnValues( array_values( $uniqids ) );
		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_SLUG' )
			->andReturn( 'wp_rocket_settings' );
		Functions\expect( 'add_option' )
			->once()
			->with( 'wp_rocket_settings', $expected['unit'] );
		Functions\expect( 'rocket_dismiss_box' )
			->once()
			->with( 'rocket_warning_plugin_modification' );

		rocket_first_install();

		$this->assertSame( 1, Filters\applied( 'rocket_first_install_options' ) );
	}
}

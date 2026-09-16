<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\WPOffloadS3Assets;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3Assets;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3Assets::as3cf_assets_compatibility
 *
 * @group WPOffloadS3Assets
 * @group ThirdParty
 */
class Test_As3cfAssetsCompatibility extends TestCase {
	protected function tearDown(): void {
		unset( $GLOBALS['as3cf_assets'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected outcome.
	 */
	public function testShouldDoExpected( $config, $expected ) {
		if ( $config['global_set'] ) {
			$as3cf_assets = Mockery::mock();
			$as3cf_assets->shouldReceive( 'is_plugin_setup' )->andReturn( $config['plugin_setup'] );
			$as3cf_assets->shouldReceive( 'get_setting' )->with( 'enable-addon' )->andReturn( $config['enable_addon'] );

			$GLOBALS['as3cf_assets'] = $as3cf_assets;
		}

		if ( $expected['filter_added'] ) {
			Functions\expect( 'add_filter' )->once()->with( 'rocket_readonly_cdn_option', '__return_true' );
		} else {
			Functions\expect( 'add_filter' )->never();
		}

		( new WPOffloadS3Assets() )->as3cf_assets_compatibility();
	}
}

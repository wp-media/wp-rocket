<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\WPOffloadS3;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3::as3cf_compatibility
 *
 * @group WPOffloadS3
 * @group ThirdParty
 */
class Test_As3cfCompatibility extends TestCase {
	protected function tearDown(): void {
		unset( $GLOBALS['as3cf'] );

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
			$as3cf = Mockery::mock();
			$as3cf->shouldReceive( 'is_plugin_setup' )->andReturn( $config['plugin_setup'] );
			$as3cf->shouldReceive( 'get_setting' )->with( 'serve-from-s3' )->andReturn( $config['serve_from_s3'] );

			$GLOBALS['as3cf'] = $as3cf;
		}

		if ( $expected['filter_added'] ) {
			Functions\expect( 'add_filter' )->once()->with( 'rocket_allow_cdn_images', '__return_false' );
		} else {
			Functions\expect( 'add_filter' )->never();
		}

		( new WPOffloadS3() )->as3cf_compatibility();
	}
}

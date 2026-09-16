<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\CDN\WPOffloadS3Assets;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3Assets;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\WPOffloadS3Assets::maybe_deactivate_cdn
 *
 * @group WPOffloadS3Assets
 * @group ThirdParty
 */
class Test_MaybeDeactivateCdn extends TestCase {
	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected outcome.
	 */
	public function testShouldDoExpected( $config, $expected ) {
		if ( $expected['cdn_disabled'] ) {
			Functions\expect( 'update_rocket_option' )->once()->with( 'cdn', 0 );
		} else {
			Functions\expect( 'update_rocket_option' )->never();
		}

		( new WPOffloadS3Assets() )->maybe_deactivate_cdn( $config['old_value'], $config['new_value'] );
	}
}

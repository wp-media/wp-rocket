<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_enable_cdn_option
 * @group OneCom
 */
class Test_MaybeUpdateCDNZone extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        $this->constants['vcaching'] = $config['onecom_performance_plugin_enabled'];

		if ( $config['onecom_performance_plugin_enabled'] ) {
			Functions\expect( 'rest_sanitize_boolean' )
				->once()
				->andReturn( $config['oc_cdn_enabled'] );

			Functions\when( 'get_option' )
				->alias( function( $value ) use( $config ) {
					if ( 'oc_cdn_enabled' === $value ) {
						return $config['oc_cdn_enabled'];
					}
				}
				);
		}

        $this->assertSame( 
            $expected['return'], 
            apply_filters( 'pre_get_rocket_option_cdn_zone', $config['zone'] ) 
        );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeUpdateCDNZone' );
	}
}

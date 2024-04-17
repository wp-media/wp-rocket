<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::disable_cdn_change
 * @group OneCom
 */
class Test_DisableCDNChange extends TestCase {

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
			$expected['field_settings'], apply_filters( 'rocket_cdn_settings_fields', $config['field_settings' ]));
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'disableCDNChange' );
	}
}


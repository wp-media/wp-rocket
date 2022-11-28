<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_set_varnish_addon_title
 * @group OneCom
 */
class Test_MaybeSetVarnishAddonTitle extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        Functions\expect( 'rest_sanitize_boolean' )
				->once()
				->andReturn( $config['is_varnish_active'] );

        Functions\when( 'get_option' )
			->alias( function( $value ) use( $config ) {
				if ( 'varnish_caching_enable' === $value ) {
                    return $config['is_varnish_active'];
                }
			}
		);

		$this->assertSame(
			$expected['title'], 
			apply_filters( 'rocket_varnish_field_settings', $config['varnish_field_settings'] )['varnish_auto_purge']['title'] 
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeSetVarnishAddonTitle' );
	}
}

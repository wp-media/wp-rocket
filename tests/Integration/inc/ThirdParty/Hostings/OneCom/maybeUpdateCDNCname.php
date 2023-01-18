<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_enable_cdn_option
 * @group OneCom
 */
class Test_MaybeUpdateCDNCname extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

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

        if ( $config['oc_cdn_enabled'] ) {
            Functions\expect( 'wp_unslash' )
                ->times( 2 )
                ->andReturn( $_SERVER[ 'ONECOM_DOMAIN_NAME'], $_SERVER[ 'HTTP_HOST'] );

            Functions\expect( 'sanitize_text_field' )
                ->times( 2 )
                ->andReturn( $_SERVER[ 'ONECOM_DOMAIN_NAME'], $_SERVER[ 'HTTP_HOST'] );
        }

        $this->assertSame( 
            $expected['return'], 
            apply_filters( 'pre_get_rocket_option_cdn_cnames', $config['cname'] ) 
        );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeUpdateCDNCname' );
	}
}

<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::exclude_from_cdn
 * @group OneCom
 */
class Test_ExcludeFromCDN extends TestCase {

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

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_cdn_reject_files', $config['excluded'] )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'excludeFromCDN' );
	}
}

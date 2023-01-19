<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\ThirdParty\Hostings\OneCom;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_remove_rocketcdn_status
 * @group OneCom
 * @group ThirdParty
 */
class Test_MaybeRemoveRocketCDNStatus extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        Functions\expect( 'rest_sanitize_boolean' )
				->once()
				->andReturn( $config['oc_cdn_enabled'] );

        Functions\expect( 'get_option' )
			->once()
			->with( 'oc_cdn_enabled' )
			->andReturn( $config['oc_cdn_enabled'] );

        $this->assertSame( $expected, apply_filters( 'rocket_display_rocketcdn_status', true ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeRemoveRocketCDNStatus' );
	}
}

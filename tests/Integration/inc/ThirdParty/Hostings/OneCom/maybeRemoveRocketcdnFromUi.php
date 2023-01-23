<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\ThirdParty\Hostings\OneCom;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_remove_rocketcdn_from_ui
 * @group OneCom
 * @group ThirdParty
 */
class Test_MaybeRemoveRocketcdnCtaBanner extends TestCase {

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

		if ( 'cta_banner' === $config['type'] ) {
			$this->assertSame( $expected, apply_filters( 'rocket_display_rocketcdn_cta', true ) );
		}

		if ( 'status' === $config['type'] ) {
			$this->assertSame( $expected, apply_filters( 'rocket_display_rocketcdn_status', true ) );
		}

		if ( 'notice' === $config['type'] ) {
			$this->assertSame( $expected, apply_filters( 'rocket_promote_rocketcdn_notice', true ) );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeRemoveRocketcdnFromUi' );
	}
}

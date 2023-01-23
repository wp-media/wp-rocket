<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\ThirdParty\Hostings\OneCom;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_remove_rocketcdn_from_ui
 * @group OneCom
 * @group ThirdParty
 */
class Test_MaybeRemoveRocketcdnFromUi extends TestCase {
    private $subscriber;

	public function setUp() : void {
		parent::setUp();

        $this->subscriber = new OneCom();
	}

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

        $this->assertSame( $expected, $this->subscriber->maybe_remove_rocketcdn_from_ui() );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeRemoveRocketcdnFromUi' );
	}
}

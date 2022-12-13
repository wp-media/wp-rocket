<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\ThirdParty\Hostings\OneCom;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::disable_cdn_change
 * @group OneCom
 * @group ThirdParty
 */
class Test_DisableCDNChange extends TestCase {
    private $onecom;

	public function setUp() : void {
		parent::setUp();
        
        $this->onecom = new OneCom();
	}

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
			$expected['field_settings'],
			$this->onecom->disable_cdn_change( $config['field_settings'] )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'disableCDNChange' );
	}
}

<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\ThirdParty\Hostings\OneCom;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_disable_cdn_option
 * @group OneCom
 * @group ThirdParty
 */
class Test_MaybeDisableCDNOption extends TestCase {
    private $onecom, $options_api, $option;

	public function setUp() : void {
		parent::setUp();

        $this->onecom = new OneCom();
	}

    public function tear_down() {
		parent::tear_down();

		// Reset after each test.
		unset( $_SERVER['ONECOM_DOMAIN_NAME'] );
		unset( $_SERVER['HTTP_HOST'] );
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

        if ( ! $config['oc_cdn_enabled'] && $config['cdn'] ) {

            Functions\expect( 'update_rocket_option' )
                ->with( 'cdn', $config['options']['cdn'] )
                ->andReturn( true );

            Functions\expect( 'update_rocket_option' )
                ->with( 'cdn_cnames', $config['options']['cdn_cnames'] )
                ->andReturn( true );

            Functions\expect( 'update_rocket_option' )
                ->with( 'cdn_zone', $config['options']['cdn_zones'] )
                ->andReturn( true );

            Functions\expect( 'rocket_clean_domain' )->once();
        }

        $this->assertSame( $expected['return'], $this->onecom->maybe_disable_cdn_option( $config['cdn'] ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeDisableCDNOption' );
	}
}

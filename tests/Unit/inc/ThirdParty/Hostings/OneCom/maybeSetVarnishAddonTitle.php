<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\ThirdParty\Hostings\OneCom;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\OneCom::maybe_set_varnish_addon_title
 * @group OneCom
 * @group ThirdParty
 */
class Test_MaybeSetVarnishAddonTitle extends TestCase {
    private $onecom;

	public function setUp() : void {
		parent::setUp();
        
        $this->onecom = new OneCom();

        Functions\stubTranslationFunctions();
	}

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

        $settings = $this->onecom->maybe_set_varnish_addon_title( $config['varnish_field_settings'] );

		$this->assertSame( $expected['title'], $settings['varnish_auto_purge']['title'] );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeSetVarnishAddonTitle' );
	}
}

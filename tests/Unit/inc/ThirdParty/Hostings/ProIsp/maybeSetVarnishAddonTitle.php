<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\ProIsp;

use WP_Rocket\ThirdParty\Hostings\ProIsp;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\ProIsp::maybe_set_varnish_addon_title
 * @group ProIsp
 * @group ThirdParty
 */
class Test_MaybeSetVarnishAddonTitle extends TestCase {
    private $proisp;

	public function setUp() : void {
		parent::setUp();

        $this->proisp = new ProIsp();

        Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->constants['vcaching'] = $config['onecom_performance_plugin_enabled'];

		if ( $config['onecom_performance_plugin_enabled'] ) {
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
		}

        $settings = $this->proisp->maybe_set_varnish_addon_title( $config['varnish_field_settings'] );

		$this->assertSame( $expected['title'], $settings['varnish_auto_purge']['title'] );
	}
}

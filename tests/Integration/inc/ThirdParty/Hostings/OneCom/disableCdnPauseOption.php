<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\OneCom;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\OneCom::disable_cdn_pause_option
 *
 * @group OneCom
 */
class Test_DisableCdnPauseOption extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_cdn_driver_sections', 'disable_cdn_pause_option', PHP_INT_MAX );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_cdn_driver_sections' );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

        $this->constants['vcaching'] = $config['onecom_performance_plugin_enabled'];

		if ( $config['onecom_performance_plugin_enabled'] ) {
			Functions\when( 'rest_sanitize_boolean' )
				->justReturn( $config['oc_cdn_enabled'] );

			Functions\when( 'get_option' )
				->alias( function( $value ) use( $config ) {
					if ( 'oc_cdn_enabled' === $value ) {
						return $config['oc_cdn_enabled'];
					}
				}
				);
		}

		$result = apply_filters( 'rocket_cdn_driver_sections', $config['sections'] );

		foreach ( $expected['sections'] as $key => $section_expected ) {
			$this->assertArrayHasKey( $key, $result, "Section $key should exist in result." );

			if ( isset( $section_expected['status_indicator']['disable_pause_btn'] ) ) {
				$this->assertSame(
					$section_expected['status_indicator']['disable_pause_btn'],
					$result[ $key ]['status_indicator']['disable_pause_btn'] ?? false,
					"disable_pause_btn for $key should match expected value."
				);
			}
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'disableCdnPauseOption' );
	}
}


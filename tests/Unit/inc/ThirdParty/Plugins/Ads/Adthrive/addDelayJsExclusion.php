<?php

namespace WP_Rocket\Tests\Unit\Inc\ThirdParty\Plugins\Ads;

use Mockery;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\ThirdParty\Plugins\Ads\Adthrive;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ads\Adthrive::add_delay_js_exclusion
 *
 * @group Adthrive
 * @group ThirdParty
 */
class Test_AddDelayJsExclusion extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$options_api = Mockery::mock( Options::class );
		$options     = Mockery::mock( Options_Data:: class );
		$adthrive    = new Adthrive( $options_api, $options );

		$options->shouldReceive( 'get' )
			->once()
			->with( 'delay_js', 0 )
			->andReturn( $settings['delay_js'] );

		if ( 1 === $settings['delay_js'] ) {
			$options->shouldReceive( 'get' )
				->once()
				->with( 'delay_js_exclusions', [] )
				->andReturn( $settings['delay_js_exclusions'] );
		}

		if ( ! empty ( $expected ) ) {
			$options->shouldReceive( 'set' )
				->once()
				->with( 'delay_js_exclusions', $expected['delay_js_exclusions'] );

			$options->shouldReceive( 'get_options' )
				->once()
				->andReturn( $expected );

			$options_api->shouldReceive( 'set' )
				->once()
				->with( 'settings', $expected );
		}

		$adthrive->add_delay_js_exclusion();
	}
}

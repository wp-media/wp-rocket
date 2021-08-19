<?php

namespace WP_Rocket\Tests\Unit\Inc\ThirdParty\Plugins\Optimization\WPMeteor;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor::disable_delay_js
 *
 * @group WPMeteor
 * @group ThirdParty
 */
class Test_DisableDelayJs extends TestCase {
	public function testShouldReturnExpected() {
		$options_api = Mockery::mock( Options::class);
		$options     = Mockery::mock( Options_Data::class );
		$meteor = new WPMeteor( $options_api, $options );

		$options->shouldReceive( 'set' )
			->once()
			->with( 'delay_js', 0 );

		$options->shouldReceive( 'get_options' )
			->once()
			->andReturn( [
				'delay_js' => 0
			]
		);

		$options_api->shouldReceive( 'set' )
			->once()
			->with( 'settings', [
				'delay_js' => 0
			] );

		$meteor->disable_delay_js();
	}
}

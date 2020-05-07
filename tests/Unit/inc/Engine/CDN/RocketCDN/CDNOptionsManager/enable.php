<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\CDNOptionsManager;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use Mockery;

/**
 * @covers\WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::enable
 * @group RocketCDN
 */
class Test_Enable extends TestCase {
	public function testShouldEnableCDNOptions() {
		$expected = [
			'cdn'        => 1,
			'cdn_cnames' => [
				'https://rocketcdn.me',
			],
			'cdn_zone'   => [
				'all',
			],
		];

		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocketcdn_status' );

		$options_array = Mockery::mock( Options_Data::class );
		foreach ($expected as $option_key => $option_value) {
			$options_array->shouldReceive( 'set' )
				->once()
				->with( $option_key, $option_value )
				->andReturn();
		}

		$options_array->shouldReceive( 'get_options' )
		              ->andReturn( $expected );

		$options = Mockery::mock( Options::class );
		$options->shouldReceive( 'set' )
		        ->with( 'settings', $expected );

		( new CDNOptionsManager(
			$options,
			$options_array
		) )->enable( 'https://rocketcdn.me' );
	}
}

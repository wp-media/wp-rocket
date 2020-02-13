<?php

namespace WP_Rocket\Tests\Unit\CDN\RocketCDN\CDNOptionsManager;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\CDN\RocketCDN\CDNOptionsManager;

/**
 * @covers\WP_Rocket\CDN\RocketCDN\CDNOptionsManager::enable
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

		$options_array = $this->createMock( Options_Data::class );
		$options_array->expects( $this->exactly( 3 ) )
		              ->method( 'set' )
		              ->withConsecutive(
			              [ 'cdn', 1 ],
			              [ 'cdn_cnames', [ 'https://rocketcdn.me' ] ],
			              [ 'cdn_zone', [ 'all' ] ]
		              );
		$options_array->method( 'get_options' )
		              ->willReturn( $expected );

		$options = $this->createMock( Options::class );
		$options->expects( $this->once() )
		        ->method( 'set' )
		        ->with( 'settings', $expected );

		( new CDNOptionsManager(
			$options,
			$options_array
		) )->enable( 'https://rocketcdn.me' );
	}
}

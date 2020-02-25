<?php

namespace WP_Rocket\Tests\Unit\inc\classes\CDN\RocketCDN\CDNOptionsManager;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\CDN\RocketCDN\CDNOptionsManager;

/**
 * @covers\WP_Rocket\CDN\RocketCDN\CDNOptionsManager::disable
 * @group RocketCDN
 */
class Test_Disable extends TestCase {
	public function testShouldDisableCDNOptions() {
		$expected = [
			'cdn'        => 0,
			'cdn_cnames' => [],
			'cdn_zone'   => [],
		];

		Functions\expect( 'delete_option' )
			->once()
			->with( 'rocketcdn_user_token' );
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocketcdn_status' );

		$options_array = $this->createMock( Options_Data::class );
		$options_array->expects( $this->exactly( 3 ) )
		              ->method( 'set' )
		              ->withConsecutive(
			              [ 'cdn', 0 ],
			              [ 'cdn_cnames', [] ],
			              [ 'cdn_zone', [] ]
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
		) )->disable();
	}
}

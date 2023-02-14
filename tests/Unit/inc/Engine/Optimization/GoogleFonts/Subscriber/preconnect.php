<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\GoogleFonts\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\GoogleFonts\Combine;
use WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2;
use WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber::preconnect
 * @group GoogleFonts
 */
class Test_Preconnect extends TestCase {
	private $options;
	private $subscriber;

	public function setUp() : void {
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new Subscriber( Mockery::mock( Combine::class ), Mockery::mock( CombineV2::class ), $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedArray( $bypass, $option_value, $urls, $relation_type, $user_logged_in, $expected ) {
		Functions\when( 'rocket_bypass' )->justReturn( $bypass );

		if ( ! $bypass ) {
			$this->options->shouldReceive( 'get' )
				->once()
				->andReturn( $option_value );

			Functions\when( 'is_user_logged_in' )->justReturn( $user_logged_in );
		} else {
			$this->options->shouldReceive( 'get' )
				->never();
		}


		$this->assertSame(
			$expected,
			$this->subscriber->preconnect( $urls, $relation_type )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preconnect' );
	}
}

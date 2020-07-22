<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\GoogleFonts\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber::preconnect
 * @group CombineGoogleFonts
 */
class Test_Preconnect extends TestCase {
	private $options;
	private $subscriber;

	public function setUp() {
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new Subscriber( $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedArray( $bypass, $option_value, $urls, $relation_type, $expected ) {
		Functions\when( 'rocket_bypass' )->justReturn( $bypass );

		if ( ! $bypass ) {
			$this->options->shouldReceive( 'get' )
				->once()
				->andReturn( $option_value );
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

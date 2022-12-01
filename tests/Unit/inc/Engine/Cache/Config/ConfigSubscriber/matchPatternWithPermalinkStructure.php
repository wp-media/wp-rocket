<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\Config\ConfigSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\Config\ConfigSubscriber;

/**
 * @covers \WP_Rocket\Engine\Cache\Config\ConfigSubscriber::match_pattern_with_permalink_structure
 */
class Test_MatchPatternWithPermalinkStructure extends TestCase {
    private $config_subscriber;

	public function setUp() : void {
		parent::setUp();
		
		$this->config_subscriber = new ConfigSubscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( ! empty( $config['patterns'] ) ) {

			$maybe_trailing_slash = $config['permalink']['trailing_slash'] ? '/' : '';
			$return_values = [
				$config['patterns'][0] . $maybe_trailing_slash,
				$config['patterns'][1] . $maybe_trailing_slash,
			];

			Functions\expect('user_trailingslashit')
			->twice()
			->andReturnValues( $return_values );
		}

		$this->assertSame( 
			$expected,
			$this->config_subscriber->match_pattern_with_permalink_structure( $config['patterns'] ) 
		);
	}
}

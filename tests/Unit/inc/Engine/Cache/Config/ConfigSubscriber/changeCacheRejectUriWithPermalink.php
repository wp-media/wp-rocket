<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\Config\ConfigSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\Config\ConfigSubscriber;

/**
 * @covers \WP_Rocket\Engine\Cache\Config\ConfigSubscriber::change_cache_reject_uri_with_permalink
 */
class Test_ChangeCacheRejectUriWithPermalink extends TestCase {
    private $config_subscriber;

	public function setUp() : void {
		parent::setUp();
		
		$this->config_subscriber = new ConfigSubscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( isset( $config['permalink'] ) ) {
			$maybe_trailing_slash = $config['permalink']['trailing_slash'] ? '/' : '';
			$return_values = [
				$config['value']['cache_reject_uri'][0] . $maybe_trailing_slash,
				$config['value']['cache_reject_uri'][1] . $maybe_trailing_slash,
			];

			Functions\expect('user_trailingslashit')
			->twice()
			->andReturnValues( $return_values );
		}

		$this->assertSame( 
			$expected,
			$this->config_subscriber->change_cache_reject_uri_with_permalink( $config['value'], $config['old_value'] ) 
		);
	}
}

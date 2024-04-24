<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\Config\ConfigSubscriber;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Cache\Config\ConfigSubscriber;

/**
 * Test class covering \WP_Rocket\Engine\Cache\Config\ConfigSubscriber::change_cache_reject_uri_with_permalink
 */
class Test_ChangeCacheRejectUriWithPermalink extends TestCase {
    private $config_subscriber;

	public function setUp() : void {
		parent::setUp();
		
		$options_data = Mockery::mock( Options_Data::class );
		$options      = Mockery::mock( Options::class );
		$this->config_subscriber = new ConfigSubscriber( $options_data, $options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$return_values = [];

		if ( isset( $config['permalink'] ) ) {
			$times = 2;
			$maybe_trailing_slash = $config['permalink']['trailing_slash'] ? '/' : '';

			$return_values[] = $config['value']['cache_reject_uri'][0] . $maybe_trailing_slash;

			if ( false !== strpos( $config['value']['cache_reject_uri'][1], 'index.php' ) || '/' === $config['value']['cache_reject_uri'][1] ) {
				unset( $return_values[1] );
				$times = 1;
			}
			else{
				$return_values[] = $config['value']['cache_reject_uri'][1] . $maybe_trailing_slash;
			}

			Functions\expect('user_trailingslashit')
			->times($times)
			->andReturnValues( $return_values );
		}

		$this->assertSame( 
			$expected,
			$this->config_subscriber->change_cache_reject_uri_with_permalink( $config['value'], $config['old_value'] ) 
		);
	}
}

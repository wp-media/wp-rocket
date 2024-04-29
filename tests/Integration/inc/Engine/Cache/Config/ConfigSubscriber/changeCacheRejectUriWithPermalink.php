<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\Config\ConfigSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Cache\Config\ConfigSubscriber::change_cache_reject_uri_with_permalink
 */
class Test_ChangeCacheRejectUriWithPermalink extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        
		if ( isset( $config['permalink'] ) ) {
            $this->set_permalink_structure( $config['permalink']['structure'] );
		}

		$this->assertSame( 
			$expected,
            apply_filters( 'pre_update_option_wp_rocket_settings', $config['value'],  $config['old_value'] )
		);
	}
}

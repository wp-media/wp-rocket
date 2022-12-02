<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\Config\ConfigSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\Config\ConfigSubscriber::change_cache_reject_uri_with_permalink
 */
class Test_ChangeCacheRejectUriWithPermalink extends TestCase {
    
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $settings, $expected ) {
		if ( isset( $settings['permalink'] ) ) {
            $this->set_permalink_structure( $config['permalink']['structure'] );
		}

		$this->assertSame( 
			$expected,
            apply_filters( 'pre_update_option_' . WP_ROCKET_SLUG, $settings['new_value'], $settings['old_value'] )
		);
	}
}

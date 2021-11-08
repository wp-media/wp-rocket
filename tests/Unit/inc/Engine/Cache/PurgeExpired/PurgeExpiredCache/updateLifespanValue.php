<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\PurgeExpired\PurgeExpiredCache;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache::update_lifespan_value
 * @group  Cache
 */
class Test_UpdateLifespanValue extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/PurgeExpired/PurgeExpiredCache/updateLifespanValue.php';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldUpdateLifespan( $config, $expected ) {
		$old_options = $config['options'];

		if ( isset($old_options['purge_cron_interval']) && 'MINUTE_IN_SECONDS' === $old_options['purge_cron_unit'] ) {
			Functions\when( 'get_option' )->justReturn( $old_options );
			Functions\expect( 'update_option' )
				->once()
				->with( 'wp_rocket_settings', $expected );
		}else{
			Functions\expect( 'get_option' )->never();
			Functions\expect( 'update_option' )->never();
		}

		$purge_cache = new PurgeExpiredCache( '' );
		$purge_cache->update_lifespan_value($old_options['purge_cron_interval'], $old_options['purge_cron_unit']);

	}

}

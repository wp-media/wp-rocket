<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeExpired\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Cache\PurgeExpired\PurgeExpiredCache;
use WP_Rocket\Engine\Cache\PurgeExpired\Subscriber;

/**
 * @covers Subscriber::get_cache_lifespan
 * @uses   \WP_Rocket\Admin\Options
 * @uses   \WP_Rocket\Admin\Options_Data
 * @group  Subscriber
 */
class Test_GetCacheLifespan extends TestCase {

	private function getSubscriberInstance() {
		return new Subscriber(
			new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) ),
			new PurgeExpiredCache( rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) )
		);
	}

	public function testShouldReturnLifespan() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 10,
				'purge_cron_unit'     => HOUR_IN_SECONDS,
			]
		);

		$this->assertSame( 36000, $this->getSubscriberInstance()->get_cache_lifespan() );
	}

	public function testShouldReturnZeroWhenNoLifespan() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => 0,
				'purge_cron_unit'     => HOUR_IN_SECONDS,
			]
		);

		$this->assertSame( 0, $this->getSubscriberInstance()->get_cache_lifespan() );
	}

	public function testShouldReturnDefaultValueWhenIncorrect() {
		update_option(
			'wp_rocket_settings',
			[
				'purge_cron_interval' => - 10,
				'purge_cron_unit'     => '',
			]
		);

		$this->assertSame( 36000, $this->getSubscriberInstance()->get_cache_lifespan() );
	}
}

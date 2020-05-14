<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\ExpiredCachePurgeSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Cache\ExpiredCachePurge;
use WP_Rocket\Engine\Cache\ExpiredCachePurgeSubscriber;

/**
 * @covers \WP_Rocket\Engine\Cache\ExpiredCachePurgeSubscriber::get_cache_lifespan
 * @uses   \WP_Rocket\Admin\Options
 * @uses   \WP_Rocket\Admin\Options_Data
 *
 * @group  Cache
 * @group  Subscriber
 */
class Test_GetCacheLifespan extends TestCase {

	private function getSubscriberInstance() {
		return new ExpiredCachePurgeSubscriber(
			new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) ),
			new ExpiredCachePurge( _rocket_get_wp_rocket_cache_path() )
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

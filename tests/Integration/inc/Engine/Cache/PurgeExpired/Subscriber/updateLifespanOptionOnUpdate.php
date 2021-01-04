<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\PurgeExpired\Subscriber;

use WP_Rocket\Engine\Cache\PurgeExpired\Subscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers Subscriber::update_lifespan_option_on_update
 * @group  Cache
 */
class Test_UpdateLifespanOptionOnUpdate extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/PurgeExpired/Subscriber/updateLifespanOptionOnUpdate.php';

	private $options = [];

	public function setUp() {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'update_lifespan_option_on_update', 13 );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_purge_cron_interval', [$this, 'set_purge_cron_interval'] );
		remove_filter( 'pre_get_rocket_option_purge_cron_unit', [$this, 'set_purge_cron_unit'] );

		$this->restoreWpFilter( 'wp_rocket_upgrade' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldUpdateLifespan( $config, $expected ) {
		$this->options = array_merge( get_option( 'wp_rocket_settings' ), $config['options'] );
		add_filter( 'pre_get_rocket_option_purge_cron_interval', [$this, 'set_purge_cron_interval'] );
		add_filter( 'pre_get_rocket_option_purge_cron_unit', [$this, 'set_purge_cron_unit'] );

		do_action('wp_rocket_upgrade', '', $config['old_version']);

		$options = get_option( 'wp_rocket_settings', [] );
		if ( isset($expected['bailout']) && $expected['bailout'] ) {
			$this->assertEmpty( $options );
		}else{
			$this->assertSame( $expected['purge_cron_interval'], intval( $options['purge_cron_interval'] ) );
			$this->assertSame( $expected['purge_cron_unit'], $options['purge_cron_unit'] );
		}

	}

	public function set_purge_cron_interval( $value ) {
		return $this->options['purge_cron_interval'];
	}

	public function set_purge_cron_unit( $value ) {
		return $this->options['purge_cron_unit'];
	}

}

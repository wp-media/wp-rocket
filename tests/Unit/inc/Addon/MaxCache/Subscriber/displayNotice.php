<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Addon\MaxCache\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::display_notice
 *
 * @group MaxCache
 */
class TestDisplayNotice extends TestCase {
	/**
	 * Checks whether the announcement is made for a given state.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Site and plugin state for the case.
	 * @param bool  $expected Whether the notice is printed.
	 *
	 * @return void
	 */
	public function testShouldAnnounceWhenExpected( $config, $expected ) {
		$printed = false;

		if ( ! defined( 'WP_ROCKET_PLUGIN_SLUG' ) ) {
			define( 'WP_ROCKET_PLUGIN_SLUG', 'wprocket' );
		}

		Functions\when( '__' )->returnArg();
		Functions\when( 'current_user_can' )->justReturn( $config['allowed'] );
		Functions\when( 'get_transient' )->justReturn( $config['pending'] );
		Functions\when( 'get_user_meta' )->justReturn( $config['dismissed'] ? [ 'rocket_maxcache_notice' ] : [] );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'admin_url' )->returnArg();
		Functions\when( 'wp_nonce_url' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'rocket_get_constant' )->justReturn( 'wp-rocket' );
		Functions\when( 'rocket_notice_html' )->alias(
			function () use ( &$printed ) {
				$printed = true;
			}
		);

		$maxcache = Mockery::mock( MaxCache::class );
		$maxcache->shouldReceive( 'options' )->andReturn( new Options_Data( [ 'maxcache' => $config['option'] ] ) );
		$maxcache->shouldReceive( 'has_directives' )->andReturn( $config['directives'] );
		$maxcache->shouldReceive( 'is_enabled' )->andReturn( $config['enabled'] );

		$subscriber = new Subscriber( $maxcache, Mockery::mock( Options::class ) );

		$subscriber->display_notice();

		$this->assertSame( $expected, $printed );
	}
}

<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

abstract class TestCase extends FilesystemTestCase {
	protected static $rocketcdn_user_token;

	public static function setUpBeforeClass() {
		static::$use_settings_trait = true;
		static::$transients         = [
			'rocketcdn_status' => null,
		];
		parent::setUpBeforeClass();

		static::$rocketcdn_user_token = get_option( 'rocketcdn_user_token', null );

		// Clean out the cached dirs before we run these tests.
		_rocket_get_cache_dirs( '', '', true );
	}

	public function setUp() {
		parent::setUp();

		set_transient( 'rocketcdn_status', [ 'transient' ], MINUTE_IN_SECONDS );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );

		if ( empty( static::$rocketcdn_user_token ) ) {
			delete_option( 'rocketcdn_user_token' );
		} else {
			update_option( 'rocketcdn_user_token', static::$rocketcdn_user_token );
		}

		// Clean out the cached dirs before we leave this test class.
		_rocket_get_cache_dirs( '', '', true );

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );
		unset( $GLOBALS['debug_fs'] );
	}

	protected function getCDNOptionsManager() {
		$options      = new Options( 'wp_rocket_' );
		$option_array = new Options_Data( $options->get( 'settings' ) );

		return new CDNOptionsManager(
			$options,
			$option_array
		);
	}

	protected function assertSettings( $expected ) {
		// Check the settings.
		$settings = get_option( 'wp_rocket_settings' );
		foreach ( $expected['settings'] as $key => $value ) {
			$this->assertArrayHasKey( $key, $settings );
			$this->assertSame( $value, $settings[ $key ] );
		}
	}

	protected function assertCacheDeleted( $expected ) {
		$this->checkEntriesDeleted( $expected['cleaned'] );
		$this->checkShouldNotDeleteEntries();
	}
}

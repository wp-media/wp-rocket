<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\CDNOptionsManager;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

abstract class TestCase extends FilesystemTestCase {
	use DBTrait;

	protected static $rocketcdn_user_token;

	public static function set_up_before_class() {
		static::$use_settings_trait = true;
		static::$transients         = [
			'rocketcdn_status' => null,
		];
		parent::set_up_before_class();

		self::installFresh();

		static::$rocketcdn_user_token = get_option( 'rocketcdn_user_token', null );
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		set_transient( 'rocketcdn_status', [ 'transient' ], MINUTE_IN_SECONDS );
	}

	public function tear_down() {
		delete_transient( 'rocketcdn_status' );

		if ( empty( static::$rocketcdn_user_token ) ) {
			delete_option( 'rocketcdn_user_token' );
		} else {
			update_option( 'rocketcdn_user_token', static::$rocketcdn_user_token );
		}

		unset( $GLOBALS['sitepress'], $GLOBALS['q_config'], $GLOBALS['polylang'] );

		parent::tear_down();
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

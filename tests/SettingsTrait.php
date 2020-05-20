<?php

namespace WP_Rocket\Tests;

trait SettingsTrait {
	public static $before_settings = [];

	public $settings     = [];
	public $old_settings = [];

	public static function getOriginalSettings() {
		self::$before_settings = (array) get_option( 'wp_rocket_settings', [] );
	}

	public static function resetOriginalSettings() {
		update_option( 'wp_rocket_settings', self::$before_settings );
	}

	public function setUpSettings() {
		$this->old_settings = array_key_exists( 'settings', $this->config )
			? array_merge( self::$before_settings, $this->config['settings'] )
			: self::$before_settings;
		update_option( 'wp_rocket_settings', $this->old_settings );
	}

	public function tearDownSettings() {
		delete_option( 'wp_rocket_settings' );
	}

	public function mergeExistingSettingsAndUpdate( array $settings ) {
		update_option(
			'wp_rocket_settings',
			array_merge( $this->old_settings, $settings )
		);
	}
}

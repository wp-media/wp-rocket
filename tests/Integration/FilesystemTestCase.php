<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Tests\SettingsTrait;
use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Integration\VirtualFilesystemTestCase;

abstract class FilesystemTestCase extends VirtualFilesystemTestCase {
	use SettingsTrait;
	use StubTrait;
	use VirtualFilesystemTrait;
	use FilterTrait;

	protected static $use_settings_trait = false;
	protected static $skip_setting_up_settings = false;
	protected static $transients         = [];

	public static function set_up_before_class() {
		parent::set_up_before_class();

		if ( static::$use_settings_trait ) {
			self::getOriginalSettings();
		}

		if ( ! empty( self::$transients ) ) {
			foreach ( array_keys( self::$transients ) as $transient ) {
				static::$transients[ $transient ] = get_transient( $transient );
			}
		}

		// Clean out the cached dirs before we run these tests.
		_rocket_get_cache_dirs( '', '', true );
	}

	public static function tear_down_after_class() {
		if ( static::$use_settings_trait ) {
			self::resetOriginalSettings();
		}

		foreach ( static::$transients as $transient => $value ) {
			if ( ! empty( $value ) ) {
				set_transient( $transient, $value );
			} else {
				delete_transient( $transient );
			}
		}

		// Clean out the cached dirs before we leave this test class.
		_rocket_get_cache_dirs( '', '', true );

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->initDefaultStructure();
		$this->init();
		if ( static::$use_settings_trait && ! static::$skip_setting_up_settings ) {
			$this->setUpSettings();
		}

		$this->stubRocketGetConstant();
		$this->redefineRocketDirectFilesystem();
	}

	public function tear_down() {
		if ( static::$use_settings_trait ) {
			$this->tearDownSettings();
		}

		$this->resetStubProperties();

		unset( $GLOBALS['debug_fs'] );

		parent::tear_down();
	}
}

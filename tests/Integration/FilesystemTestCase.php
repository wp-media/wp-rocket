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

	protected static $use_settings_trait = false;
	protected static $skip_setting_up_settings = false;
	protected static $transients         = [];

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		if ( static::$use_settings_trait ) {
			SettingsTrait::getOriginalSettings();
		}

		if ( ! empty( self::$transients ) ) {
			foreach ( array_keys( self::$transients ) as $transient ) {
				static::$transients[ $transient ] = get_transient( $transient );
			}
		}

		// Clean out the cached dirs before we run these tests.
		_rocket_get_cache_dirs( '', '', true );
	}

	public static function tearDownAfterClass() {
		parent::setUpBeforeClass();

		if ( static::$use_settings_trait ) {
			SettingsTrait::resetOriginalSettings();
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
	}

	public function setUp() : void {
		$this->initDefaultStructure();
		if ( static::$use_settings_trait && ! static::$skip_setting_up_settings ) {
			$this->setUpSettings();
		}

		parent::setUp();

		$this->stubRocketGetConstant();
		$this->redefineRocketDirectFilesystem();
	}

	public function tearDown() {
		if ( static::$use_settings_trait ) {
			$this->tearDownSettings();
		}

		$this->resetStubProperties();

		unset( $GLOBALS['debug_fs'] );

		parent::tearDown();
	}
}

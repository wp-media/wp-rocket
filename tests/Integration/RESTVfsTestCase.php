<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Tests\SettingsTrait;
use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Tests\VirtualFilesystemTrait;
use WPMedia\PHPUnit\Integration\RESTVfsTestCase as BaseTestCase;

abstract class RESTVfsTestCase extends BaseTestCase {
	use CapTrait;
	use SettingsTrait;
	use StubTrait;
	use VirtualFilesystemTrait;

	protected static $use_settings_trait       = false;
	protected static $skip_setting_up_settings = false;
	protected static $transients               = [];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		CapTrait::hasAdminCapBeforeClass();

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
		parent::tearDownAfterClass();

		CapTrait::resetAdminCap();

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

	public function setUp() {
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

	public function dataProvider() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}
}

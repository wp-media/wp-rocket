<?php

namespace WP_Rocket\Tests\Integration;

use ReflectionObject;
use WP_Rocket\Tests\SettingsTrait;
use WP_Rocket\Tests\StubTrait;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	use CapTrait;
	use SettingsTrait;
	use StubTrait;
	use FilterTrait;
	use DBTrait;

	protected static $use_settings_trait = true;
	protected static $transients         = [];

	protected $config;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installFresh();

		CapTrait::hasAdminCapBeforeClass();

		if ( static::$use_settings_trait ) {
			SettingsTrait::getOriginalSettings();
		}

		if ( ! empty( self::$transients ) ) {
			foreach ( array_keys( self::$transients ) as $transient ) {
				self::$transients[ $transient ] = get_transient( $transient );
			}
		}
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();

		CapTrait::resetAdminCap();

		if ( static::$use_settings_trait ) {
			SettingsTrait::resetOriginalSettings();
		}

		foreach ( self::$transients as $transient => $value ) {
			if ( ! empty( $transient ) ) {
				set_transient( $transient, $value );
			} else {
				delete_transient( $transient );
			}
		}

		self::uninstallAll();
	}

	public function set_up() {
		parent::set_up();

		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		$this->stubRocketGetConstant();

		if ( static::$use_settings_trait ) {
			$this->setUpSettings();
		}
	}

	public function tear_down() {
		$this->resetStubProperties();

		if ( static::$use_settings_trait ) {
			$this->tearDownSettings();
		}

		parent::tear_down();
	}

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		return isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;
	}

	protected function loadTestDataConfig() {
		$obj      = new ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}
}

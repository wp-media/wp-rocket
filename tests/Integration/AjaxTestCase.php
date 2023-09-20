<?php

namespace WP_Rocket\Tests\Integration;

use ReflectionObject;
use WP_Rocket\Tests\CallbackTrait;
use WP_Rocket\Tests\SettingsTrait;
use WP_Rocket\Tests\StubTrait;
use WPMedia\PHPUnit\Integration\AjaxTestCase as WPMediaAjaxTestCase;

abstract class AjaxTestCase extends WPMediaAjaxTestCase {
	use CallbackTrait;
	use CapTrait;
	use SettingsTrait;
	use StubTrait;
	use DBTrait;

	protected static $use_settings_trait = false;
	protected static $transients         = [];

	protected $config;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::hasAdminCapBeforeClass();

		self::installFresh();

		if ( static::$use_settings_trait ) {
			self::getOriginalSettings();
		}

		if ( ! empty( self::$transients ) ) {
			foreach ( array_keys( self::$transients ) as $transient ) {
				self::$transients[ $transient ] = get_transient( $transient );
			}
		}
	}

	public static function tear_down_after_class() {
		self::resetAdminCap();

		self::uninstallAll();

		if ( static::$use_settings_trait ) {
			self::resetOriginalSettings();
		}

		foreach ( self::$transients as $transient => $value ) {
			if ( ! empty( $transient ) ) {
				set_transient( $transient, $value );
			} else {
				delete_transient( $transient );
			}
		}

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		self::removeDBHooks();

		$this->stubRocketGetConstant();

		if ( static::$use_settings_trait ) {
			$this->setUpSettings();
		}
	}

	public function tear_down() {
		unset( $_POST['action'], $_POST['nonce'] );
		$this->action = null;
		self::resetAdminCap();

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

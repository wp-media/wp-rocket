<?php

namespace WP_Rocket\Tests\Integration;

use ReflectionObject;
use WP_Rocket\Tests\SettingsTrait;
use WP_Rocket\Tests\StubTrait;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	use SettingsTrait;
	use StubTrait;

	protected static $use_settings_trait = true;
	protected static $transients         = [];

	protected $config;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		if ( static::$use_settings_trait ) {
			SettingsTrait::getOriginalSettings();
		}

		if ( ! empty( self::$transients ) ) {
			foreach ( array_keys( self::$transients ) as $transient ) {
				self::$transients[ $transient ] = get_transient( $transient );
			}
		}
	}

	public static function tearDownAfterClass() {
		parent::setUpBeforeClass();

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
	}

	public function setUp() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		$this->stubRocketGetConstant();

		parent::setUp();

		if ( static::$use_settings_trait ) {
			$this->setUpSettings();
		}
	}

	public function tearDown() {
		parent::tearDown();

		if ( static::$use_settings_trait ) {
			$this->tearDownSettings();
		}
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

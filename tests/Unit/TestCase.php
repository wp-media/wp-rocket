<?php

namespace WP_Rocket\Tests\Unit;

use ReflectionObject;
use WP_Rocket\Tests\StubTrait;
use WPMedia\PHPUnit\Unit\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	use StubTrait;

	protected $config;

	protected function setUp(): void {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		$this->stubRocketGetConstant();
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

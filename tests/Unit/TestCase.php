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
		$this->stubRocketHasConstant();
	}

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		$data = isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;

		// An empty provider makes PHPUnit skip the test silently: fail instead, so a missing or misnamed fixture is noticed.
		if ( empty( $data ) ) {
			throw new \RuntimeException( sprintf( 'No test data for %s: its fixture under tests/Fixtures is missing or empty.', static::class ) );
		}

		return $data;
	}

	protected function loadTestDataConfig() {
		$obj      = new ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}
}

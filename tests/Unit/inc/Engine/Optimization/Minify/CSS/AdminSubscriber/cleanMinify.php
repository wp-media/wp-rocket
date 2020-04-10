<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::clean_minify
 * @group  Optimize
 * @group  AdminSubscriber
 */
class Test_CleanMinify extends TestCase {
	private $admin_subcriber;
	private $config;

	public function setUp() {
		parent::setUp();
		$this->admin_subcriber = new AdminSubscriber();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinify( $value, $should_run ) {
		if ( $should_run ) {
			Functions\expect( 'rocket_clean_minify' )
				->once()
				->with( 'css' );
		} else {
			Functions\expect( 'rocket_clean_minify' )->never();
		}
		$this->admin_subcriber->clean_minify( $this->config['settings'], $value );
	}

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, 'cleanMinify' );
	}
}

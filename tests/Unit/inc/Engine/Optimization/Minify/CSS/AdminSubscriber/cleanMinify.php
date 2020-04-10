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
	private $admin_susbcriber;
	private $config;

	public function setUp() {
		parent::setUp();
		$this->admin_susbcriber = new AdminSubscriber();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testCleanMinify( $old_value, $value, $shouldRun ) {
		if ( $shouldRun ) {
			Functions\expect( 'rocket_clean_minify' )
				->once()
				->with( 'css' );
		} else {
			Functions\expect( 'rocket_clean_minify' )->never();
		}
		$this->admin_susbcriber->clean_minify( $old_value, $value );
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
